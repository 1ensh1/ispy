<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ClassList;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function showTeacherProfile(Teacher $teacher)
    {
        $teacher->load('user');

        $classes = ClassList::active()
            ->where('teacher_id', $teacher->id)
            ->orderBy('class_name')
            ->get();

        $archivedClasses = ClassList::archived()
            ->where('teacher_id', $teacher->id)
            ->orderBy('class_name')
            ->get();

        foreach ($classes as $class) {
            $class->setRelation(
                'activeStudents',
                Student::where('class_list_id', $class->id)
                    ->whereNull('archived_at')
                    ->with('parentUser')
                    ->get()
            );
        }

        $classIds = $classes->pluck('id');

        $currentSubs = \App\Models\ClassSubstitute::active()
            ->whereIn('class_list_id', $classIds)
            ->with(['substituteTeacher', 'classList'])
            ->get();

        $otherTeachers = Teacher::where('id', '!=', $teacher->id)
            ->orderBy('name')
            ->get();

        $unassignedClasses = ClassList::active()
            ->whereNull('teacher_id')
            ->orderBy('class_name')
            ->get();

        $recentActivity = ActivityLog::where('user_id', $teacher->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.teachers.profile', compact(
            'teacher', 'classes', 'archivedClasses', 'recentActivity', 'currentSubs', 'otherTeachers', 'unassignedClasses'
        ));
    }

    public function showParentProfile(User $user)
    {
        $parent = \App\Models\ParentUser::where('user_id', $user->id)->firstOrFail();
        $activeStudents   = Student::where('parent_id', $parent->id)->whereNull('archived_at')->get();
        $archivedStudents = Student::where('parent_id', $parent->id)->whereNotNull('archived_at')->get();
        return view('admin.parents.profile', compact('user', 'parent', 'activeStudents', 'archivedStudents'));
    }

    public function showStudentProfile(Student $student)
    {
        $student->load(['parentUser', 'classList']);

        $masteryScores = DB::table('mastery_scores')
            ->join('vocabulary_library', 'mastery_scores.vocabulary_id', '=', 'vocabulary_library.id')
            ->where('mastery_scores.student_id', $student->id)
            ->select('vocabulary_library.english_label', 'mastery_scores.total_score', 'mastery_scores.proficiency_level')
            ->orderByDesc('mastery_scores.total_score')
            ->limit(10)
            ->get();

        $recentActivity = DB::table('student_progress')
            ->join('vocabulary_library', 'student_progress.vocabulary_id', '=', 'vocabulary_library.id')
            ->where('student_progress.student_id', $student->id)
            ->select('vocabulary_library.english_label', 'student_progress.mode', 'student_progress.score', 'student_progress.attempted_at')
            ->orderByDesc('student_progress.attempted_at')
            ->limit(10)
            ->get();

        return view('admin.students.profile', compact('student', 'masteryScores', 'recentActivity'));
    }
}
