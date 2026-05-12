<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\ConsultationSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationsController extends Controller
{
    public function index()
    {
        $parent  = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $student = $parent->students()->with('classList.teacher')->first();
        $teacher = $student?->classList?->teacher;

        $bookings = DB::table('face_to_face_bookings')
            ->join('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
            ->leftJoin('teachers', 'face_to_face_bookings.teacher_id', '=', 'teachers.id')
            ->where('face_to_face_bookings.parent_id', $parent->id)
            ->orderBy('consultation_slots.scheduled_date')
            ->orderBy('consultation_slots.time_start')
            ->select(
                'face_to_face_bookings.*',
                'consultation_slots.scheduled_date',
                'consultation_slots.time_start',
                'consultation_slots.time_end',
                'teachers.name as teacher_name'
            )
            ->get();

        $teachers = collect();
        if ($student?->classList?->teacher) {
            $teachers = collect([$teacher]);
        }

        $availableDates = $teacher
            ? DB::table('consultation_slots')
                ->where('teacher_id', $teacher->id)
                ->where('is_available', true)
                ->where('scheduled_date', '>=', today()->format('Y-m-d'))
                ->pluck('scheduled_date')
                ->map(fn($d) => is_string($d) ? $d : date('Y-m-d', strtotime($d)))
                ->unique()->values()->toArray()
            : [];

        return view('parent.consultations', compact(
            'parent', 'student', 'teacher', 'bookings', 'teachers', 'availableDates'
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

    public function store(Request $request)
    {
        $request->validate([
            'slot_id'            => 'required|exists:consultation_slots,id',
            'purpose_of_meeting' => 'required|string|max:1000',
        ]);

        $parent  = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $student = $parent->students()->with('classList.teacher')->first();
        $teacher = $student?->classList?->teacher;

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

            DB::table('notifications')->insert([
                'recipient_id'      => $teacher->id,
                'recipient_role'    => 'Teacher',
                'notification_type' => 'Availability',
                'title'             => 'New Consultation Request',
                'message'           => "{$parent->name} booked a consultation on {$dateFormatted} at {$timeFormatted} for {$student->name}.",
                'is_read'           => false,
                'created_at'        => now(),
            ]);
        }

        return back()->with('success', 'Consultation booked successfully.');
    }
}
