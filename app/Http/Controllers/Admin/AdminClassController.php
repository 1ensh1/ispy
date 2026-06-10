<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
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
        ]);

        $class = ClassList::active()->findOrFail($request->class_list_id);

        if ($class->teacher_id !== null) {
            return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
                ->withErrors(['class_list_id' => 'This class is already assigned to a teacher.']);
        }

        $teacher = Teacher::findOrFail($request->teacher_id);

        $class->teacher_id = $teacher->id;
        $class->save();

        self::log('Assign Class', "assigned class {$class->class_name} to teacher {$teacher->name}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$class->class_name}\" assigned to {$teacher->name}.");
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

        $teacherName = optional(Teacher::find($class->teacher_id))->name ?? 'Unknown';

        $class->teacher_id = null;
        $class->save();

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

        $class = ClassList::create([
            'class_name'            => $request->class_name,
            'subject'               => $request->subject,
            'teacher_id'            => $teacher->id,
            'unified_classroom_pin' => $pin,
        ]);

        self::log('Create and Assign Class', "created class '{$class->class_name}' ({$request->subject}) and assigned to {$teacher->name}. PIN: {$pin}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$class->class_name}\" created and assigned to {$teacher->name}. PIN: {$pin}.");
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
                ->withErrors(['delete_class' => 'Cannot delete a class that still has active students.']);
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
                ->withErrors(['delete_class' => 'Cannot delete a class that has active substitute assignments. Remove all substitutes first.']);
        }

        $teacher = Teacher::find($class->teacher_id);
        $className   = $class->class_name;
        $subject     = $class->subject ?? 'No subject';
        $teacherName = $teacher->name ?? 'Unknown';

        $class->archived_at = now();
        $class->save();

        self::log('Archive Class', "archived class {$className} ({$subject}) from teacher {$teacherName}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$className}\" archived successfully.");
    }

    public function restoreClass(int $id, Request $request)
    {
        $request->validate(['teacher_id' => 'required|integer|exists:teachers,id']);

        $class = ClassList::withoutGlobalScopes()->findOrFail($id);

        $teacher     = Teacher::find($class->teacher_id);
        $className   = $class->class_name;
        $subject     = $class->subject ?? 'No subject';
        $teacherName = $teacher->name ?? 'Unknown';

        $class->archived_at = null;
        $class->save();

        self::log('Restore Class', "restored class {$className} ({$subject}) for teacher {$teacherName}");

        return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
            ->with('success', "Class \"{$className}\" restored successfully.");
    }
}
