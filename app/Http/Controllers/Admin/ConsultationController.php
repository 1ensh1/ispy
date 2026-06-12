<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceToFaceBooking;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::orderBy('name')->get();

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        // Stat cards are computed across ALL bookings (independent of filters / current page).
        $allBookings = FaceToFaceBooking::with('slot')
            ->select('face_to_face_bookings.*')
            ->get();

        $today     = today()->toDateString();
        $upcoming  = $allBookings->filter(
            fn($b) => in_array($b->status, ['Pending', 'Confirmed'])
                   && ($b->slot?->scheduled_date?->toDateString() ?? '') >= $today
        )->count();
        $completed = $allBookings->where('status', 'Completed')->count();
        $cancelled = $allBookings->where('status', 'Cancelled')->count();
        $noshow    = $allBookings->where('status', 'No-show')->count();

        // Table data: filtered + paginated server-side.
        $query = FaceToFaceBooking::with(['slot', 'teacher', 'parentProfile.students'])
            ->join('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
            ->orderByDesc('consultation_slots.scheduled_date')
            ->orderBy('consultation_slots.time_start')
            ->select('face_to_face_bookings.*');

        if ($request->filled('teacher_id') && $request->teacher_id !== 'all') {
            $query->where('face_to_face_bookings.teacher_id', $request->teacher_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('parentProfile', fn($p) => $p->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('parentProfile.students', fn($s) => $s->where('name', 'like', "%{$term}%"));
            });
        }

        $rows = $query->paginate($perPage)->appends(request()->query());

        $rows->getCollection()->transform(fn($b) => [
            'teacher_id' => $b->teacher_id,
            'teacher'    => $b->teacher?->name ?? '—',
            'parent'     => $b->parentProfile?->name ?? '—',
            'student'    => $b->parentProfile?->students->pluck('name')->implode(', ') ?: '—',
            'date'       => $b->slot?->scheduled_date?->format('M d, Y') ?? '—',
            'time'       => $b->slot ? Carbon::parse($b->slot->time_start)->format('g:i A') : '—',
            'purpose'    => $b->purpose_of_meeting ?? '',
            'status'     => $b->status,
        ]);

        return view('admin.consultations', compact(
            'rows', 'teachers', 'upcoming', 'completed', 'cancelled', 'noshow', 'perPage'
        ));
    }

    public function export(Request $request)
    {
        $query = FaceToFaceBooking::with(['slot', 'teacher', 'parentProfile.students'])
            ->join('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
            ->orderByDesc('consultation_slots.scheduled_date')
            ->select('face_to_face_bookings.*');

        if ($request->filled('teacher_id')) {
            $query->where('face_to_face_bookings.teacher_id', $request->teacher_id);
        }

        $bookings = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="consultations_' . now()->format('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-store',
        ];

        $callback = function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Teacher', 'Parent', 'Student', 'Date', 'Time', 'Purpose', 'Status']);
            foreach ($bookings as $b) {
                $students = $b->parentProfile?->students->pluck('name')->implode(', ') ?: '—';
                fputcsv($handle, [
                    $b->teacher?->name ?? '—',
                    $b->parentProfile?->name ?? '—',
                    $students,
                    $b->slot?->scheduled_date?->format('M d, Y') ?? '—',
                    $b->slot ? Carbon::parse($b->slot->time_start)->format('g:i A') : '—',
                    $b->purpose_of_meeting ?? '',
                    $b->status,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
