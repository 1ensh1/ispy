<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\EngagementRecord;
use App\Traits\LogsActivity;
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

        self::log('create', 'Parent sent message to teacher');

        return back()->with('success', 'Message sent.');
    }
}
