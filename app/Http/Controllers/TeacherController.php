<?php

namespace App\Http\Controllers;

use App\Mail\TeacherAccountCreated;
use App\Models\ClassList;
use App\Models\ClassSubject;
use App\Models\ParentUser;
use App\Models\Student;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    use LogsActivity;

    /**
     * Resolve a per-page query value, accepting only 10, 20, 50 (default 10).
     */
    private function perPage(Request $request, string $key): int
    {
        $value = (int) $request->query($key, 10);
        return in_array($value, [10, 20, 50], true) ? $value : 10;
    }

    public function index(Request $request)
    {
        // ===== TEACHERS =====
        $search = $request->query('search');
        $teachersPerPage = $this->perPage($request, 'teachers_per_page');
        $teacherQuery = User::where('role', 'teacher');
        if ($search) {
            $teacherQuery->where('name', 'ILIKE', "%{$search}%");
        }
        $users = $teacherQuery->latest()->paginate($teachersPerPage)->appends(request()->query());

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
                'teachers.profile_picture',
                'class_lists.id as class_list_id',
                'class_lists.class_name',
                'class_lists.subject',
                'class_lists.unified_classroom_pin'
            )
            ->get();

        $teacherRowsByUserId = $allTeacherRows->groupBy('user_id');
        $teacherIds = $allTeacherRows->pluck('id')->unique()->values()->toArray();

        // Active subject assignments (class_subjects) per teacher. This is the
        // source of truth for the Classes column and the Edit Teacher modal —
        // class_lists.teacher_id is no longer read for display.
        $activeSubjectsByTeacher = $teacherIds
            ? DB::table('class_subjects')
                ->join('class_lists', 'class_subjects.class_list_id', '=', 'class_lists.id')
                ->whereIn('class_subjects.teacher_id', $teacherIds)
                ->whereNull('class_subjects.archived_at')
                ->select(
                    'class_subjects.teacher_id',
                    'class_subjects.class_list_id',
                    'class_subjects.subject',
                    'class_lists.class_name',
                    'class_lists.unified_classroom_pin'
                )
                ->orderBy('class_lists.class_name')
                ->orderBy('class_subjects.subject')
                ->get()
                ->groupBy('teacher_id')
            : collect();

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
            // Class + subject assignments come from class_subjects (grouped per
            // class so each class shows its subjects and a single PIN control).
            $subjectRows      = $activeSubjectsByTeacher->get($teacherId, collect());
            $classAssignments = $subjectRows
                ->groupBy('class_list_id')
                ->map(function ($classRows) {
                    $first = $classRows->first();
                    return [
                        'class_list_id' => $first->class_list_id,
                        'class_name'    => $first->class_name,
                        'pin'           => $first->unified_classroom_pin,
                        'subjects'      => $classRows->pluck('subject')->unique()->values()->all(),
                    ];
                })->values()->all();

            // Edit-modal pre-fill: first active class assignment + its subjects.
            $editClassListId  = $subjectRows->isNotEmpty() ? $subjectRows->first()->class_list_id : null;
            $editSubjects     = $editClassListId
                ? $subjectRows->where('class_list_id', $editClassListId)->pluck('subject')->unique()->values()->all()
                : [];

            $classListsByUser[$userId] = [
                'teacher_id'            => $firstRow->id,
                'id'                    => $firstRow->class_list_id,
                'class_name'            => $firstRow->class_name,
                'unified_classroom_pin' => $firstRow->unified_classroom_pin,
                'class_assignments'     => $classAssignments,
                'status'                => $firstRow->status ?? 'Active',
                'profile_picture'       => $firstRow->profile_picture ?? null,
                'edit_class_list_id'    => $editClassListId,
                'edit_subjects'         => $editSubjects,
            ];
        }

        // ===== PARENTS =====
        $parentSearch = $request->query('parent_search');
        $parentsPerPage = $this->perPage($request, 'parents_per_page');
        $parentQuery = User::where('role', 'parent');
        if ($parentSearch) {
            $parentQuery->where('name', 'ILIKE', "%{$parentSearch}%");
        }
        $parentUsers = $parentQuery->latest()->paginate($parentsPerPage, ['*'], 'parent_page')
            ->appends(request()->query());

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
                    'children'        => ($pid && $childrenByParent->has($pid))
                        ? $childrenByParent->get($pid)
                        : collect(),
                    'profile_picture' => $pr ? ($pr->profile_picture ?? null) : null,
                ];
            }
        }

        // ===== STUDENTS =====
        $studentsPerPage = $this->perPage($request, 'students_per_page');
        $studentSearch = $request->query('student_search');
        $studentQuery = Student::active()->with(['parentUser', 'classList.teacher']);
        if ($studentSearch) {
            $studentQuery->where('name', 'ILIKE', '%' . $studentSearch . '%');
        }
        $students    = $studentQuery->paginate($studentsPerPage, ['*'], 'student_page')
            ->appends(request()->query());
        $parentsList      = ParentUser::orderBy('name')->get();
        $classLists       = ClassList::active()->with('teacher')->orderBy('class_name')->get();
        $archivedStudents = Student::archived()->with(['parentUser', 'classList.teacher'])->get();

        $admins = DB::table('administrators')
            ->join('users', 'administrators.user_id', '=', 'users.id')
            ->select('administrators.id', 'administrators.name', 'administrators.profile_picture', 'users.email')
            ->orderBy('administrators.id')
            ->get();

        $activeTab = 'teacher';

        return view('admin.users', compact(
            'users', 'activeTab', 'search', 'extraData', 'studentCountsByUser', 'classListsByUser',
            'parentUsers', 'parentSearch',
            'students', 'archivedStudents', 'parentsList', 'classLists',
            'admins',
            'teachersPerPage', 'parentsPerPage', 'studentsPerPage',
            'studentSearch'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users',
            'class_list_id' => ['nullable', 'integer', Rule::exists('class_lists', 'id')->whereNull('archived_at')],
            'subjects'      => 'nullable|array',
            'subjects.*'    => ['string', Rule::in(['English', 'Filipino'])],
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

        // Classes are created on the Manage Classes page; here we only link the
        // teacher to an existing class via class_subjects (never class_lists).
        $this->syncTeacherClassSubjects(
            $teacherId,
            $validated['class_list_id'] ?? null,
            $validated['subjects'] ?? []
        );

        self::log('create', "created teacher account for {$user->name}");

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

        self::log('update', "resent activation email to teacher {$teacher->name}");

        return redirect()->route('admin.teachers.index')
            ->with('success', "Activation email resent to {$teacher->email}.");
    }

    public function update(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $teacher->id,
            'class_list_id' => ['nullable', 'integer', Rule::exists('class_lists', 'id')->whereNull('archived_at')],
            'subjects'      => 'nullable|array',
            'subjects.*'    => ['string', Rule::in(['English', 'Filipino'])],
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

            // Re-sync subject assignments: archive all current active ones for
            // this teacher, then (re)create for the newly selected class/subjects.
            // class_lists.teacher_id and class_lists.subject are left untouched.
            ClassSubject::where('teacher_id', $teacherRecord->id)
                ->whereNull('archived_at')
                ->update(['archived_at' => now()]);

            $this->syncTeacherClassSubjects(
                $teacherRecord->id,
                $validated['class_list_id'] ?? null,
                $validated['subjects'] ?? []
            );
        }

        self::log('update', "updated teacher account for {$validated['name']}");

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

        self::log('delete', "deleted teacher {$name}");

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher account for \"{$name}\" has been deleted.");
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Link a teacher to an existing class for the given subjects via
     * class_subjects. For each subject: restore-and-reassign an existing
     * (class_list_id, subject) row if present (active or archived), otherwise
     * create a new one. No class is selected => nothing is created. Never
     * writes to class_lists.subject or class_lists.teacher_id, and never
     * hard-deletes a row.
     *
     * @param  array<int,string>  $subjects
     */
    private function syncTeacherClassSubjects(int $teacherId, ?int $classListId, array $subjects): void
    {
        if (!$classListId) {
            return;
        }

        foreach ($subjects as $subject) {
            if (!in_array($subject, ['English', 'Filipino'], true)) {
                continue;
            }

            $existing = ClassSubject::where('class_list_id', $classListId)
                ->where('subject', $subject)
                ->first();

            if ($existing) {
                $existing->teacher_id  = $teacherId;
                $existing->archived_at = null;
                $existing->save();
            } else {
                ClassSubject::create([
                    'class_list_id' => $classListId,
                    'teacher_id'    => $teacherId,
                    'subject'       => $subject,
                    'created_at'    => now(),
                ]);
            }
        }
    }
}
