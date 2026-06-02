<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\ConsultationSlot;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    use LogsActivity;
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $upcomingCount = DB::table('face_to_face_bookings')
            ->join('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
            ->where('face_to_face_bookings.teacher_id', $teacher->id)
            ->whereIn('face_to_face_bookings.status', ['Pending', 'Confirmed'])
            ->where('consultation_slots.scheduled_date', '>=', today()->format('Y-m-d'))
            ->count();

        $maxPref   = DB::table('teacher_word_set_preferences')
            ->where('teacher_id', $teacher->id)
            ->where('category', 'like', 'max_appointments_per_day:%')
            ->value('category');
        $maxPerDay = $maxPref ? (int) explode(':', $maxPref)[1] : 6;

        // Bug 3: only upcoming slots so past weeks don't inflate the weekly grid
        $slots = ConsultationSlot::where('teacher_id', $teacher->id)
            ->where('scheduled_date', '>=', today()->format('Y-m-d'))
            ->orderBy('scheduled_date')
            ->orderBy('time_start')
            ->get();

        $slotDates = $slots->pluck('scheduled_date')
            ->map(fn($d) => is_string($d) ? $d : date('Y-m-d', strtotime($d)))
            ->unique()->values()->toArray();

        $bookings = DB::table('face_to_face_bookings')
            ->join('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
            ->leftJoin('parents', 'face_to_face_bookings.parent_id', '=', 'parents.id')
            ->where('face_to_face_bookings.teacher_id', $teacher->id)
            ->orderByDesc('consultation_slots.scheduled_date')
            ->select(
                'face_to_face_bookings.id',
                'face_to_face_bookings.purpose_of_meeting',
                'face_to_face_bookings.status',
                'consultation_slots.scheduled_date',
                'consultation_slots.time_start',
                'consultation_slots.time_end',
                'parents.name as parent_name',
                DB::raw('(SELECT s.name FROM students s WHERE s.parent_id = face_to_face_bookings.parent_id LIMIT 1) as student_name')
            )
            ->get();

        return view('teacher.consultation', compact('upcomingCount', 'slots', 'slotDates', 'bookings', 'maxPerDay'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'time_start'     => 'required|date_format:H:i',
            'time_end'       => 'required|date_format:H:i|after:time_start',
        ]);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Bug 1: prevent duplicate slots
        $duplicate = DB::table('consultation_slots')
            ->where('teacher_id', $teacher->id)
            ->where('scheduled_date', $request->scheduled_date)
            ->where('time_start', $request->time_start)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'A slot already exists for that date and time.');
        }

        // Bug 2: enforce max appointments per day
        $maxPref   = DB::table('teacher_word_set_preferences')
            ->where('teacher_id', $teacher->id)
            ->where('category', 'like', 'max_appointments_per_day:%')
            ->value('category');
        $maxPerDay = $maxPref ? (int) explode(':', $maxPref)[1] : 6;

        $slotsOnDay = DB::table('consultation_slots')
            ->where('teacher_id', $teacher->id)
            ->where('scheduled_date', $request->scheduled_date)
            ->count();

        if ($slotsOnDay >= $maxPerDay) {
            return back()->withInput()->with('error', 'You have reached the maximum number of slots for this day.');
        }

        ConsultationSlot::create([
            'teacher_id'     => $teacher->id,
            'scheduled_date' => $request->scheduled_date,
            'time_start'     => $request->time_start,
            'time_end'       => $request->time_end,
            'is_available'   => true,
        ]);

        self::log('create', 'Teacher set consultation availability');

        return back()->with('success', 'Consultation slot added.');
    }

    public function saveMaxAppointments(Request $request)
    {
        $request->validate(['max_per_day' => 'required|in:2,4,6,8']);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Delete any existing max_appointments_per_day entry (any value variant),
        // then insert fresh — avoids updating a boolean column with an integer.
        DB::table('teacher_word_set_preferences')
            ->where('teacher_id', $teacher->id)
            ->where('category', 'like', 'max_appointments_per_day:%')
            ->delete();

        DB::table('teacher_word_set_preferences')->insert([
            'teacher_id'  => $teacher->id,
            'category'    => 'max_appointments_per_day:' . $request->max_per_day,
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Max appointments per day updated.');
    }

    public function saveSchedule(Request $request)
    {
        $request->validate(['slots' => 'array']);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        foreach ($request->input('slots', []) as $slotId => $isAvailable) {
            ConsultationSlot::where('id', (int) $slotId)
                ->where('teacher_id', $teacher->id)
                ->update(['is_available' => (bool) $isAvailable]);
        }

        return back()->with('success', 'Schedule saved.');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:face_to_face_bookings,id',
            'status'     => 'required|in:Confirmed,Completed',
        ]);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $booking = DB::table('face_to_face_bookings')
            ->where('id', $request->booking_id)
            ->where('teacher_id', $teacher->id)
            ->first();

        abort_if(!$booking, 403);

        if ($request->status === 'Confirmed' && $booking->status !== 'Pending') {
            return back()->with('error', 'Can only confirm a pending booking.');
        }
        if ($request->status === 'Completed' && $booking->status !== 'Confirmed') {
            return back()->with('error', 'Can only complete a confirmed booking.');
        }

        DB::table('face_to_face_bookings')
            ->where('id', $booking->id)
            ->update(['status' => $request->status]);

        $slot = DB::table('consultation_slots')->where('id', $booking->slot_id)->first();

        DB::table('notifications')->insert([
            'recipient_id'      => $booking->parent_id,
            'recipient_role'    => 'Parent',
            'notification_type' => 'Availability',
            'action_url'        => route('parent.consultations'),
            'title'             => 'Booking Status Updated',
            'message'           => 'Your consultation booking on '
                . date('M d, Y', strtotime($slot->scheduled_date))
                . ' has been ' . strtolower($request->status) . '.',
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        $parentRecord = DB::table('parents')->where('id', $booking->parent_id)->value('name');
        self::log('update', "Teacher " . strtolower($request->status) . " booking for parent: " . ($parentRecord ?? 'Unknown'));

        return back()->with('success', 'Booking ' . strtolower($request->status) . '.');
    }

    public function destroy($slot)
    {
        $teacher   = Teacher::where('user_id', auth()->id())->firstOrFail();
        $slotModel = ConsultationSlot::findOrFail($slot);

        abort_if($slotModel->teacher_id !== $teacher->id, 403);

        $hasConfirmed = DB::table('face_to_face_bookings')
            ->where('slot_id', $slotModel->id)
            ->where('status', 'Confirmed')
            ->exists();

        if ($hasConfirmed) {
            return back()->with('error', 'Cannot delete a slot with a confirmed booking.');
        }

        $slotModel->delete();

        return back()->with('success', 'Slot deleted.');
    }
}
