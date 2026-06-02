<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ClassList;
use App\Models\ClassSubstitute;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminSubstituteController extends Controller
{
    public function assign(Request $request)
    {
        $request->validate([
            'class_list_id'         => 'required|integer|exists:class_lists,id',
            'substitute_teacher_id' => 'required|integer|exists:teachers,id',
            'start_date'            => 'required|date',
            'end_date'              => 'nullable|date|after_or_equal:start_date',
            'teacher_id'            => 'required|integer|exists:teachers,id',
        ]);

        $class = ClassList::findOrFail($request->class_list_id);

        if ($class->teacher_id == $request->substitute_teacher_id) {
            return redirect()->route('admin.teachers.profile', ['teacher' => $request->teacher_id])
                ->withErrors([
                    'substitute_teacher_id' => 'Cannot assign the primary teacher as their own substitute.',
                ]);
        }

        ClassSubstitute::create([
            'class_list_id'         => $request->class_list_id,
            'substitute_teacher_id' => $request->substitute_teacher_id,
            'assigned_by'           => Auth::id(),
            'start_date'            => $request->start_date,
            'end_date'              => $request->end_date,
            'created_at'            => now(),
        ]);

        $subTeacher = Teacher::find($request->substitute_teacher_id);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'role'        => 'Admin',
            'action'      => 'Assign Substitute',
            'description' => "Assigned {$subTeacher->name} as substitute for class \"{$class->class_name}\" starting {$request->start_date}.",
            'created_at'  => now(),
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $subTeacher->id,
            'recipient_role'    => 'Teacher',
            'notification_type' => 'Availability',
            'action_url'        => config('app.url') . '/teacher/dashboard',
            'title'             => 'Substitute Assignment',
            'message'           => "You have been assigned as substitute teacher for \"{$class->class_name}\" starting {$request->start_date}.",
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        return redirect()->route('admin.teachers.profile', ['teacher' => $class->teacher_id])
            ->with('success', "{$subTeacher->name} assigned as substitute successfully.");
    }

    public function remove(int $id)
    {
        $sub = ClassSubstitute::with(['substituteTeacher', 'classList'])->findOrFail($id);

        $teacherName      = $sub->substituteTeacher->name ?? 'Unknown';
        $className        = $sub->classList->class_name ?? 'Unknown';
        $teacherId        = $sub->substitute_teacher_id;
        $primaryTeacherId = $sub->classList->teacher_id;

        $sub->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'role'        => 'Admin',
            'action'      => 'Remove Substitute',
            'description' => "Removed {$teacherName} as substitute for class \"{$className}\".",
            'created_at'  => now(),
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $teacherId,
            'recipient_role'    => 'Teacher',
            'notification_type' => 'Availability',
            'action_url'        => config('app.url') . '/teacher/dashboard',
            'title'             => 'Substitute Assignment Ended',
            'message'           => "Your substitute assignment for \"{$className}\" has been removed by an administrator.",
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        return redirect()->route('admin.teachers.profile', ['teacher' => $primaryTeacherId])
            ->with('success', "Substitute assignment removed.");
    }

    public function listForClass(Request $request)
    {
        $request->validate(['class_list_id' => 'required|integer|exists:class_lists,id']);

        $subs = ClassSubstitute::active()
            ->where('class_list_id', $request->class_list_id)
            ->with('substituteTeacher')
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'teacher_name' => $s->substituteTeacher->name ?? '—',
                'start_date'   => $s->start_date,
                'end_date'     => $s->end_date ?? 'Open-ended',
            ]);

        return response()->json($subs);
    }
}
