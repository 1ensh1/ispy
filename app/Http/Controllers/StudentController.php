<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\ParentUser;
use App\Models\ClassList;

class StudentController extends Controller
{
    public function index()
    {
        $students   = Student::with(['parentUser', 'classList.teacher'])->paginate(15);
        $parents    = ParentUser::orderBy('name')->get();
        $classLists = ClassList::with('teacher')->orderBy('class_name')->get();

        return view('admin.students.index', compact('students', 'parents', 'classLists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'profile_icon'  => 'required|string|in:cat,dog,bear,rabbit,fox,frog,penguin,lion',
            'parent_id'     => 'nullable|exists:parents,id',
            'class_list_id' => 'nullable|exists:class_lists,id',
        ]);

        $parentPassword = Str::random(6);

        Student::create([
            'name'            => $validated['name'],
            'profile_icon'    => $validated['profile_icon'],
            'parent_id'       => $validated['parent_id'] ?? null,
            'class_list_id'   => $validated['class_list_id'] ?? null,
            'parent_password' => $parentPassword,
        ]);

        return redirect()->route('admin.students')
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

        $updateData = [
            'parent_id'     => $validated['parent_id']     ?: null,
            'class_list_id' => $validated['class_list_id'] ?: null,
            'profile_icon'  => $validated['profile_icon'],
        ];

        if (!empty($validated['parent_password'])) {
            $updateData['parent_password'] = $validated['parent_password'];
        }

        $student->update($updateData);

        return redirect()->route('admin.students')
            ->with('success', "Assignments for \"{$student->name}\" saved successfully.");
    }

    public function destroy(Student $student)
    {
        $name = $student->name;

        DB::table('captured_objects')->where('student_id', $student->id)->delete();
        DB::table('student_progress')->where('student_id', $student->id)->delete();
        DB::table('mastery_scores')->where('student_id', $student->id)->delete();
        $student->delete();

        return redirect()->route('admin.students')
            ->with('success', "Student \"{$name}\" has been deleted.");
    }
}
