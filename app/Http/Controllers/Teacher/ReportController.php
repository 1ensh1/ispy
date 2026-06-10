<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EngagementRecord;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\Teacher;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $students = Student::where('class_list_id', $request->active_class_id)
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

    public function show(Request $request, Student $student)
    {
        abort_if($student->class_list_id !== $request->active_class_id, 403);

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
        abort_if($student->class_list_id !== $request->active_class_id, 403);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

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

        self::log('create', "sent progress report for student {$student->name}");

        return redirect()->back()->with('success', 'Progress report sent to ' . $parent->name . ' successfully.');
    }

    public function exportStudentReportCsv(Request $request, Student $student)
    {
        abort_if($student->class_list_id !== $request->active_class_id, 403);

        $rows = DB::table('student_progress')
            ->join('vocabulary_library', 'student_progress.vocabulary_id', '=', 'vocabulary_library.id')
            ->leftJoin('mastery_scores', function ($join) use ($student) {
                $join->on('mastery_scores.student_id', '=', 'student_progress.student_id')
                     ->on('mastery_scores.vocabulary_id', '=', 'student_progress.vocabulary_id');
            })
            ->where('student_progress.student_id', $student->id)
            ->select(
                'vocabulary_library.english_label as word',
                'student_progress.mode',
                'student_progress.score',
                'mastery_scores.proficiency_level',
                'student_progress.attempted_at'
            )
            ->orderBy('vocabulary_library.english_label')
            ->orderBy('student_progress.mode')
            ->get();

        $filename = strtolower(str_replace(' ', '-', $student->name)) . '-report.csv';

        return response()->streamDownload(function () use ($rows, $student) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Name', 'Vocabulary Word', 'Mode', 'Score', 'Proficiency Level', 'Attempted At']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $student->name,
                    $row->word,
                    $row->mode,
                    $row->score,
                    $row->proficiency_level ?? '—',
                    $row->attempted_at,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
