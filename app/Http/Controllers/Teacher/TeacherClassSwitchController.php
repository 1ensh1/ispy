<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Models\ClassSubstitute;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherClassSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['class_id' => 'required|integer']);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $ownClassIds = ClassList::where('teacher_id', $teacher->id)
            ->pluck('id');

        $subClassIds = ClassSubstitute::active()
            ->where('substitute_teacher_id', $teacher->id)
            ->pluck('class_list_id');

        $accessible = $ownClassIds->concat($subClassIds)->unique();

        if (!$accessible->contains($request->class_id)) {
            return redirect()->back()->withErrors(['class_id' => 'You do not have access to that class.']);
        }

        session(['active_class_id' => (int) $request->class_id]);

        return redirect()->back()->with('success', 'Class switched successfully.');
    }
}
