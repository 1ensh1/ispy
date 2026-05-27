<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProficiencyController extends Controller
{
    public function index(Request $request)
    {
        $parent   = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $students = $parent->students()->get();

        if ($request->filled('student_id')) {
            $student = $students->firstWhere('id', (int) $request->query('student_id'));
            abort_if(!$student, 403);
        } else {
            $student = $students->first();
        }

        $overallPercent = 0;
        $overallLevel   = 'Beginner';
        $categories     = [];

        if ($student) {
            $rawOverall = DB::table('mastery_scores')
                ->where('student_id', $student->id)
                ->avg('total_score') ?? 0;
            $overallPercent = min(100, (int) round($rawOverall * 100));
            $overallLevel   = $this->getLevel($overallPercent);

            $cvcFil = DB::table('mastery_scores')
                ->join('vocabulary_library', 'mastery_scores.vocabulary_id', '=', 'vocabulary_library.id')
                ->where('mastery_scores.student_id', $student->id)
                ->where('vocabulary_library.complexity_level', 1)
                ->avg('mastery_scores.total_score') ?? 0;

            $cvcEng = DB::table('student_progress')
                ->join('vocabulary_library', 'student_progress.vocabulary_id', '=', 'vocabulary_library.id')
                ->where('student_progress.student_id', $student->id)
                ->where('vocabulary_library.complexity_level', 1)
                ->where('student_progress.mode', 'English')
                ->avg('student_progress.score') ?? 0;

            $multiFil = DB::table('mastery_scores')
                ->join('vocabulary_library', 'mastery_scores.vocabulary_id', '=', 'vocabulary_library.id')
                ->where('mastery_scores.student_id', $student->id)
                ->where('vocabulary_library.complexity_level', '>=', 2)
                ->avg('mastery_scores.total_score') ?? 0;

            $multiEng = DB::table('student_progress')
                ->join('vocabulary_library', 'student_progress.vocabulary_id', '=', 'vocabulary_library.id')
                ->where('student_progress.student_id', $student->id)
                ->where('vocabulary_library.complexity_level', '>=', 2)
                ->where('student_progress.mode', 'English')
                ->avg('student_progress.score') ?? 0;

            $totalCaptures = DB::table('captured_objects')
                ->where('student_id', $student->id)->count();
            $successCaptures = DB::table('captured_objects')
                ->where('student_id', $student->id)
                ->where('is_successful_match', true)->count();
            $objRecogn = $totalCaptures > 0 ? ($successCaptures / $totalCaptures) : 0;

            $pronunciation = DB::table('student_progress')
                ->where('student_id', $student->id)
                ->where('mode', 'Spelling')
                ->avg('score') ?? 0;

            $categories = [
                ['label' => 'CVC Words (Filipino)',      'percent' => min(100, (int) round($cvcFil * 100))],
                ['label' => 'CVC Words (English)',        'percent' => min(100, (int) round($cvcEng * 100))],
                ['label' => 'Multi-syllabic (Filipino)',  'percent' => min(100, (int) round($multiFil * 100))],
                ['label' => 'Multi-syllabic (English)',   'percent' => min(100, (int) round($multiEng * 100))],
                ['label' => 'Object Recognition',         'percent' => min(100, (int) round($objRecogn * 100))],
                ['label' => 'Pronunciation Accuracy',     'percent' => min(100, (int) round($pronunciation * 100))],
            ];

            foreach ($categories as &$cat) {
                $cat['level'] = $this->getLevel($cat['percent']);
            }
        }

        return view('parent.proficiency', compact(
            'parent', 'students', 'student', 'overallPercent', 'overallLevel', 'categories'
        ));
    }

    private function getLevel(int $percent): string
    {
        if ($percent >= 80) return 'Proficient';
        if ($percent >= 50) return 'Developing';
        return 'Beginner';
    }
}
