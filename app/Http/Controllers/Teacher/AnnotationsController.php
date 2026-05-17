<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAnnotation;
use Illuminate\Http\Request;

class AnnotationsController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $classIds = $teacher->classLists()->pluck('id');
        $students = \App\Models\Student::whereIn('class_list_id', $classIds)
            ->orderBy('name')
            ->get();

        $annotations = TeacherAnnotation::where('teacher_id', $teacher->id)
            ->with('student')
            ->orderByDesc('annotation_date')
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.annotations', compact('annotations', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'      => 'required|integer|exists:students,id',
            'annotation_date' => 'required|date',
            'note'            => 'required|string|max:2000',
            'tags'            => 'nullable|string',
        ]);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $tags = collect(explode(',', $request->input('tags', '')))
            ->map(fn($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        TeacherAnnotation::create([
            'teacher_id'      => $teacher->id,
            'student_id'      => $request->student_id,
            'annotation_date' => $request->annotation_date,
            'note'            => $request->note,
            'tags'            => $tags ?: null,
            'created_at'      => now(),
        ]);

        return back()->with('success', 'Annotation saved.');
    }
}
