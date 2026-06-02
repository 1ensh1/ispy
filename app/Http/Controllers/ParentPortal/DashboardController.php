<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\FaceToFaceBooking;
use App\Models\MasteryScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $parent   = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $students = $parent->students()->active()->with('classList.teacher')->get();

        if ($request->filled('student_id')) {
            $student = $students->firstWhere('id', (int) $request->query('student_id'));
            abort_if(!$student, 403);
        } else {
            $student = $students->first();
        }

        $wordsMastered  = 0;
        $wordsThisWeek  = 0;
        $pronunciationScore = 0;
        $nextConsultation   = null;
        $activityData = ['labels' => [], 'data' => []];
        $recentWords  = collect();
        $unreadCount  = 0;

        if ($student) {
            $wordsMastered = DB::table('mastery_scores')
                ->where('student_id', $student->id)
                ->where('proficiency_level', 'Mastered')
                ->count();

            $wordsThisWeek = DB::table('mastery_scores')
                ->where('student_id', $student->id)
                ->where('proficiency_level', 'Mastered')
                ->where('updated_at', '>=', now()->startOfWeek())
                ->count();

            $rawScore = DB::table('student_progress')
                ->where('student_id', $student->id)
                ->where('mode', 'Spelling')
                ->avg('score') ?? 0;
            $pronunciationScore = min(100, (int) round($rawScore * 100));

            $nextConsultation = DB::table('face_to_face_bookings')
                ->join('consultation_slots', 'face_to_face_bookings.slot_id', '=', 'consultation_slots.id')
                ->leftJoin('teachers', 'face_to_face_bookings.teacher_id', '=', 'teachers.id')
                ->where('face_to_face_bookings.parent_id', $parent->id)
                ->whereIn('face_to_face_bookings.status', ['Pending', 'Confirmed'])
                ->where('consultation_slots.scheduled_date', '>=', today()->format('Y-m-d'))
                ->orderBy('consultation_slots.scheduled_date')
                ->orderBy('consultation_slots.time_start')
                ->select(
                    'face_to_face_bookings.*',
                    'consultation_slots.scheduled_date',
                    'consultation_slots.time_start',
                    'teachers.name as teacher_name'
                )
                ->first();

            $progressByDate = DB::table('student_progress')
                ->where('student_id', $student->id)
                ->where('attempted_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(attempted_at) as date, COUNT(*) as attempts')
                ->groupBy('date')
                ->pluck('attempts', 'date');

            $labels = [];
            $data   = [];
            for ($i = 6; $i >= 0; $i--) {
                $date    = now()->subDays($i)->format('Y-m-d');
                $labels[] = now()->subDays($i)->format('D');
                $data[]   = (int) ($progressByDate[$date] ?? 0);
            }
            $activityData = ['labels' => $labels, 'data' => $data];

            $recentWords = DB::table('mastery_scores')
                ->join('vocabulary_library', 'mastery_scores.vocabulary_id', '=', 'vocabulary_library.id')
                ->where('mastery_scores.student_id', $student->id)
                ->orderBy('mastery_scores.updated_at', 'desc')
                ->limit(5)
                ->select(
                    'mastery_scores.proficiency_level',
                    'vocabulary_library.english_label',
                    'vocabulary_library.filipino_label'
                )
                ->get();
        }

        $unreadCount = DB::table('notifications')
            ->where('recipient_id', $parent->id)
            ->where('recipient_role', 'Parent')
            ->where('is_read', false)
            ->count();

        return view('parent.dashboard', compact(
            'parent', 'students', 'student', 'wordsMastered', 'wordsThisWeek',
            'pronunciationScore', 'nextConsultation', 'activityData',
            'recentWords', 'unreadCount'
        ));
    }

    public function changePassword(Request $request)
    {
        $parent = ParentProfile::where('user_id', auth()->id())->firstOrFail();

        if ($request->filled('student_id')) {
            $student = \App\Models\Student::active()->where('id', $request->input('student_id'))->firstOrFail();
            abort_if($student->parent_id !== $parent->id, 403);
        } else {
            $student = \App\Models\Student::active()->where('parent_id', $parent->id)->first();
        }

        if (!$student) {
            return back()->withErrors(['current_password' => 'No linked student found for your account.'])->withInput();
        }

        if ($request->current_password !== $student->parent_password) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        if ($request->new_password !== $request->new_password_confirmation) {
            return back()->withErrors(['new_password' => 'Passwords do not match.'])->withInput();
        }

        if ($request->new_password === $request->current_password) {
            return back()->withErrors(['new_password' => 'New password must be different from the current password.'])->withInput();
        }

        $student->parent_password = $request->new_password;
        $student->save();

        return back()->with('success', 'Parent password updated successfully.');
    }
}
