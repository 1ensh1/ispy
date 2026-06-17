<?php

namespace App\Http\Controllers;

use App\Mail\TeacherAccountCreated;
use App\Models\ClassList;
use App\Models\ClassSubject;
use App\Models\ParentUser;
use App\Models\Student;
use App\Models\User;
use App\Services\ClassSubjectService;
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
        // Filter by class via class_subjects (source of truth for assignments).
        $teacherClassId = $request->query('teacher_class_id');
        if ($teacherClassId) {
            $teacherUserIds = DB::table('class_subjects')
                ->join('teachers', 'class_subjects.teacher_id', '=', 'teachers.id')
                ->where('class_subjects.class_list_id', $teacherClassId)
                ->whereNull('class_subjects.archived_at')
                ->pluck('teachers.user_id');
            $teacherQuery->whereIn('id', $teacherUserIds);
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

        // Active student counts come from class_subjects: count students per
        // class_list_id, then sum per teacher over their active assignments.
        $assignedClassListIds = $activeSubjectsByTeacher
            ->flatten(1)
            ->pluck('class_list_id')
            ->unique()
            ->values();

        $studentCountsByClass = $assignedClassListIds->isNotEmpty()
            ? DB::table('students')
                ->whereIn('class_list_id', $assignedClassListIds)
                ->whereNull('archived_at')
                ->select('class_list_id', DB::raw('COUNT(id) as total'))
                ->groupBy('class_list_id')
                ->pluck('total', 'class_list_id')
            : collect();

        $studentCountsByUser = [];
        $classListsByUser    = [];
        foreach ($teacherRowsByUserId as $userId => $rows) {
            $firstRow = $rows->first();
            $teacherId = $firstRow->id;
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

            // Student count = active students across this teacher's assigned
            // classes (deduped by class_list_id via class_assignments).
            $studentCountsByUser[$userId] = collect($classAssignments)
                ->sum(fn ($a) => (int) ($studentCountsByClass[$a['class_list_id']] ?? 0));

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
        // Filter by class: parents with at least one active (non-archived) student
        // in the chosen class.
        $parentClassId = $request->query('parent_class_id');
        if ($parentClassId) {
            $parentUserIds = DB::table('students')
                ->join('parents', 'students.parent_id', '=', 'parents.id')
                ->where('students.class_list_id', $parentClassId)
                ->whereNull('students.archived_at')
                ->pluck('parents.user_id');
            $parentQuery->whereIn('id', $parentUserIds);
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
                    'status'          => $pr ? ($pr->status ?? 'Active') : 'Active',
                ];
            }
        }

        // ===== STUDENTS =====
        $studentsPerPage = $this->perPage($request, 'students_per_page');
        $studentSearch = $request->query('student_search');
        $studentQuery = Student::active()->with(['parentUser', 'classList.teacher'])->orderBy('id');
        if ($studentSearch) {
            $studentQuery->where('name', 'ILIKE', '%' . $studentSearch . '%');
        }
        // Filter by class.
        $studentClassId = $request->query('class_id');
        if ($studentClassId) {
            $studentQuery->where('class_list_id', $studentClassId);
        }
        $students    = $studentQuery->paginate($studentsPerPage, ['*'], 'student_page')
            ->appends(request()->query());

        // Active teachers per class come from class_subjects (post-14C), not the
        // stale class_lists.teacher_id. Map class_list_id => [teacher names].
        $teachersByClass = ClassSubject::whereNull('archived_at')
            ->with('teacher:id,name')
            ->get()
            ->groupBy('class_list_id')
            ->map(fn ($rows) => $rows
                ->pluck('teacher.name')
                ->filter()
                ->unique()
                ->values()
                ->all());

        $parentsList      = ParentUser::orderBy('name')->get();
        $classLists       = ClassList::active()->with('teacher')->orderBy('class_name')->get();

        // Subjects available to assign per class = the subjects with NO active
        // class_subjects row at all (same rule as the Teacher View "Assign
        // Existing Class" dropdown). Informational only for the Edit Teacher modal.
        $allSubjects      = ['English', 'Filipino'];
        $claimedByClass   = ClassSubject::whereNull('archived_at')
            ->get()
            ->groupBy('class_list_id')
            ->map(fn ($rows) => $rows->pluck('subject')->unique()->all());
        foreach ($classLists as $cl) {
            $cl->available_subjects = array_values(
                array_diff($allSubjects, $claimedByClass->get($cl->id, []))
            );
        }
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
            'students', 'archivedStudents', 'parentsList', 'classLists', 'teachersByClass',
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
        // Shared service blocks subjects already held by another teacher.
        if (! empty($validated['class_list_id'])) {
            app(ClassSubjectService::class)->assign(
                $validated['class_list_id'],
                $teacherId,
                $validated['subjects'] ?? []
            );
        }

        self::log('create', "created teacher account for {$user->name}");

        // Generate a one-time activation token (with TTL) and build the activation link.
        $tokenData = app(\App\Services\ActivationService::class)->generateToken();
        DB::table('teacher_activation_tokens')->insert([
            'teacher_id' => $teacherId,
            'token'      => $tokenData['token'],
            'created_at' => now(),
            'expires_at' => $tokenData['expires_at'],
        ]);
        $activationUrl = route('teacher.activate', ['token' => $tokenData['token']]);

        try {
            Mail::to($user->email)->send(new TeacherAccountCreated($user->name, $user->email, $activationUrl));
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

        $tokenData = app(\App\Services\ActivationService::class)->generateToken();
        DB::table('teacher_activation_tokens')->insert([
            'teacher_id' => $record->id,
            'token'      => $tokenData['token'],
            'created_at' => now(),
            'expires_at' => $tokenData['expires_at'],
        ]);
        $activationUrl = route('teacher.activate', ['token' => $tokenData['token']]);

        try {
            Mail::to($teacher->email)->send(new TeacherAccountCreated($teacher->name, $teacher->email, $activationUrl));
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

        $created       = [];
        $skipped       = [];
        $blocked       = [];
        $blockedByKids = [];
        $className     = null;

        if ($teacherRecord) {
            DB::table('teachers')
                ->where('id', $teacherRecord->id)
                ->update(['name' => $validated['name'], 'updated_at' => now()]);

            $classListId = $validated['class_list_id'] ?? null;
            $subjects    = $validated['subjects'] ?? [];

            // The modal edits a single class. Re-sync that class only — leave the
            // teacher's assignments on other classes untouched. class_lists.teacher_id
            // and class_lists.subject are never written here.
            if ($classListId) {
                $service   = app(ClassSubjectService::class);
                $className = optional(ClassList::find($classListId))->class_name;

                // Hard-delete subjects the admin deselected (guarded: a class with
                // active students can't have a subject left without a teacher).
                $current = ClassSubject::where('class_list_id', $classListId)
                    ->where('teacher_id', $teacherRecord->id)
                    ->whereNull('archived_at')
                    ->pluck('subject')
                    ->all();

                foreach (array_diff($current, $subjects) as $subject) {
                    // unassign() returns false when blocked by active students —
                    // surface that instead of silently claiming success.
                    if (! $service->unassign($classListId, $teacherRecord->id, $subject)) {
                        $blockedByKids[] = $subject;
                    }
                }

                // Assign selected subjects (subjects held by another teacher are blocked).
                ['created' => $created, 'skipped' => $skipped, 'blocked' => $blocked]
                    = $service->assign($classListId, $teacherRecord->id, $subjects);
            }
        }

        self::log('update', "updated teacher account for {$validated['name']}");

        // Build a detailed message that names what WAS assigned (matching the
        // Teacher View "Assign Existing Class" message completeness), plus what
        // was skipped or blocked.
        if ($className && ! empty($created)) {
            $message = "Class \"{$className}\" (" . implode(', ', $created) . ") assigned to {$teacher->name}.";
        } else {
            $message = "Teacher \"{$teacher->name}\" updated successfully.";
        }

        if (! empty($skipped)) {
            $message .= ' Skipped already-assigned: ' . implode(', ', $skipped) . '.';
        }
        if (! empty($blocked)) {
            $parts = [];
            foreach ($blocked as $subject => $holder) {
                $parts[] = "{$subject} (held by {$holder})";
            }
            $message .= ' Skipped — already assigned to another teacher: ' . implode(', ', $parts) . '.';
        }
        if (! empty($blockedByKids)) {
            $message .= ' Could not unassign (active students still enrolled): ' . implode(', ', $blockedByKids) . '.';
        }

        return redirect()->route('admin.teachers.index')->with('success', $message);
    }

    public function destroy(User $teacher)
    {
        $record = DB::table('teachers')->where('user_id', $teacher->id)->first();

        if ($record) {
            // Count active students across every class this teacher holds via
            // class_subjects (the source of truth; class_lists.teacher_id is
            // deprecated and null-by-design).
            $studentCount = DB::table('students')
                ->whereIn('class_list_id', function ($q) use ($record) {
                    $q->select('class_list_id')
                        ->from('class_subjects')
                        ->where('teacher_id', $record->id)
                        ->whereNull('archived_at');
                })
                ->whereNull('archived_at')
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
}
