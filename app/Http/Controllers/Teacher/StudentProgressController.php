<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentProgressController extends Controller
{
    public function index(Request $request)
    {
        $classList = ClassList::find($request->active_class_id);

        $students = collect();

        if ($classList) {
            $students = Student::where('class_list_id', $classList->id)
                ->with(['masteryScores', 'capturedObjects', 'studentProgress'])
                ->get()
                ->map(function (Student $student) {
                    $spellingScores = $student->studentProgress->where('mode', 'Spelling');

                    $pronunciationAvg = $spellingScores->isNotEmpty()
                        ? round($spellingScores->avg('score'))
                        : 0;

                    return (object) [
                        'id'            => $student->id,
                        'name'          => $student->name,
                        'last_active'   => $student->studentProgress->max('attempted_at'),
                        'words_mastered'=> $student->masteryScores->where('proficiency_level', 'Mastered')->count(),
                        'scans'         => $student->capturedObjects->count(),
                        'pronunciation' => $pronunciationAvg,
                    ];
                });
        }

        return view('teacher.student-progress', compact('students', 'classList'));
    }
}
