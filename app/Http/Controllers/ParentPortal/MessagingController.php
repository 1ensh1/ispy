<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\Message;
use App\Models\EngagementRecord;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $parent  = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $student = $parent->students()->active()->with('classList.teacher')->first();

        // Ensure a thread exists for every teacher across the parent's students'
        // class_subjects (sole source of truth) before listing conversations.
        EngagementRecord::provisionForParent($parent->id);

        // Teacher IDs with a CURRENT live class link, computed fresh each load.
        // Stale records (link gone) are hidden — never deleted — and reappear
        // with full history if the class relationship is later restored.
        $validTeacherIds = EngagementRecord::validTeacherIdsForParent($parent->id);

        // Conversations for this parent, filtered to currently-valid relationships.
        $conversations = EngagementRecord::where('parent_id', $parent->id)
            ->whereIn('teacher_id', $validTeacherIds)
            ->with('teacher')
            ->get();

        foreach ($conversations as $conv) {
            $conv->latestMessage = DB::table('messages')
                ->where('engagement_id', $conv->id)
                ->orderByDesc('sent_at')
                ->first();
            $conv->unreadCount = DB::table('messages')
                ->where('engagement_id', $conv->id)
                ->where('sender_role', 'Teacher')
                ->where('is_read', false)
                ->count();
        }

        // Resolve the active conversation from ?engagement_id, default to the first.
        $engagement = null;
        if ($request->filled('engagement_id')) {
            $engagement = $conversations->firstWhere('id', (int) $request->engagement_id);
            abort_if(! $engagement, 403);
        } else {
            $engagement = $conversations->first();
        }

        $teacher         = null;
        $messages        = collect();
        $activeClassName = null;

        if ($engagement) {
            $teacher = $engagement->teacher;

            // Class label from the CURRENT live link (not a stale stored value).
            $activeClassName = EngagementRecord::currentClassNameForPair(
                $parent->id,
                $engagement->teacher_id
            );

            $messages = DB::table('messages')
                ->where('engagement_id', $engagement->id)
                ->orderBy('id', 'asc')
                ->get();

            DB::table('messages')
                ->where('engagement_id', $engagement->id)
                ->where('sender_role', '!=', 'Parent')
                ->update(['is_read' => true]);

            $engagement->unreadCount = 0;
        }

        return view('parent.messaging', compact('parent', 'student', 'teacher', 'engagement', 'messages', 'conversations', 'activeClassName'));
    }

    /**
     * Full thread as JSON for instant in-place conversation switching.
     * Enforces ownership AND the valid-set gate (stale threads are not viewable).
     */
    public function thread(Request $request)
    {
        $request->validate(['engagement_id' => 'required|integer']);

        $parent     = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::with('teacher')->find($request->integer('engagement_id'));

        abort_if(
            ! $engagement
            || $engagement->parent_id !== $parent->id
            || ! in_array((int) $engagement->teacher_id, EngagementRecord::validTeacherIdsForParent($parent->id), true),
            403
        );

        $messages = DB::table('messages')
            ->where('engagement_id', $engagement->id)
            ->orderBy('id', 'asc')
            ->get(['id', 'sender_role', 'message_body', 'sent_at']);

        // Mirror index(): opening the thread marks Teacher-sent messages read.
        DB::table('messages')
            ->where('engagement_id', $engagement->id)
            ->where('sender_role', '!=', 'Parent')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'messages'         => $messages,
            'counterpart_name' => $engagement->teacher?->name ?? 'Unknown Teacher',
            'class_name'       => EngagementRecord::currentClassNameForPair($parent->id, $engagement->teacher_id),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message_body' => 'required|string|max:2000',
        ]);

        $parent  = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $student = $parent->students()->active()->with('classList.teacher')->first();
        $teacher = $student?->classList?->teacher;

        $engagement = $teacher
            ? EngagementRecord::where('parent_id', $parent->id)
                ->where('teacher_id', $teacher->id)
                ->first()
            : null;

        abort_if(!$engagement, 403);

        // Valid-set gate: a stale thread (class link removed) cannot be posted to.
        abort_if(
            ! in_array((int) $engagement->teacher_id, EngagementRecord::validTeacherIdsForParent($parent->id), true),
            403
        );

        DB::table('messages')->insert([
            'engagement_id' => $engagement->id,
            'sender_role'   => 'Parent',
            'sender_id'     => $parent->id,
            'message_body'  => $request->message_body,
            'sent_at'       => now(),
            'is_read'       => false,
        ]);

        if ($teacher) {
            DB::table('notifications')->insert([
                'recipient_id'      => $teacher->id,
                'recipient_role'    => 'Teacher',
                'notification_type' => 'Availability',
                'action_url'        => route('teacher.messaging', ['engagement_id' => $engagement->id]),
                'title'             => 'New Message from Parent',
                'message'           => "{$parent->name} sent you a message regarding {$student->name}.",
                'is_read'           => false,
                'created_at'        => now(),
            ]);
        }

        self::log('create', 'sent message to teacher ' . ($teacher?->name ?? 'Unknown'));

        return back()->with('success', 'Message sent.');
    }

    public function poll(Request $request)
    {
        $request->validate([
            'engagement_id' => 'required|integer',
            'last_id'       => 'integer',
        ]);

        $parent     = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::find($request->integer('engagement_id'));

        if (! $engagement
            || $engagement->parent_id !== $parent->id
            || ! in_array((int) $engagement->teacher_id, EngagementRecord::validTeacherIdsForParent($parent->id), true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastId = $request->integer('last_id', 0);

        $messages = Message::where('engagement_id', $engagement->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get(['id', 'sender_role', 'message_body', 'sent_at']);

        Message::where('engagement_id', $engagement->id)
            ->where('sender_role', '!=', 'Parent')
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

        $parent     = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::find($request->integer('engagement_id'));

        if (! $engagement
            || $engagement->parent_id !== $parent->id
            || ! in_array((int) $engagement->teacher_id, EngagementRecord::validTeacherIdsForParent($parent->id), true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'engagement_id' => $engagement->id,
            'sender_role'   => 'Parent',
            'sender_id'     => $parent->id,
            'message_body'  => $request->message_body,
            'sent_at'       => Carbon::now(),
            'is_read'       => false,
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $engagement->teacher_id,
            'recipient_role'    => 'Teacher',
            'notification_type' => 'Report',
            'action_url'        => route('teacher.messaging', ['engagement_id' => $engagement->id]),
            'title'             => 'New Message from Parent',
            'message'           => 'Parent ' . $parent->name . ' sent you a message.',
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
