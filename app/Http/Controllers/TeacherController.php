<?php

namespace App\Http\Controllers;

use App\Mail\TeacherAccountCreated;
use App\Models\ClassList;
use App\Models\ParentUser;
use App\Models\Student;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        // ===== TEACHERS =====
        $search = $request->query('search');
        $teacherQuery = User::where('role', 'teacher');
        if ($search) {
            $teacherQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $teacherQuery->latest()->paginate(10)->appends(['search' => $search]);

        $userIds = $users->pluck('id')->toArray();

        // Fetch all rows (one per class per teacher) to support multiple classes
        $allTeacherRows = DB::table('teachers')
            ->leftJoin('class_lists', function ($join) {
                $join->on('class_lists.teacher_id', '=', 'teachers.id')
                     ->whereNull('class_lists.archived_at');
            })
            ->whereIn('teachers.user_id', $userIds)
            ->select(
                'teachers.id',
                'teachers.user_id',
                'teachers.status',
                'class_lists.id as class_list_id',
                'class_lists.class_name',
                'class_lists.subject',
                'class_lists.unified_classroom_pin'
            )
            ->get();

        $teacherRowsByUserId = $allTeacherRows->groupBy('user_id');
        $teacherIds = $allTeacherRows->pluck('id')->unique()->values()->toArray();

        $rawCounts = DB::table('students')
            ->join('class_lists', 'students.class_list_id', '=', 'class_lists.id')
            ->whereIn('class_lists.teacher_id', $teacherIds)
            ->whereNull('students.archived_at')
            ->select('class_lists.teacher_id', DB::raw('COUNT(students.id) as total'))
            ->groupBy('class_lists.teacher_id')
            ->pluck('total', 'teacher_id');

        $studentCountsByUser = [];
        $classListsByUser    = [];
        foreach ($teacherRowsByUserId as $userId => $rows) {
            $firstRow = $rows->first();
            $teacherId = $firstRow->id;
            $studentCountsByUser[$userId] = $rawCounts[$teacherId] ?? 0;
            $allClassNames = $rows->pluck('class_name')->filter()->unique()->join(', ');
            $allClasses    = $rows->filter(fn($r) => $r->class_list_id !== null)
                ->map(fn($r) => [
                    'id'      => $r->class_list_id,
                    'name'    => $r->class_name,
                    'subject' => $r->subject,
                    'pin'     => $r->unified_classroom_pin,
                ])->values()->all();
            $classListsByUser[$userId] = [
                'teacher_id'            => $firstRow->id,
                'id'                    => $firstRow->class_list_id,
                'class_name'            => $firstRow->class_name,
                'all_class_names'       => $allClassNames ?: null,
                'unified_classroom_pin' => $firstRow->unified_classroom_pin,
                'all_classes'           => $allClasses,
                'status'                => $firstRow->status ?? 'Active',
            ];
        }

        // ===== PARENTS =====
        $parentSearch = $request->query('parent_search');
        $parentQuery = User::where('role', 'parent');
        if ($parentSearch) {
            $parentQuery->where(function ($q) use ($parentSearch) {
                $q->where('name', 'like', "%{$parentSearch}%")
                  ->orWhere('email', 'like', "%{$parentSearch}%");
            });
        }
        $parentUsers = $parentQuery->latest()->paginate(10, ['*'], 'parent_page')
            ->appends(['parent_search' => $parentSearch, 'tab' => 'parents']);

        $extraData = [];
        $parentUserIds = $parentUsers->pluck('id')->toArray();
        if ($parentUserIds) {
            $parentRecords   = DB::table('parents')->whereIn('user_id', $parentUserIds)->get()->keyBy('user_id');
            $parentIds       = $parentRecords->pluck('id')->toArray();
            $childrenByParent = $parentIds
                ? DB::table('students')->whereIn('parent_id', $parentIds)->whereNull('archived_at')->get()->groupBy('parent_id')
                : collect();
            foreach ($parentUsers as $user) {
                $pr = $parentRecords->get($user->id);
                $pid = $pr ? $pr->id : null;
                $extraData[$user->id] = [
                    'children' => ($pid && $childrenByParent->has($pid))
                        ? $childrenByParent->get($pid)
                        : collect(),
                ];
            }
        }

        // ===== STUDENTS =====
        $students    = Student::active()->with(['parentUser', 'classList.teacher'])
            ->paginate(15, ['*'], 'student_page')
            ->appends(['tab' => 'students']);
        $parentsList = ParentUser::orderBy('name')->get();
        $classLists  = ClassList::active()->with('teacher')->orderBy('class_name')->get();

        $activeTab = 'teacher';

        return view('admin.users', compact(
            'users', 'activeTab', 'search', 'extraData', 'studentCountsByUser', 'classListsByUser',
            'parentUsers', 'parentSearch',
            'students', 'parentsList', 'classLists'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'class_name' => 'required|string|max:100',
            'email'      => 'required|email|max:255|unique:users',
        ]);

        $tempPassword = Str::random(10);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($tempPassword),
            'role'     => 'teacher',
        ]);

        $teacherId = DB::table('teachers')->insertGetId([
            'user_id'    => $user->id,
            'name'       => $validated['name'],
            'status'     => 'Inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ClassList::create([
            'teacher_id'            => $teacherId,
            'class_name'            => $validated['class_name'],
            'unified_classroom_pin' => null,
        ]);

        self::log('create', "Created teacher account for {$user->name} ({$user->email})");

        // Generate a one-time activation token and build the activation link.
        $token = Str::random(64);
        DB::table('teacher_activation_tokens')->insert([
            'teacher_id' => $teacherId,
            'token'      => $token,
            'created_at' => now(),
        ]);
        $activationUrl = route('teacher.activate', ['token' => $token]);

        try {
            Mail::to($user->email)->send(new TeacherAccountCreated($user->name, $user->email, $tempPassword, $activationUrl));
        } catch (\Throwable $e) {
            Log::error("Failed to send teacher account email to {$user->email}: {$e->getMessage()}");
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher account created. A credentials email has been sent to {$user->email}.");
    }

    public function resendActivation(User $teacher)
    {
        $record = DB::table('teachers')->where('user_id', $teacher->id)->first();

        if (!$record) {
            return redirect()->route('admin.teachers.index')
                ->with('error', "Teacher record not found for \"{$teacher->name}\".");
        }

        // Replace any existing token so only the newest link works.
        DB::table('teacher_activation_tokens')->where('teacher_id', $record->id)->delete();

        $token = Str::random(64);
        DB::table('teacher_activation_tokens')->insert([
            'teacher_id' => $record->id,
            'token'      => $token,
            'created_at' => now(),
        ]);
        $activationUrl = route('teacher.activate', ['token' => $token]);

        try {
            Mail::to($teacher->email)->send(new TeacherAccountCreated($teacher->name, $teacher->email, '(your existing temporary password)', $activationUrl));
        } catch (\Throwable $e) {
            Log::error("Failed to resend teacher activation email to {$teacher->email}: {$e->getMessage()}");
        }

        self::log('update', "Resent activation email to {$teacher->name} ({$teacher->email})");

        return redirect()->route('admin.teachers.index')
            ->with('success', "Activation email resent to {$teacher->email}.");
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

        self::log('update', "Admin updated teacher: {$validated['name']}");

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
                ->whereNull('students.archived_at')
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

        self::log('delete', "Admin deleted teacher: {$name}");

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher account for \"{$name}\" has been deleted.");
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }
}
