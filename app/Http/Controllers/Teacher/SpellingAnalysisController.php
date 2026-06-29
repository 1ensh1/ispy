<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\Student;
use App\Models\StudentProgress;
use Illuminate\Http\Request;

class SpellingAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $classList = ClassList::find($request->active_class_id);

        $barChartData  = ['labels' => [], 'data' => []];
        $phonemeCounts = array_fill_keys(['a', 'e', 'i', 'o', 'u', 'k', 't', 's', 'n', 'l', 'p', 'r'], 0);

        if ($classList) {
            $studentIds = Student::where('class_list_id', $classList->id)->pluck('id');

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

            foreach ($errorRows as $row) {
                $attempt = strtoupper(trim((string) ($row->errors[0] ?? '')));
                $correct = strtoupper(trim((string) ($row->vocabulary?->english_label ?? '')));
                if ($attempt === '' || $correct === '') continue;

                $maxLen = max(strlen($attempt), strlen($correct));
                $attempt = str_pad($attempt, $maxLen, '*');
                $correct = str_pad($correct, $maxLen, '*');

                for ($i = 0; $i < $maxLen; $i++) {
                    if ($attempt[$i] !== $correct[$i]) {
                        $phoneme = strtolower($correct[$i]);
                        if (array_key_exists($phoneme, $phonemeCounts)) {
                            $phonemeCounts[$phoneme]++;
                        }
                    }
                }
            }
        }

        $topPhoneme = '';
        $topCount   = 0;
        foreach ($phonemeCounts as $letter => $count) {
            if ($count > $topCount) {
                $topPhoneme = $letter;
                $topCount   = $count;
            }
        }

        return view('teacher.spelling-analysis', compact('barChartData', 'phonemeCounts', 'topPhoneme', 'topCount'));
    }
}
