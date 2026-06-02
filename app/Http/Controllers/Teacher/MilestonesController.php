<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MilestonesController extends Controller
{
    public function index(Request $request)
    {
        $classList = ClassList::find($request->active_class_id);

        $students = collect();

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
        }

        return view('teacher.milestones', compact('students', 'classList'));
    }
}
