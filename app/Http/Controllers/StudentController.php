<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\ParentUser;
use App\Models\ClassList;

class StudentController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $showArchived = $request->boolean('show_archived');

        $students = $showArchived
            ? Student::archived()->with(['parentUser', 'classList.teacher'])->paginate(15)
            : Student::active()->with(['parentUser', 'classList.teacher'])->paginate(15);

        $parents    = ParentUser::orderBy('name')->get();
        $classLists = ClassList::with('teacher')->orderBy('class_name')->get();

        return view('admin.students.index', compact('students', 'parents', 'classLists', 'showArchived'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'profile_icon'    => 'required|string|in:cat,dog,bear,rabbit,fox,frog,penguin,lion',
            'parent_id'       => 'nullable|exists:parents,id',
            'class_list_id'   => 'nullable|exists:class_lists,id',
            'parent_password' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['parent_id'])) {
            $activeCount = Student::active()->where('parent_id', $validated['parent_id'])->count();
            if ($activeCount >= 10) {
                return back()->withErrors(['parent_id' => 'This parent already has 10 active students.'])->withInput();
            }
        }

        $parentPassword = $request->input('parent_password') ?: Str::random(8);

        Student::create([
            'name'            => $validated['name'],
            'profile_icon'    => $validated['profile_icon'],
            'parent_id'       => $validated['parent_id'] ?? null,
            'class_list_id'   => $validated['class_list_id'] ?? null,
            'parent_password' => $parentPassword,
        ]);

        self::log('create', "Admin created student: {$validated['name']}");

        return redirect()->route('admin.teachers.index', ['tab' => 'students'])
            ->with('new_student_name', $validated['name'])
            ->with('new_student_password', $parentPassword);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'parent_id'       => 'nullable|exists:parents,id',
            'class_list_id'   => 'nullable|exists:class_lists,id',
            'profile_icon'    => 'required|string|in:cat,dog,bear,rabbit,fox,frog,penguin,lion',
            'parent_password' => 'nullable|string|max:255',
        ]);

        $newParentId = $validated['parent_id'] ?: null;

        if ($newParentId && $newParentId != $student->parent_id) {
            $activeCount = Student::active()
                ->where('parent_id', $newParentId)
                ->where('id', '!=', $student->id)
                ->count();
            if ($activeCount >= 10) {
                return back()->withErrors(['parent_id' => 'This parent already has 10 active students.'])->withInput();
            }
        }

        $updateData = [
            'parent_id'     => $newParentId,
            'class_list_id' => $validated['class_list_id'] ?: null,
            'profile_icon'  => $validated['profile_icon'],
        ];

        if (!empty($validated['parent_password'])) {
            $updateData['parent_password'] = $validated['parent_password'];
        }

        $student->update($updateData);

        return redirect()->route('admin.teachers.index', ['tab' => 'students'])
            ->with('success', "Assignments for \"{$student->name}\" saved successfully.");
    }

    public function archive(Student $student)
    {
        $student->update(['archived_at' => now()]);

        self::log('archive', "Admin archived student: {$student->name}");

        return redirect()->route('admin.students')
            ->with('success', "Student \"{$student->name}\" has been archived.");
    }

    public function restore(Student $student)
    {
        if ($student->parent_id) {
            $activeCount = Student::active()->where('parent_id', $student->parent_id)->count();
            if ($activeCount >= 10) {
                return back()->withErrors(['restore' => 'Cannot restore: this parent already has 10 active students.']);
            }
        }

        $student->update(['archived_at' => null]);

        self::log('restore', "Admin restored student: {$student->name}");

        return redirect()->route('admin.students', ['show_archived' => 1])
            ->with('success', "Student \"{$student->name}\" has been restored.");
    }
}
