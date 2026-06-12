<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\ClassSubject;
use App\Models\ClassSubstitute;
use App\Models\Student;
use App\Models\Teacher;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminClassController extends Controller
{
    use LogsActivity;

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

        $created = [];
        $skipped = [];

        foreach (array_unique($request->subjects) as $subject) {
            $exists = ClassSubject::where('class_list_id', $class->id)
                ->where('teacher_id', $teacher->id)
                ->where('subject', $subject)
                ->whereNull('archived_at')
                ->exists();

            if ($exists) {
                $skipped[] = $subject;
                continue;
            }

            ClassSubject::create([
                'class_list_id' => $class->id,
                'teacher_id'    => $teacher->id,
                'subject'       => $subject,
                'created_at'    => now(),
            ]);
            $created[] = $subject;
        }

        if (empty($created)) {
            return redirect()->route('admin.teachers.profile', ['teacher' => $teacher->id])
                ->withErrors(['subjects' => 'This teacher is already assigned the selected subject(s) for this class.']);
        }

        self::log('Assign Class', "assigned class {$class->class_name} (" . implode(', ', $created) . ") to teacher {$teacher->name}");

        $message = "Class \"{$class->class_name}\" (" . implode(', ', $created) . ") assigned to {$teacher->name}.";
        if (! empty($skipped)) {
            $message .= ' Skipped already-assigned: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('admin.teachers.profile', ['teacher' => $teacher->id])
            ->with('success', $message);
    }

    public function unassignClass(Request $request, int $classListId)
    {
        $request->validate(['teacher_id' => 'required|integer|exists:teachers,id']);

        $class = ClassList::findOrFail($classListId);

        $hasActiveStudents = Student::where('class_list_id', $class->id)
            ->whereNull('archived_at')
            ->exists();

        if ($hasActiveStudents) {
            return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
                ->withErrors(['unassign' => 'Cannot unassign a class that still has active students.']);
        }

        $teacherName = optional(Teacher::find($request->teacher_id))->name ?? 'Unknown';

        // Remove this teacher's active subject assignments for the class.
        ClassSubject::where('class_list_id', $class->id)
            ->where('teacher_id', $request->teacher_id)
            ->whereNull('archived_at')
            ->delete();

        self::log('Unassign Class', "unassigned class {$class->class_name} from teacher {$teacherName}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$class->class_name}\" unassigned.");
    }

    public function createAndAssign(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'subject'    => 'required|in:English,Filipino',
            'teacher_id' => 'required|integer|exists:teachers,id',
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);

        do {
            $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (ClassList::where('unified_classroom_pin', $pin)->exists());

        // class_subjects is the source of truth — leave class_lists.teacher_id
        // and class_lists.subject null, matching ClassController::store().
        $class = ClassList::create([
            'class_name'            => $request->class_name,
            'subject'               => null,
            'teacher_id'            => null,
            'unified_classroom_pin' => $pin,
        ]);

        ClassSubject::create([
            'class_list_id' => $class->id,
            'teacher_id'    => $teacher->id,
            'subject'       => $request->subject,
            'created_at'    => now(),
        ]);

        self::log('Create and Assign Class', "created class '{$class->class_name}' ({$request->subject}) and assigned to {$teacher->name}. PIN: {$pin}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$class->class_name}\" created and assigned to {$teacher->name} ({$request->subject}). PIN: {$pin}.");
    }

    public function updateSubject(Request $request)
    {
        $request->validate([
            'class_list_id' => 'required|integer|exists:class_lists,id',
            'subject'       => 'required|in:English,Filipino',
            'teacher_id'    => 'required|integer|exists:teachers,id',
        ]);

        $class = ClassList::findOrFail($request->class_list_id);
        $class->subject = $request->subject;
        $class->save();

        self::log('Update Class Subject', "updated subject for class {$class->class_name} to {$request->subject}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Subject for \"{$class->class_name}\" updated to {$request->subject}.");
    }

    public function archiveClass(int $id, Request $request)
    {
        $request->validate(['teacher_id' => 'required|integer|exists:teachers,id']);

        $class = ClassList::findOrFail($id);

        $hasActiveStudents = Student::where('class_list_id', $class->id)
            ->whereNull('archived_at')
            ->exists();

        if ($hasActiveStudents) {
            return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
                ->withErrors(['delete_class' => 'Cannot archive a class that still has active students.']);
        }

        $today = now()->toDateString();
        $hasActiveSubs = DB::table('class_substitutes')
            ->where('class_list_id', $class->id)
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->exists();

        if ($hasActiveSubs) {
            return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
                ->withErrors(['delete_class' => 'Cannot archive a class that has active substitute assignments. Remove all substitutes first.']);
        }

        $teacherName = optional(Teacher::find($request->teacher_id))->name ?? 'Unknown';
        $className   = $class->class_name;

        // Archive this teacher's active subject assignments for the class.
        ClassSubject::where('class_list_id', $class->id)
            ->where('teacher_id', $request->teacher_id)
            ->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        self::log('Archive Class', "archived class {$className} for teacher {$teacherName}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$className}\" archived successfully.");
    }

    public function restoreClass(int $id, Request $request)
    {
        $request->validate(['teacher_id' => 'required|integer|exists:teachers,id']);

        $class = ClassList::withoutGlobalScopes()->findOrFail($id);

        $teacherName = optional(Teacher::find($request->teacher_id))->name ?? 'Unknown';
        $className   = $class->class_name;

        // Restore this teacher's archived subject assignments for the class.
        ClassSubject::where('class_list_id', $class->id)
            ->where('teacher_id', $request->teacher_id)
            ->whereNotNull('archived_at')
            ->update(['archived_at' => null]);

        self::log('Restore Class', "restored class {$className} for teacher {$teacherName}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$className}\" restored successfully.");
    }
}
