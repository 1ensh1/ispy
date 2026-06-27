<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Message;
use App\Models\EngagementRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Symmetric provisioning: ensure every parent who has a non-archived
        // student in one of this teacher's class_subjects has the full set of
        // engagement records (this teacher's pair included). class_subjects is
        // the sole source of truth. Idempotent via firstOrCreate.
        $parentIds = DB::table('class_subjects')
            ->where('class_subjects.teacher_id', $teacher->id)
            ->whereNull('class_subjects.archived_at')
            ->join('students', 'students.class_list_id', '=', 'class_subjects.class_list_id')
            ->whereNull('students.archived_at')
            ->distinct()
            ->pluck('students.parent_id');

        foreach ($parentIds as $parentId) {
            EngagementRecord::provisionForParent($parentId);
        }

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        // Parent IDs with a CURRENT live class link, computed fresh each load.
        // Records whose class relationship is gone are hidden (never deleted)
        // and reappear with full history if the link is later restored.
        $validParentIds = EngagementRecord::validParentIdsForTeacher($teacher->id);

        $search = $request->query('search');
        $engagementsQuery = EngagementRecord::where('teacher_id', $teacher->id)
            ->whereIn('parent_id', $validParentIds)
            ->with('parentProfile');
        if ($search) {
            $engagementsQuery->whereHas('parentProfile', fn($q) => $q->where('name', 'ILIKE', '%' . $search . '%'));
        }
        $engagements = $engagementsQuery->paginate($perPage)->appends(request()->query());

        foreach ($engagements as $eng) {
            $eng->latestMessage = DB::table('messages')
                ->where('engagement_id', $eng->id)
                ->orderByDesc('sent_at')
                ->first();
            $eng->unreadCount = DB::table('messages')
                ->where('engagement_id', $eng->id)
                ->where('sender_role', 'Parent')
                ->where('is_read', false)
                ->count();
        }

        $activeEngagement = null;
        $messages = collect();

        if ($request->filled('engagement_id')) {
            $requestedId = (int) $request->engagement_id;

            // View-gate: the thread is viewable only if it belongs to this
            // teacher AND the parent still has a live class link. Stale threads
            // are not chattable even via a direct ?engagement_id URL.
            $requested = EngagementRecord::find($requestedId);
            abort_if(
                ! $requested
                || $requested->teacher_id !== $teacher->id
                || ! in_array((int) $requested->parent_id, $validParentIds, true),
                403
            );

            // Reuse the paginated row when present (keeps unread display in sync),
            // otherwise fall back to the validated record.
            $activeEngagement = $engagements->firstWhere('id', $requestedId) ?? $requested;
            if ($activeEngagement) {
                $messages = DB::table('messages')
                    ->where('engagement_id', $activeEngagement->id)
                    ->orderBy('id', 'asc')
                    ->get();

                DB::table('messages')
                    ->where('engagement_id', $activeEngagement->id)
                    ->where('sender_role', 'Parent')
                    ->update(['is_read' => true]);

                $activeEngagement->unreadCount = 0;
            }
        }

        return view('teacher.messaging', compact('engagements', 'activeEngagement', 'messages', 'perPage'));
    }

    /**
     * Full thread as JSON for instant in-place conversation switching.
     * Enforces ownership AND the valid-set gate (stale threads are not viewable).
     */
    public function thread(Request $request)
    {
        $request->validate(['engagement_id' => 'required|integer']);

        $teacher    = Teacher::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::with('parentProfile')->find($request->integer('engagement_id'));

        abort_if(
            ! $engagement
            || $engagement->teacher_id !== $teacher->id
            || ! in_array((int) $engagement->parent_id, EngagementRecord::validParentIdsForTeacher($teacher->id), true),
            403
        );

        $messages = DB::table('messages')
            ->where('engagement_id', $engagement->id)
            ->orderBy('id', 'asc')
            ->get(['id', 'sender_role', 'message_body', 'sent_at']);

        // Mirror index(): opening the thread marks Parent-sent messages read.
        DB::table('messages')
            ->where('engagement_id', $engagement->id)
            ->where('sender_role', 'Parent')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'messages'         => $messages,
            'counterpart_name' => $engagement->parentProfile?->name ?? 'Unknown Parent',
            'class_name'       => null, // teacher header shows parent name only
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'engagement_id' => 'required|exists:engagement_records,id',
            'message_body'  => 'required|string|max:2000',
        ]);

        $teacher    = Teacher::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::findOrFail($request->engagement_id);

        abort_if($engagement->teacher_id !== $teacher->id, 403);

        // Valid-set gate: a stale thread (class link removed) cannot be posted to.
        abort_if(
            ! in_array((int) $engagement->parent_id, EngagementRecord::validParentIdsForTeacher($teacher->id), true),
            403
        );

        DB::table('messages')->insert([
            'engagement_id' => $engagement->id,
            'sender_role'   => 'Teacher',
            'sender_id'     => $teacher->id,
            'message_body'  => $request->message_body,
            'sent_at'       => now(),
            'is_read'       => false,
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $engagement->parent_id,
            'recipient_role'    => 'Parent',
            'notification_type' => 'Availability',
            'action_url'        => route('parent.messaging'),
            'title'             => 'New Message from Teacher ' . $teacher->name,
            'message'           => "Your child's teacher sent you a message.",
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        return redirect()->route('teacher.messaging', ['engagement_id' => $engagement->id])
            ->with('success', 'Message sent.');
    }

    public function poll(Request $request)
    {
        $request->validate([
            'engagement_id' => 'required|integer',
            'last_id'       => 'integer',
        ]);

        $teacher    = Teacher::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::find($request->integer('engagement_id'));

        if (! $engagement
            || $engagement->teacher_id !== $teacher->id
            || ! in_array((int) $engagement->parent_id, EngagementRecord::validParentIdsForTeacher($teacher->id), true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastId = $request->integer('last_id', 0);

        $messages = Message::where('engagement_id', $engagement->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get(['id', 'sender_role', 'message_body', 'sent_at']);

        Message::where('engagement_id', $engagement->id)
            ->where('sender_role', '!=', 'Teacher')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['messages' => $messages]);
    }

    public function ajaxStore(Request $request)
    {
        $validator = validator($request->all(), [
            'engagement_id' => 'required|integer',
            'message_body'  => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $teacher    = Teacher::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::find($request->integer('engagement_id'));

        if (! $engagement
            || $engagement->teacher_id !== $teacher->id
            || ! in_array((int) $engagement->parent_id, EngagementRecord::validParentIdsForTeacher($teacher->id), true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'engagement_id' => $engagement->id,
            'sender_role'   => 'Teacher',
            'sender_id'     => $teacher->id,
            'message_body'  => $request->message_body,
            'sent_at'       => Carbon::now(),
            'is_read'       => false,
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $engagement->parent_id,
            'recipient_role'    => 'Parent',
            'notification_type' => 'Report',
            'action_url'        => route('parent.messaging'),
            'title'             => 'New Message',
            'message'           => 'Teacher ' . $teacher->name . ' sent you a message.',
            'is_read'           => false,
            'created_at'        => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id'           => $message->id,
                'sender_role'  => $message->sender_role,
                'message_body' => $message->message_body,
                'sent_at'      => $message->sent_at,
            ],
        ]);
    }
}
