<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapturedObject;
use App\Models\MasteryScore;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\Teacher;
use App\Models\VocabularyLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $totalStudents = Student::count();
        $avgMastery    = round(DB::table('mastery_scores')->avg('total_score') ?? 0, 1);
        $profDist      = DB::table('mastery_scores')
            ->select('proficiency_level', DB::raw('count(*) as cnt'))
            ->groupBy('proficiency_level')
            ->pluck('cnt', 'proficiency_level');

        $teachers = Teacher::orderBy('name')->get();

        $query = Student::with(['classList.teacher', 'masteryScores']);

        if ($request->filled('teacher_id')) {
            $query->whereHas('classList', fn($q) => $q->where('teacher_id', $request->teacher_id));
        }

        $students = $query->get()->map(function ($student) {
            $scores   = $student->masteryScores;
            $dist     = $scores->groupBy('proficiency_level')->map->count();
            $dominant = $dist->sortDesc()->keys()->first() ?? 'None';
            return (object) [
                'id'         => $student->id,
                'name'       => $student->name,
                'class_name' => optional($student->classList)->class_name ?? '—',
                'teacher'    => optional(optional($student->classList)->teacher)->name ?? '—',
                'mastered'   => $dist['Mastered']   ?? 0,
                'developing' => $dist['Developing'] ?? 0,
                'beginning'  => $dist['Beginning']  ?? 0,
                'dominant'   => $dominant,
            ];
        });

        if ($request->filled('proficiency')) {
            $students = $students->filter(fn($s) => $s->dominant === $request->proficiency);
        }

        return view('admin.reports', compact(
            'students', 'teachers', 'totalStudents', 'avgMastery', 'profDist'
        ));
    }

    public function show(Student $student)
    {
        $student->load(['classList.teacher']);

        $progressQuery = StudentProgress::where('student_id', $student->id)->with('vocabulary');

        if (request()->filled('mode')) {
            $progressQuery->where('mode', request('mode'));
        }
        if (request()->filled('category')) {
            $progressQuery->whereHas('vocabulary', fn($q) => $q->where('category', request('category')));
        }

        $progress = $progressQuery->orderBy('vocabulary_id')->orderBy('mode')->get();

        $masteryScores   = MasteryScore::where('student_id', $student->id)->with('vocabulary')->get();
        $capturedObjects = CapturedObject::where('student_id', $student->id)
            ->with('vocabulary')
            ->orderByDesc('captured_at')
            ->get();

        $categories = VocabularyLibrary::distinct()->orderBy('category')->pluck('category');
        $modes      = ['Identification', 'Matching', 'Spelling'];

        return view('admin.report-detail', compact(
            'student', 'progress', 'masteryScores', 'capturedObjects', 'categories', 'modes'
        ));
    }

    public function exportStudentProgressCsv(Request $request)
    {
        $query = DB::table('student_progress')
            ->join('students', 'student_progress.student_id', '=', 'students.id')
            ->join('vocabulary_library', 'student_progress.vocabulary_id', '=', 'vocabulary_library.id')
            ->leftJoin('class_lists', 'students.class_list_id', '=', 'class_lists.id')
            ->select(
                'students.name as student_name',
                'vocabulary_library.english_label as word',
                'student_progress.mode',
                'student_progress.attempts',
                'student_progress.score',
                'student_progress.mastery_weight',
                'student_progress.attempted_at'
            )
            ->whereNull('students.archived_at');

        if ($request->filled('teacher_id')) {
            $query->where('class_lists.teacher_id', $request->teacher_id);
        }
        if ($request->filled('search')) {
            $query->where('students.name', 'like', '%' . $request->search . '%');
        }

        $rows = $query->orderBy('students.name')->orderBy('vocabulary_library.english_label')->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Name', 'Vocabulary Word', 'Mode', 'Attempts', 'Score', 'Mastery Weight', 'Attempted At']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->student_name,
                    $row->word,
                    $row->mode,
                    $row->attempts,
                    $row->score,
                    $row->mastery_weight,
                    $row->attempted_at,
                ]);
            }
            fclose($handle);
        }, 'student-progress.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportMasteryScoresCsv(Request $request)
    {
        $query = DB::table('mastery_scores')
            ->join('students', 'mastery_scores.student_id', '=', 'students.id')
            ->join('vocabulary_library', 'mastery_scores.vocabulary_id', '=', 'vocabulary_library.id')
            ->leftJoin('class_lists', 'students.class_list_id', '=', 'class_lists.id')
            ->select(
                'students.name as student_name',
                'vocabulary_library.english_label as word',
                'mastery_scores.total_score',
                'mastery_scores.proficiency_level',
                'mastery_scores.updated_at'
            )
            ->whereNull('students.archived_at');

        if ($request->filled('teacher_id')) {
            $query->where('class_lists.teacher_id', $request->teacher_id);
        }
        if ($request->filled('search')) {
            $query->where('students.name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('proficiency')) {
            $query->where('mastery_scores.proficiency_level', $request->proficiency);
        }

        $rows = $query->orderBy('students.name')->orderBy('vocabulary_library.english_label')->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Name', 'Word', 'Total Score', 'Proficiency Level', 'Updated At']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->student_name,
                    $row->word,
                    $row->total_score,
                    $row->proficiency_level,
                    $row->updated_at,
                ]);
            }
            fclose($handle);
        }, 'mastery-scores.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportCapturedObjectsCsv(Request $request)
    {
        $query = DB::table('captured_objects')
            ->join('students', 'captured_objects.student_id', '=', 'students.id')
            ->join('vocabulary_library', 'captured_objects.vocabulary_id', '=', 'vocabulary_library.id')
            ->leftJoin('class_lists', 'students.class_list_id', '=', 'class_lists.id')
            ->select(
                'students.name as student_name',
                'vocabulary_library.english_label as word',
                'captured_objects.captured_image_url',
                'captured_objects.is_successful_match',
                'captured_objects.captured_at'
            )
            ->whereNull('students.archived_at');

        if ($request->filled('teacher_id')) {
            $query->where('class_lists.teacher_id', $request->teacher_id);
        }
        if ($request->filled('search')) {
            $query->where('students.name', 'like', '%' . $request->search . '%');
        }

        $rows = $query->orderBy('students.name')->orderByDesc('captured_objects.captured_at')->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Name', 'Word', 'Captured Image URL', 'Successful Match', 'Captured At']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->student_name,
                    $row->word,
                    $row->captured_image_url ?? '',
                    $row->is_successful_match ? 'Yes' : 'No',
                    $row->captured_at,
                ]);
            }
            fclose($handle);
        }, 'captured-objects.csv', ['Content-Type' => 'text/csv']);
    }
}
