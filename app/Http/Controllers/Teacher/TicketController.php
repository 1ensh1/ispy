<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Teacher;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $tickets = Ticket::with('assignedToAdmin')
            ->where('created_by_user_id', $userId)
            ->when($request->filled('status'),   fn($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn($q) => $q->where('priority', $request->priority))
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.tickets.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'priority'    => 'required|in:Low,Medium,High',
        ]);

        $userId  = auth()->id();
        $teacher = Teacher::where('user_id', $userId)->firstOrFail();
        $admin   = User::where('role', 'Admin')->first();

        $ticket = Ticket::create([
            'created_by_user_id'  => $userId,
            'created_by_role'     => 'Teacher',
            'assigned_to_user_id' => $admin->id,
            'title'               => $request->title,
            'description'         => $request->description,
            'priority'            => $request->priority,
            'status'              => 'Open',
        ]);

        $admins = User::where('role', 'Admin')->get();
        $notifRows = $admins->map(fn($a) => [
            'recipient_id'      => $a->id,
            'recipient_role'    => 'Admin',
            'title'             => 'New Support Ticket',
            'message'           => "Teacher {$teacher->name} submitted a ticket: '{$ticket->title}'",
            'notification_type' => 'Ticket',
            'action_url'        => route('admin.tickets.index'),
            'is_read'           => false,
            'created_at'        => now(),
        ])->toArray();

        DB::table('notifications')->insert($notifRows);

        ActivityLog::create([
            'user_id'     => $userId,
            'role'        => 'Teacher',
            'action'      => 'Ticket',
            'description' => "Teacher {$teacher->name} submitted ticket '{$ticket->title}' with priority {$request->priority}",
            'created_at'  => now(),
        ]);

        return back()->with('success', 'Your ticket has been submitted successfully.');
    }
}
