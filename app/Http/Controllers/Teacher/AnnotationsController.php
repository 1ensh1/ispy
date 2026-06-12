<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAnnotation;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class AnnotationsController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $students = Student::where('class_list_id', $request->active_class_id)
            ->orderBy('name')
            ->get();

        $studentIds = Student::where('class_list_id', $request->active_class_id)
            ->whereNull('archived_at')
            ->pluck('id');

        $annotations = TeacherAnnotation::whereIn('student_id', $studentIds)
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

        abort_if(
            Student::where('id', $request->student_id)
                ->where('class_list_id', $request->active_class_id)
                ->doesntExist(),
            403
        );

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

        $annotatedStudent = Student::find($request->student_id);
        self::log('create', "added annotation for student " . ($annotatedStudent?->name ?? 'Unknown'));

        return back()->with('success', 'Annotation saved.');
    }
}
