<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\ConsultationSlot;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Auto-complete past Confirmed bookings silently on page load
        $pastSlotIds = DB::table('consultation_slots')
            ->where('teacher_id', $teacher->id)
            ->where('scheduled_date', '<', today()->format('Y-m-d'))
            ->pluck('id');

        if ($pastSlotIds->isNotEmpty()) {
            DB::table('face_to_face_bookings')
                ->whereIn('slot_id', $pastSlotIds)
                ->where('status', 'Confirmed')
                ->update(['status' => 'Completed']);
        }

        // Silently delete unbooked out-of-range slots (outside 08:00–17:00)
        $bookedSlotIds = DB::table('face_to_face_bookings')->pluck('slot_id')->unique();
        $outOfRangeIds = DB::table('consultation_slots')
            ->where('teacher_id', $teacher->id)
            ->where('is_available', true)
            ->where(function ($q) {
                $q->where('time_start', '<', '08:00:00')
                  ->orWhere('time_end', '>', '17:00:00');
            })
            ->whereNotIn('id', $bookedSlotIds)
            ->pluck('id');
        if ($outOfRangeIds->isNotEmpty()) {
            DB::table('consultation_slots')->whereIn('id', $outOfRangeIds)->delete();
        }

        $todayMonday = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $rangeStart  = $todayMonday->copy()->subWeeks(4);
        $rangeEnd    = $todayMonday->copy()->addWeeks(8)->addDays(4); // through Friday of week +8

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

        $slotsCollection = ConsultationSlot::where('teacher_id', $teacher->id)
            ->whereBetween('scheduled_date', [
                $rangeStart->format('Y-m-d'),
                $rangeEnd->format('Y-m-d'),
            ])
            ->orderBy('scheduled_date')
            ->orderBy('time_start')
            ->get();

        // Fetch booking data (id, status, parent name) per slot — latest booking wins via keyBy.
        $slotIds     = $slotsCollection->pluck('id')->toArray();
        $bookingData = $slotIds
            ? DB::table('face_to_face_bookings')
                ->leftJoin('parents', 'face_to_face_bookings.parent_id', '=', 'parents.id')
                ->whereIn('face_to_face_bookings.slot_id', $slotIds)
                ->where('face_to_face_bookings.status', '!=', 'Rejected') // Rejected = slot freed; treat as no booking
                ->orderBy('face_to_face_bookings.id') // ascending so highest id (latest) wins on keyBy
                ->select(
                    'face_to_face_bookings.slot_id',
                    'face_to_face_bookings.id as booking_id',
                    'face_to_face_bookings.status',
                    'parents.name as parent_name'
                )
                ->get()
                ->keyBy('slot_id')
            : collect();

        $allSlots = $slotsCollection->map(fn($s) => [
            'id'             => $s->id,
            'scheduled_date' => Carbon::parse($s->scheduled_date)->format('Y-m-d'),
            'time_start'     => $s->time_start,
            'time_end'       => $s->time_end,
            'is_available'   => (bool) $s->is_available,
            'booking_status' => $bookingData->has($s->id) ? $bookingData->get($s->id)->status      : null,
            'parent_name'    => $bookingData->has($s->id) ? $bookingData->get($s->id)->parent_name : null,
            'booking_id'     => $bookingData->has($s->id) ? $bookingData->get($s->id)->booking_id  : null,
        ])->values()->toArray();

        $slotDates = $slotsCollection->pluck('scheduled_date')
            ->map(fn($d) => is_string($d) ? $d : date('Y-m-d', strtotime($d)))
            ->unique()->values()->toArray();

        $calendarBookingData = $slotIds
            ? DB::table('face_to_face_bookings')
                ->leftJoin('parents', 'face_to_face_bookings.parent_id', '=', 'parents.id')
                ->whereIn('face_to_face_bookings.slot_id', $slotIds)
                ->orderBy('face_to_face_bookings.id')
                ->select(
                    'face_to_face_bookings.slot_id',
                    'face_to_face_bookings.status',
                    'face_to_face_bookings.purpose_of_meeting',
                    'parents.name as parent_name',
                    DB::raw('(SELECT s.name FROM students s WHERE s.parent_id = face_to_face_bookings.parent_id LIMIT 1) as student_name')
                )
                ->get()
                ->keyBy('slot_id')
            : collect();

        $calendarSlots = $slotsCollection->map(fn($s) => [
            'id'             => $s->id,
            'scheduled_date' => Carbon::parse($s->scheduled_date)->format('Y-m-d'),
            'time_start'     => $s->time_start,
            'time_end'       => $s->time_end,
            'is_available'   => (bool) $s->is_available,
            'booking_status' => $calendarBookingData->has($s->id) ? $calendarBookingData->get($s->id)->status             : null,
            'parent_name'    => $calendarBookingData->has($s->id) ? $calendarBookingData->get($s->id)->parent_name        : null,
            'student_name'   => $calendarBookingData->has($s->id) ? $calendarBookingData->get($s->id)->student_name       : null,
            'purpose'        => $calendarBookingData->has($s->id) ? $calendarBookingData->get($s->id)->purpose_of_meeting : null,
        ])->values()->toArray();

        $bookings = DB::table('face_to_face_bookings')
            ->join('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
            ->leftJoin('parents', 'face_to_face_bookings.parent_id', '=', 'parents.id')
            ->where('face_to_face_bookings.teacher_id', $teacher->id)
            ->orderByRaw("CASE WHEN face_to_face_bookings.status = 'Pending' THEN 0 ELSE 1 END")
            ->orderByDesc('consultation_slots.scheduled_date')
            ->select(
                'face_to_face_bookings.id',
                'face_to_face_bookings.slot_id',
                'face_to_face_bookings.purpose_of_meeting',
                'face_to_face_bookings.status',
                'consultation_slots.scheduled_date',
                'consultation_slots.time_start',
                'consultation_slots.time_end',
                'parents.name as parent_name',
                DB::raw('(SELECT s.name FROM students s WHERE s.parent_id = face_to_face_bookings.parent_id LIMIT 1) as student_name')
            )
            ->get();

        return view('teacher.consultation', compact(
            'upcomingCount', 'allSlots', 'slotDates', 'calendarSlots', 'bookings', 'maxPerDay', 'todayMonday'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'time_start'     => 'required|date_format:H:i',
            'time_end'       => 'required|date_format:H:i|after:time_start',
        ]);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        // Time overlap check: new_start < existing_end AND new_end > existing_start
        $conflicting = DB::table('consultation_slots')
            ->where('teacher_id', $teacher->id)
            ->where('scheduled_date', $request->scheduled_date)
            ->where('time_start', '<', $request->time_end)
            ->where('time_end', '>', $request->time_start)
            ->first();

        if ($conflicting) {
            $startFmt = date('g:i A', strtotime($conflicting->time_start));
            $endFmt   = date('g:i A', strtotime($conflicting->time_end));
            return back()->withInput()->with('error', "This time slot overlaps with an existing slot ({$startFmt} – {$endFmt}).");
        }

        // Time range restriction: 08:00 AM – 5:00 PM only
        if ($request->time_start < '08:00' || $request->time_end > '17:00') {
            return back()->withInput()->with('error', 'Slots must be scheduled between 8:00 AM and 5:00 PM.');
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

        if ($request->status === 'Completed') {
            $slot = DB::table('consultation_slots')->where('id', $booking->slot_id)->first();
            if ($slot) {
                $meetingEnd = Carbon::parse($slot->scheduled_date . ' ' . $slot->time_end);
                if (Carbon::now()->lt($meetingEnd)) {
                    return back()->with('error', 'Cannot mark complete before the meeting has ended.');
                }
            }
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

        // Cancel any active booking (Pending or Confirmed) — keep the record
        $activeBooking = DB::table('face_to_face_bookings')
            ->where('slot_id', $slotModel->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->first();

        if ($activeBooking) {
            $originalStatus = $activeBooking->status;
            DB::table('face_to_face_bookings')
                ->where('id', $activeBooking->id)
                ->update(['status' => 'Cancelled']);
        }

        $slotDate  = $slotModel->scheduled_date;
        $slotStart = $slotModel->time_start;
        $slotModel->delete();

        if ($activeBooking) {
            DB::table('notifications')->insert([
                'recipient_id'      => $activeBooking->parent_id,
                'recipient_role'    => 'Parent',
                'notification_type' => 'Availability',
                'action_url'        => route('parent.consultations'),
                'title'             => 'Consultation Cancelled',
                'message'           => 'Your consultation booking on '
                    . date('M d, Y', strtotime($slotDate))
                    . ' at ' . date('g:i A', strtotime($slotStart))
                    . ' has been cancelled by the teacher.',
                'is_read'           => false,
                'created_at'        => now(),
            ]);

            $parentName = DB::table('parents')->where('id', $activeBooking->parent_id)->value('name');
            self::log('cancel', 'Teacher cancelled slot with ' . strtolower($originalStatus) . ' booking for parent: ' . ($parentName ?? 'Unknown'));
        } else {
            self::log('cancel', 'Teacher cancelled consultation slot');
        }

        return back()->with('success', 'Slot deleted.');
    }

    public function confirmBooking($bookingId)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $booking = DB::table('face_to_face_bookings')
            ->where('id', $bookingId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'Pending')
            ->first();

        abort_if(!$booking, 403);

        DB::table('face_to_face_bookings')
            ->where('id', $booking->id)
            ->update(['status' => 'Confirmed']);

        $slot = DB::table('consultation_slots')->where('id', $booking->slot_id)->first();

        DB::table('notifications')->insert([
            'recipient_id'      => $booking->parent_id,
            'recipient_role'    => 'Parent',
            'notification_type' => 'Availability',
            'action_url'        => route('parent.consultations'),
            'title'             => 'Consultation Confirmed',
            'message'           => 'Your consultation booking on '
                . date('M d, Y', strtotime($slot->scheduled_date))
                . ' at ' . date('g:i A', strtotime($slot->time_start))
                . ' has been confirmed by your teacher.',
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        $parentName = DB::table('parents')->where('id', $booking->parent_id)->value('name');
        self::log('update', 'Teacher confirmed booking for parent: ' . ($parentName ?? 'Unknown'));

        return back()->with('success', 'Booking confirmed.');
    }

    public function rejectBooking($bookingId)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $booking = DB::table('face_to_face_bookings')
            ->where('id', $bookingId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'Pending')
            ->first();

        abort_if(!$booking, 403);

        DB::table('face_to_face_bookings')
            ->where('id', $booking->id)
            ->update(['status' => 'Rejected']);

        $slot = DB::table('consultation_slots')->where('id', $booking->slot_id)->first();

        // Free the slot so other parents can book it
        DB::table('consultation_slots')
            ->where('id', $booking->slot_id)
            ->update(['is_available' => true]);

        DB::table('notifications')->insert([
            'recipient_id'      => $booking->parent_id,
            'recipient_role'    => 'Parent',
            'notification_type' => 'Availability',
            'action_url'        => route('parent.consultations'),
            'title'             => 'Consultation Rejected',
            'message'           => 'Your consultation booking on '
                . date('M d, Y', strtotime($slot->scheduled_date))
                . ' at ' . date('g:i A', strtotime($slot->time_start))
                . ' has been rejected by your teacher.',
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        $parentName = DB::table('parents')->where('id', $booking->parent_id)->value('name');
        self::log('update', 'Teacher rejected booking for parent: ' . ($parentName ?? 'Unknown'));

        return back()->with('success', 'Booking rejected.');
    }

    public function completeBooking($bookingId)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $booking = DB::table('face_to_face_bookings')
            ->where('id', $bookingId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'Confirmed')
            ->first();

        abort_if(!$booking, 403);

        $slot = DB::table('consultation_slots')->where('id', $booking->slot_id)->first();

        // Only allow marking complete after the meeting end time has passed
        $meetingEnd = Carbon::parse($slot->scheduled_date . ' ' . $slot->time_end);
        if (Carbon::now()->lt($meetingEnd)) {
            return response()->json(['error' => 'Cannot mark complete before the meeting has ended.'], 403);
        }

        DB::table('face_to_face_bookings')
            ->where('id', $booking->id)
            ->update(['status' => 'Completed']);

        DB::table('notifications')->insert([
            'recipient_id'      => $booking->parent_id,
            'recipient_role'    => 'Parent',
            'notification_type' => 'Availability',
            'action_url'        => route('parent.consultations'),
            'title'             => 'Consultation Completed',
            'message'           => 'Your consultation on '
                . date('M d, Y', strtotime($slot->scheduled_date))
                . ' at ' . date('g:i A', strtotime($slot->time_start))
                . ' has been marked as completed.',
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        $parentName = DB::table('parents')->where('id', $booking->parent_id)->value('name');
        self::log('update', 'Teacher marked booking as complete for parent: ' . ($parentName ?? 'Unknown'));

        return back()->with('success', 'Booking marked as complete.');
    }
}
