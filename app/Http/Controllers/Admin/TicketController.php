<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Teacher;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with('createdByUser')
            ->leftJoin('teachers', 'teachers.user_id', '=', 'tickets.created_by_user_id')
            ->select('tickets.*', 'teachers.name as teacher_display_name')
            ->when($request->filled('status'),   fn($q) => $q->where('tickets.status', $request->status))
            ->when($request->filled('priority'), fn($q) => $q->where('tickets.priority', $request->priority))
            ->when($request->filled('role'),     fn($q) => $q->where('tickets.created_by_role', $request->role))
            ->orderByDesc('tickets.created_at')
            ->get();

        $teachers = Teacher::orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'              => 'required|max:255',
            'description'        => 'required',
            'priority'           => 'required|in:Low,Medium,High',
            'created_by_user_id' => 'required|exists:users,id',
        ]);

        $admin   = auth()->user();
        $teacher = Teacher::where('user_id', $request->created_by_user_id)->firstOrFail();

        $ticket = Ticket::create([
            'created_by_user_id'  => $request->created_by_user_id,
            'created_by_role'     => 'Admin',
            'assigned_to_user_id' => $admin->id,
            'title'               => $request->title,
            'description'         => $request->description,
            'priority'            => $request->priority,
            'status'              => 'Open',
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $teacher->id,
            'recipient_role'    => 'Teacher',
            'title'             => 'New Ticket Submitted',
            'message'           => "Admin has created a ticket on your behalf: {$ticket->title}",
            'notification_type' => 'Ticket',
            'action_url'        => route('teacher.tickets.index'),
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        ActivityLog::create([
            'user_id'     => $admin->id,
            'role'        => 'Admin',
            'action'      => 'Ticket',
            'description' => "Admin {$admin->name} created ticket '{$ticket->title}' on behalf of Teacher {$teacher->name}",
            'created_at'  => now(),
        ]);

        return back()->with('success', 'Ticket created successfully.');
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status'           => 'required|in:Open,In Progress,Resolved,Closed',
            'resolution_notes' => 'nullable|string',
        ]);

        $ticket->update([
            'status'           => $request->status,
            'resolution_notes' => $request->resolution_notes,
        ]);

        if (in_array($request->status, ['Resolved', 'Closed']) && $ticket->created_by_role === 'Teacher') {
            $teacher = Teacher::where('user_id', $ticket->created_by_user_id)->first();
            if ($teacher) {
                DB::table('notifications')->insert([
                    'recipient_id'      => $teacher->id,
                    'recipient_role'    => 'Teacher',
                    'title'             => "Ticket {$request->status}",
                    'message'           => "Your ticket '{$ticket->title}' has been marked as {$request->status}.",
                    'notification_type' => 'Ticket',
                    'action_url'        => route('teacher.tickets.index'),
                    'is_read'           => false,
                    'created_at'        => now(),
                ]);
            }
        }

        $admin = auth()->user();
        ActivityLog::create([
            'user_id'     => $admin->id,
            'role'        => 'Admin',
            'action'      => 'Ticket',
            'description' => "Admin {$admin->name} updated ticket '{$ticket->title}' to status {$request->status}",
            'created_at'  => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
