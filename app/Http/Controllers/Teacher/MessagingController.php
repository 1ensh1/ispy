<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\EngagementRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $search = $request->query('search');
        $engagementsQuery = EngagementRecord::where('teacher_id', $teacher->id)
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
            $activeEngagement = $engagements->firstWhere('id', (int) $request->engagement_id);
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

    public function store(Request $request)
    {
        $request->validate([
            'engagement_id' => 'required|exists:engagement_records,id',
            'message_body'  => 'required|string|max:2000',
        ]);

        $teacher    = Teacher::where('user_id', auth()->id())->firstOrFail();
        $engagement = EngagementRecord::findOrFail($request->engagement_id);

        abort_if($engagement->teacher_id !== $teacher->id, 403);

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
            'title'             => 'New Message from Ms. ' . $teacher->name,
            'message'           => "Your child's teacher sent you a message.",
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        return redirect()->route('teacher.messaging', ['engagement_id' => $engagement->id])
            ->with('success', 'Message sent.');
    }
}
