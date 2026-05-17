<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EngagementRecord;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $teacher  = Teacher::where('user_id', auth()->id())->firstOrFail();
        $classIds = $teacher->classLists()->pluck('id');

        $students = Student::whereIn('class_list_id', $classIds)
            ->with('masteryScores')
            ->get()
            ->map(function (Student $s) {
                $scores          = $s->masteryScores;
                $masteredCount   = $scores->where('proficiency_level', 'Mastered')->count();
                $developingCount = $scores->where('proficiency_level', 'Developing')->count();
                $beginningCount  = $scores->where('proficiency_level', 'Beginning')->count();
                $total           = $scores->count();

                $dominant = 'None';
                if ($total > 0) {
                    if ($masteredCount >= $developingCount && $masteredCount >= $beginningCount) {
                        $dominant = 'Mastered';
                    } elseif ($developingCount >= $beginningCount) {
                        $dominant = 'Developing';
                    } else {
                        $dominant = 'Beginning';
                    }
                }

                return (object) [
                    'id'                   => $s->id,
                    'name'                 => $s->name,
                    'dominant_proficiency' => $dominant,
                    'mastered_count'       => $masteredCount,
                    'developing_count'     => $developingCount,
                    'beginning_count'      => $beginningCount,
                    'total_words'          => $total,
                ];
            });

        return view('teacher.reports', compact('students'));
    }

    public function show(Student $student)
    {
        $teacher  = Teacher::where('user_id', auth()->id())->firstOrFail();
        $classIds = $teacher->classLists()->pluck('id');

        abort_if(! $classIds->contains($student->class_list_id), 403);

        $masteryScores = $student->masteryScores()->with('vocabulary')->get();

        $progressRecords = StudentProgress::where('student_id', $student->id)
            ->with('vocabulary')
            ->orderBy('vocabulary_id')
            ->orderBy('mode')
            ->get();

        $progressByWord = [];
        foreach ($progressRecords as $record) {
            $vid = $record->vocabulary_id;
            if (! isset($progressByWord[$vid])) {
                $progressByWord[$vid] = ['vocab' => $record->vocabulary, 'modes' => []];
            }
            $progressByWord[$vid]['modes'][$record->mode] = $record;
        }

        return view('teacher.reports-detail', compact('student', 'masteryScores', 'progressByWord'));
    }

    public function send(Request $request, Student $student)
    {
        $teacher  = Teacher::where('user_id', auth()->id())->firstOrFail();
        $classIds = $teacher->classLists()->pluck('id');

        abort_if(! $classIds->contains($student->class_list_id), 403);

        if (is_null($student->parent_id)) {
            return redirect()->back()->with(
                'error',
                'This student does not have a linked parent account. Please contact the admin.'
            );
        }

        $hasProgress = StudentProgress::where('student_id', $student->id)->exists();
        if (! $hasProgress && ! $request->boolean('force')) {
            return redirect()->back()
                ->with('warning', 'This student has no learning activity to report yet. Click "Send Anyway" to send the report regardless.')
                ->with('warn_force_student', $student->id);
        }

        $parent     = ParentProfile::findOrFail($student->parent_id);
        $engagement = EngagementRecord::firstOrCreate([
            'parent_id'  => $parent->id,
            'teacher_id' => $teacher->id,
        ]);
        $engagement->update(['last_report_sent' => now()]);

        DB::table('notifications')->insert([
            'recipient_id'      => $parent->id,
            'recipient_role'    => 'Parent',
            'notification_type' => 'Milestone',
            'title'             => 'Progress Report from Teacher ' . $teacher->name,
            'message'           => $student->name . "'s progress report has been sent by their teacher. Tap to review their learning activity.",
            'action_url'        => route('parent.progress', ['student_id' => $student->id]),
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        return redirect()->back()->with('success', 'Progress report sent to ' . $parent->name . ' successfully.');
    }
}
