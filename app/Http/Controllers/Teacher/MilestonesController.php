<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MilestonesController extends Controller
{
    public function index(Request $request)
    {
        $classList = ClassList::find($request->active_class_id);

        $students   = collect();
        $leaderboard = collect();

        if ($classList) {
            $weekStart    = now()->startOfWeek(Carbon::MONDAY);
            $daysThisWeek = $weekStart->diffInDays(now()->copy()->startOfDay()) + 1;

            $students = Student::where('class_list_id', $classList->id)
                ->with(['masteryScores.vocabulary', 'studentProgress'])
                ->get()
                ->map(function (Student $student) use ($weekStart, $daysThisWeek) {
                    $mastery  = $student->masteryScores;
                    $progress = $student->studentProgress;

                    $cvcScores   = $mastery->filter(fn($ms) => $ms->vocabulary?->complexity_level === 1);
                    $multiScores = $mastery->filter(fn($ms) => $ms->vocabulary && $ms->vocabulary->complexity_level >= 2);
                    $cvcAvg      = $cvcScores->isNotEmpty()   ? min(100, round($cvcScores->avg('total_score')))   : 0;
                    $multiAvg    = $multiScores->isNotEmpty() ? min(100, round($multiScores->avg('total_score'))) : 0;

                    $cvcChampion = $cvcAvg >= 80;

                    $fastLearner = $mastery
                        ->filter(fn($ms) => $ms->updated_at?->gte($weekStart))
                        ->count() >= 3;

                    $activeDays = $progress
                        ->filter(fn($sp) => $sp->attempted_at?->gte($weekStart))
                        ->map(fn($sp) => $sp->attempted_at->toDateString())
                        ->unique()
                        ->count();
                    $consistent = $activeDays >= $daysThisWeek;

                    return (object) [
                        'name'         => $student->name,
                        'cvc_mastery'  => $cvcAvg,
                        'multi_mastery'=> $multiAvg,
                        'cvc_champion' => $cvcChampion,
                        'fast_learner' => $fastLearner,
                        'consistent'   => $consistent,
                    ];
                });

            // Build leaderboard: active students sorted by total mastery score (dense rank)
            $rawStudents = Student::where('class_list_id', $classList->id)
                ->whereNull('archived_at')
                ->with('masteryScores')
                ->get();

            // Top-5 most-attempted words per student (single bulk query, PostgreSQL-compatible)
            $studentIds = $rawStudents->pluck('id')->all();
            $frequentWordsByStudent = $studentIds
                ? DB::table('student_progress as sp')
                    ->join('vocabulary_library as vl', 'vl.id', '=', 'sp.vocabulary_id')
                    ->leftJoin('mastery_scores as ms', function ($join) {
                        $join->on('ms.student_id', '=', 'sp.student_id')
                             ->on('ms.vocabulary_id', '=', 'sp.vocabulary_id');
                    })
                    ->whereIn('sp.student_id', $studentIds)
                    ->select(
                        'sp.student_id',
                        'sp.vocabulary_id',
                        DB::raw('SUM(sp.attempts) as total_attempts'),
                        'vl.english_label',
                        'vl.filipino_label',
                        'vl.category',
                        'ms.proficiency_level'
                    )
                    ->groupBy('sp.student_id', 'sp.vocabulary_id', 'vl.english_label', 'vl.filipino_label', 'vl.category', 'ms.proficiency_level')
                    ->orderBy('sp.student_id')
                    ->orderByDesc(DB::raw('SUM(sp.attempts)'))
                    ->get()
                    ->groupBy('student_id')
                    ->map(fn($rows) => $rows->take(5)->values())
                : collect();

            $scored = $rawStudents->map(function (Student $student) use ($frequentWordsByStudent) {
                $ms = $student->masteryScores;
                return [
                    'student_name'     => $student->name,
                    'profile_icon'     => $student->profile_icon,
                    'mastery_total'    => (float) $ms->sum('total_score'),
                    'mastered_count'   => $ms->where('proficiency_level', 'Mastered')->count(),
                    'developing_count' => $ms->where('proficiency_level', 'Developing')->count(),
                    'beginning_count'  => $ms->where('proficiency_level', 'Beginning')->count(),
                    'total_words'      => $ms->count(),
                    'frequent_words'   => $frequentWordsByStudent->get($student->id, collect())->all(),
                ];
            })->sortByDesc('mastery_total')->values();

            $rank      = 1;
            $prevScore = null;
            $leaderboard = $scored->map(function (array $entry) use (&$rank, &$prevScore) {
                if ($prevScore !== null && $entry['mastery_total'] < $prevScore) {
                    $rank++;
                }
                $prevScore = $entry['mastery_total'];
                return array_merge($entry, [
                    'rank'          => $rank,
                    'mastery_total' => number_format($entry['mastery_total'], 1),
                ]);
            });
        }

        return view('teacher.milestones', compact('students', 'classList', 'leaderboard'));
    }
}
