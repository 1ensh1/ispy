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
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
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
}
