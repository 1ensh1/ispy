<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function index()
    {
        $parent  = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $student = $parent->students()->first();

        $snapshots      = collect();
        $recentActivity = collect();

        if ($student) {
            $progressRows = DB::table('student_progress')
                ->where('student_id', $student->id)
                ->where('attempted_at', '>=', now()->subDays(13)->startOfDay())
                ->selectRaw('DATE(attempted_at) as date, COUNT(DISTINCT vocabulary_id) as words, COUNT(*) as attempts, AVG(score) as avg_score')
                ->groupBy('date')
                ->orderByDesc('date')
                ->get();

            $scansByDate = DB::table('captured_objects')
                ->where('student_id', $student->id)
                ->where('captured_at', '>=', now()->subDays(13)->startOfDay())
                ->selectRaw('DATE(captured_at) as date, COUNT(*) as scans')
                ->groupBy('date')
                ->pluck('scans', 'date');

            $snapshots = $progressRows->map(function ($row) use ($scansByDate) {
                $pct = min(100, (int) round(($row->avg_score ?? 0) * 100));
                return (object) [
                    'date'     => $row->date,
                    'words'    => $row->words,
                    'scans'    => (int) ($scansByDate[$row->date] ?? 0),
                    'attempts' => $row->attempts,
                    'pct'      => $pct,
                ];
            });

            $recentActivity = DB::table('student_progress')
                ->join('vocabulary_library', 'student_progress.vocabulary_id', '=', 'vocabulary_library.id')
                ->where('student_progress.student_id', $student->id)
                ->orderBy('student_progress.attempted_at', 'desc')
                ->limit(10)
                ->select(
                    'student_progress.attempted_at',
                    'student_progress.mode',
                    'student_progress.score',
                    'vocabulary_library.english_label',
                    'vocabulary_library.filipino_label'
                )
                ->get();
        }

        return view('parent.progress', compact('parent', 'student', 'snapshots', 'recentActivity'));
    }
}
