<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Ticket;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $tickets = Ticket::with('createdByUser')
            ->leftJoin('teachers', 'teachers.user_id', '=', 'tickets.created_by_user_id')
            ->select('tickets.*', 'teachers.name as teacher_display_name')
            ->when($request->filled('status'),   fn($q) => $q->where('tickets.status', $request->status))
            ->when($request->filled('priority'), fn($q) => $q->where('tickets.priority', $request->priority))
            ->when($request->filled('role'),     fn($q) => $q->where('tickets.created_by_role', $request->role))
            ->orderByDesc('tickets.created_at')
            ->paginate($perPage)
            ->appends(request()->query());

        $teachers = Teacher::orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'teachers', 'perPage'));
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

        self::log('Ticket', "created ticket #{$ticket->id}: {$ticket->title} on behalf of teacher {$teacher->name}");

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

        $statusPhrase = match ($request->status) {
            'Resolved' => "resolved ticket #{$ticket->id}: {$ticket->title}",
            'Closed'   => "closed ticket #{$ticket->id}: {$ticket->title}",
            default    => "updated ticket #{$ticket->id}: {$ticket->title} to status {$request->status}",
        };
        self::log('Ticket', $statusPhrase);

        return response()->json(['success' => true]);
    }
}
