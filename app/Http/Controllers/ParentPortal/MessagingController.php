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
    public function index()
    {
        $parent  = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $student = $parent->students()->active()->with('classList.teacher')->first();
        $teacher = $student?->classList?->teacher;

        $engagement = null;
        $messages   = collect();

        if ($teacher) {
            $engagement = EngagementRecord::firstOrCreate(
                ['parent_id' => $parent->id, 'teacher_id' => $teacher->id]
            );

            $messages = DB::table('messages')
                ->where('engagement_id', $engagement->id)
                ->orderBy('id', 'asc')
                ->get();

            DB::table('messages')
                ->where('engagement_id', $engagement->id)
                ->where('sender_role', '!=', 'Parent')
                ->update(['is_read' => true]);
        }

        return view('parent.messaging', compact('parent', 'student', 'teacher', 'engagement', 'messages'));
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

        if (! $engagement || $engagement->parent_id !== $parent->id) {
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

        if (! $engagement || $engagement->parent_id !== $parent->id) {
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
