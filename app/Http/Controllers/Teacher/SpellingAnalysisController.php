<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\Teacher;

class SpellingAnalysisController extends Controller
{
    public function index()
    {
        $teacher   = Teacher::where('user_id', auth()->id())->firstOrFail();
        $classList = ClassList::where('teacher_id', $teacher->id)->first();

        $barChartData  = ['labels' => [], 'data' => []];
        $phonemeCounts = array_fill_keys(['a', 'e', 'i', 'o', 'u', 'k', 't', 's', 'n', 'l', 'p', 'r'], 0);

        if ($classList) {
            $studentIds = Student::where('class_list_id', $classList->id)->pluck('id');

            // Bar chart: top 5 vocabulary words by total error count
            $errorRows = StudentProgress::whereIn('student_id', $studentIds)
                ->whereNotNull('errors')
                ->with('vocabulary')
                ->get();

            $vocabCounts = [];
            foreach ($errorRows as $row) {
                if (empty($row->errors)) continue;
                $vid = $row->vocabulary_id;
                $vocabCounts[$vid] = ($vocabCounts[$vid] ?? 0) + count((array) $row->errors);
            }
            arsort($vocabCounts);
            $top5 = array_slice($vocabCounts, 0, 5, true);

            $vocabMap = $errorRows->keyBy('vocabulary_id')
                ->map(fn($r) => $r->vocabulary?->english_label ?? 'Unknown');

            foreach ($top5 as $vid => $count) {
                $barChartData['labels'][] = ucfirst($vocabMap[$vid] ?? 'Unknown');
                $barChartData['data'][]   = $count;
            }

            // Phoneme heatmap: count letter occurrences across all error strings
            $phonemes  = array_keys($phonemeCounts);
            $allErrors = StudentProgress::whereIn('student_id', $studentIds)
                ->whereNotNull('errors')
                ->pluck('errors');

            foreach ($allErrors as $errArray) {
                foreach ((array) $errArray as $err) {
                    $lower = strtolower((string) $err);
                    foreach ($phonemes as $letter) {
                        $phonemeCounts[$letter] += substr_count($lower, $letter);
                    }
                }
            }
        }

        return view('teacher.spelling-analysis', compact('barChartData', 'phonemeCounts'));
    }
}
