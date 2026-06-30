<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\ConsultationSlot;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationsController extends Controller
{
    use LogsActivity;
    public function index()
    {
        $parent = ParentProfile::where('user_id', auth()->id())->firstOrFail();

        // All of the parent's active (non-archived) children, across every class.
        $students     = $parent->students()->active()->get();
        $classListIds = $students->pluck('class_list_id')->filter()->unique()->values();

        // Source of truth: class_subjects (non-archived, with an assigned teacher).
        // The legacy class_lists.teacher_id column is intentionally NOT used here.
        $assignments = ClassSubject::query()
            ->whereNull('archived_at')
            ->whereIn('class_list_id', $classListIds)
            ->whereNotNull('teacher_id')
            ->with(['teacher', 'classList'])
            ->get()
            ->filter(fn($cs) => $cs->teacher);   // drop rows whose teacher was deleted

        // Deduplicate by teacher: each teacher appears once, with the classes/subjects
        // (across this parent's children) they teach as disambiguating context, e.g.
        // "John Enriquez — Diamond (English, Filipino) · Sapphire (English)".
        $teachers = $assignments
            ->groupBy('teacher_id')
            ->map(function ($group) {
                $teacher = $group->first()->teacher;

                $context = $group
                    ->groupBy(fn($cs) => $cs->classList->class_name ?? 'Class')
                    ->map(fn($rows, $class) => $class . ' (' . $rows->pluck('subject')->unique()->implode(', ') . ')')
                    ->values()
                    ->implode(' · ');

                return (object) [
                    'id'      => $teacher->id,
                    'name'    => $teacher->name,
                    'context' => $context,
                ];
            })
            ->sortBy('name')
            ->values();

        // Upcoming available consultation dates per teacher, so the calendar dots
        // can update when the parent switches the selected teacher.
        $teacherIds = $teachers->pluck('id')->all();
        $availableDatesByTeacher = empty($teacherIds) ? [] : DB::table('consultation_slots')
            ->whereIn('teacher_id', $teacherIds)
            ->where('is_available', true)
            ->where('scheduled_date', '>=', today()->format('Y-m-d'))
            ->get(['teacher_id', 'scheduled_date'])
            ->groupBy('teacher_id')
            ->map(fn($rows) => $rows
                ->pluck('scheduled_date')
                ->map(fn($d) => is_string($d) ? substr($d, 0, 10) : date('Y-m-d', strtotime($d)))
                ->unique()->values()->all())
            ->toArray();

        // Dates for the initially-selected (first) teacher, for the first calendar paint.
        $firstTeacherId = $teachers->first()->id ?? null;
        $availableDates = $firstTeacherId ? ($availableDatesByTeacher[$firstTeacherId] ?? []) : [];

        $bookings = DB::table('face_to_face_bookings')
            ->leftJoin('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
            ->leftJoin('teachers', 'face_to_face_bookings.teacher_id', '=', 'teachers.id')
            ->where('face_to_face_bookings.parent_id', $parent->id)
            ->orderByDesc('face_to_face_bookings.id')
            ->select(
                'face_to_face_bookings.id',
                'face_to_face_bookings.slot_id',
                'face_to_face_bookings.teacher_id',
                'face_to_face_bookings.parent_id',
                'face_to_face_bookings.purpose_of_meeting',
                'face_to_face_bookings.status',
                'face_to_face_bookings.created_at',
                'face_to_face_bookings.updated_at',
                'consultation_slots.scheduled_date',
                'consultation_slots.time_start',
                'consultation_slots.time_end',
                'teachers.name as teacher_name'
            )
            ->get();

        return view('parent.consultations', compact(
            'parent', 'students', 'bookings', 'teachers',
            'availableDates', 'availableDatesByTeacher'
        ));
    }

    public function slots(Request $request)
    {
        $slots = DB::table('consultation_slots')
            ->where('teacher_id', $request->teacher_id)
            ->where('scheduled_date', $request->date)
            ->where('is_available', true)
            ->select('id', 'time_start', 'time_end')
            ->get();

        return response()->json($slots);
    }

    public function cancel($booking)
    {
        $parent  = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $booking = DB::table('face_to_face_bookings')
            ->where('id', $booking)
            ->where('parent_id', $parent->id)
            ->first();

        abort_if(!$booking, 403);

        if (!in_array($booking->status, ['Pending', 'Confirmed'])) {
            return back()->with('error', 'Only pending or confirmed bookings can be cancelled.');
        }

        DB::table('face_to_face_bookings')
            ->where('id', $booking->id)
            ->update(['status' => 'Cancelled', 'updated_at' => now()]);

        DB::table('consultation_slots')
            ->where('id', $booking->slot_id)
            ->update(['is_available' => true]);

        $slot = DB::table('consultation_slots')->where('id', $booking->slot_id)->first();

        DB::table('notifications')->insert([
            'recipient_id'      => $booking->teacher_id,
            'recipient_role'    => 'Teacher',
            'notification_type' => 'Availability',
            'action_url'        => route('teacher.consultation'),
            'title'             => 'Consultation Cancelled',
            'message'           => $parent->name . ' cancelled their consultation on '
                . date('M d, Y', strtotime($slot->scheduled_date)) . '.',
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        self::log('update', 'cancelled consultation booking');

        return back()->with('success', 'Booking cancelled successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'slot_id'            => 'required|exists:consultation_slots,id',
            'purpose_of_meeting' => 'required|string|max:300',
        ]);

        $parent = ParentProfile::where('user_id', auth()->id())->firstOrFail();

        $slot = ConsultationSlot::findOrFail($request->slot_id);

        if (!$slot->is_available) {
            return back()->with('error', 'This time slot is no longer available.');
        }

        $existing = DB::table('face_to_face_bookings')
            ->where('slot_id', $slot->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'This slot has already been booked. Please select another.');
        }

        // The booked teacher is the owner of the chosen slot (source of truth).
        $teacher = Teacher::find($slot->teacher_id);

        // Best-effort: identify which of this parent's children this teacher is
        // actually assigned to (via class_subjects), so the notification can name
        // the relevant child. Falls back to the first active child if ambiguous.
        $childClassIds   = $parent->students()->active()->pluck('class_list_id')->filter();
        $relevantClassId = ClassSubject::whereNull('archived_at')
            ->where('teacher_id', $slot->teacher_id)
            ->whereIn('class_list_id', $childClassIds)
            ->value('class_list_id');
        $student = $relevantClassId
            ? $parent->students()->active()->where('class_list_id', $relevantClassId)->first()
            : $parent->students()->active()->first();

        DB::table('face_to_face_bookings')->insert([
            'slot_id'            => $slot->id,
            'teacher_id'         => $slot->teacher_id,
            'parent_id'          => $parent->id,
            'purpose_of_meeting' => $request->purpose_of_meeting,
            'status'             => 'Pending',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $slot->update(['is_available' => false]);

        if ($teacher) {
            $dateFormatted = date('M d, Y', strtotime($slot->scheduled_date));
            $timeFormatted = date('h:i A', strtotime($slot->time_start));
            $childName     = $student?->name ?? 'their child';

            DB::table('notifications')->insert([
                'recipient_id'      => $teacher->id,
                'recipient_role'    => 'Teacher',
                'notification_type' => 'Availability',
                'action_url'        => route('teacher.consultation'),
                'title'             => 'New Consultation Request',
                'message'           => "{$parent->name} booked a consultation on {$dateFormatted} at {$timeFormatted} for {$childName}.",
                'is_read'           => false,
                'created_at'        => now(),
            ]);
        }

        self::log('create', "booked consultation with teacher " . ($teacher?->name ?? 'Unknown'));

        return back()->with('success', 'Consultation booked successfully.');
    }
}
