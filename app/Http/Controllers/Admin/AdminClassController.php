<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Services\ClassSubjectService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class AdminClassController extends Controller
{
    use LogsActivity;

    public function __construct(private ClassSubjectService $classSubjects)
    {
    }

    public function assignClass(Request $request)
    {
        $request->validate([
            'class_list_id' => 'required|integer|exists:class_lists,id',
            'teacher_id'    => 'required|integer|exists:teachers,id',
            'subjects'      => 'required|array|min:1',
            'subjects.*'    => 'in:English,Filipino',
        ]);

        $class   = ClassList::active()->findOrFail($request->class_list_id);
        $teacher = Teacher::findOrFail($request->teacher_id);

        ['created' => $created, 'skipped' => $skipped, 'blocked' => $blocked]
            = $this->classSubjects->assign($class->id, $teacher->id, $request->subjects);

        // Nothing created — surface why (blocked takes precedence as the actionable error).
        if (empty($created)) {
            if (! empty($blocked)) {
                $parts = [];
                foreach ($blocked as $subject => $holder) {
                    $parts[] = "{$subject} is currently assigned to {$holder}";
                }
                return redirect()->route('admin.teachers.profile', ['teacher' => $teacher->id])
                    ->withErrors(['subjects' => implode('; ', $parts) . '. Unassign them first.']);
            }

            return redirect()->route('admin.teachers.profile', ['teacher' => $teacher->id])
                ->withErrors(['subjects' => 'This teacher is already assigned the selected subject(s) for this class.']);
        }

        self::log('Assign Class', "assigned class {$class->class_name} (" . implode(', ', $created) . ") to teacher {$teacher->name}");

        $message = "Class \"{$class->class_name}\" (" . implode(', ', $created) . ") assigned to {$teacher->name}.";
        if (! empty($skipped)) {
            $message .= ' Skipped already-assigned: ' . implode(', ', $skipped) . '.';
        }
        if (! empty($blocked)) {
            $blockedParts = [];
            foreach ($blocked as $subject => $holder) {
                $blockedParts[] = "{$subject} (held by {$holder})";
            }
            $message .= ' Skipped — already assigned to another teacher: ' . implode(', ', $blockedParts) . '.';
        }

        return redirect()->route('admin.teachers.profile', ['teacher' => $teacher->id])
            ->with('success', $message);
    }

    public function unassignClass(Request $request, int $classListId)
    {
        $request->validate(['teacher_id' => 'required|integer|exists:teachers,id']);

        $class = ClassList::findOrFail($classListId);

        $subjects = ClassSubject::where('class_list_id', $class->id)
            ->where('teacher_id', $request->teacher_id)
            ->whereNull('archived_at')
            ->pluck('subject')
            ->unique()
            ->values();

        // Active students ⇒ a subject can't be left without a teacher. Block the
        // whole unassign (the class-level guard applies to every subject equally).
        if ($subjects->isNotEmpty() && $this->classSubjects->classHasActiveStudents($class->id)) {
            $list = $subjects->implode(', ');
            return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
                ->withErrors(['unassign' => "Cannot unassign: {$list} has active students in this class. Assign another teacher to {$list} first, or move/archive the students."]);
        }

        $teacherName = optional(Teacher::find($request->teacher_id))->name ?? 'Unknown';

        foreach ($subjects as $subject) {
            $this->classSubjects->unassign($class->id, $request->teacher_id, $subject);
        }

        self::log('Unassign Class', "unassigned class {$class->class_name} from teacher {$teacherName}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$class->class_name}\" unassigned.");
    }

}
