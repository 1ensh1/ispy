<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ClassList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = User::where('role', 'teacher');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->appends(['search' => $search]);

        $userIds = $users->pluck('id')->toArray();

        $teacherRecords = DB::table('teachers')
            ->leftJoin('class_lists', 'class_lists.teacher_id', '=', 'teachers.id')
            ->whereIn('teachers.user_id', $userIds)
            ->select(
                'teachers.id',
                'teachers.user_id',
                'class_lists.id as class_list_id',
                'class_lists.class_name',
                'class_lists.unified_classroom_pin'
            )
            ->get()
            ->keyBy('user_id');

        $teacherIds = $teacherRecords->pluck('id')->toArray();

        $rawCounts = DB::table('students')
            ->join('class_lists', 'students.class_list_id', '=', 'class_lists.id')
            ->whereIn('class_lists.teacher_id', $teacherIds)
            ->select('class_lists.teacher_id', DB::raw('COUNT(students.id) as total'))
            ->groupBy('class_lists.teacher_id')
            ->pluck('total', 'teacher_id');

        $studentCountsByUser = [];
        $classListsByUser    = [];
        foreach ($teacherRecords as $userId => $record) {
            $studentCountsByUser[$userId] = $rawCounts[$record->id] ?? 0;
            $classListsByUser[$userId]    = [
                'id'                    => $record->class_list_id,
                'class_name'            => $record->class_name,
                'unified_classroom_pin' => $record->unified_classroom_pin,
            ];
        }

        $activeTab   = 'teacher';
        $extraData   = [];

        return view('admin.users', compact(
            'users', 'activeTab', 'search', 'extraData', 'studentCountsByUser', 'classListsByUser'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'class_name' => 'required|string|max:100',
            'email'      => 'required|email|max:255|unique:users',
        ]);

        $tempPassword = Str::random(8);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($tempPassword),
            'role'     => 'teacher',
        ]);

        $teacherId = DB::table('teachers')->insertGetId([
            'user_id'    => $user->id,
            'name'       => $validated['name'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ClassList::create([
            'teacher_id'            => $teacherId,
            'class_name'            => $validated['class_name'],
            'unified_classroom_pin' => null,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher account for \"{$user->name}\" created successfully.")
            ->with('new_teacher_password', $tempPassword)
            ->with('new_teacher_name', $user->name);
    }

    public function update(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'class_name' => 'required|string|max:100',
            'email'      => 'required|email|max:255|unique:users,email,' . $teacher->id,
        ]);

        $teacher->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        $teacherRecord = DB::table('teachers')
            ->where('user_id', $teacher->id)
            ->first();

        if ($teacherRecord) {
            DB::table('teachers')
                ->where('id', $teacherRecord->id)
                ->update(['name' => $validated['name'], 'updated_at' => now()]);

            DB::table('class_lists')
                ->where('teacher_id', $teacherRecord->id)
                ->update(['class_name' => $validated['class_name']]);
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher \"{$teacher->name}\" updated successfully.");
    }

    public function destroy(User $teacher)
    {
        $record = DB::table('teachers')->where('user_id', $teacher->id)->first();

        if ($record) {
            $studentCount = DB::table('students')
                ->join('class_lists', 'students.class_list_id', '=', 'class_lists.id')
                ->where('class_lists.teacher_id', $record->id)
                ->count();

            if ($studentCount > 0) {
                return redirect()->route('admin.teachers.index')
                    ->with('error', "Cannot delete \"{$teacher->name}\": they have {$studentCount} active student(s) in their class. Reassign or remove students first.");
            }

            DB::table('class_lists')->where('teacher_id', $record->id)->delete();
            DB::table('teachers')->where('user_id', $teacher->id)->delete();
        }

        $name = $teacher->name;
        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher account for \"{$name}\" has been deleted.");
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }
}
