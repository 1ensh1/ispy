<?php

namespace App\Http\Controllers;

use App\Models\ClassList;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\VocabularyLibrary;
use App\Models\VocabularySuggestion;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherDashboardController extends Controller
{
    use LogsActivity;

    /**
     * Max active (non-archived) students allowed per class. Established in
     * Session 11 and shared between the enrollment roster and enrollmentStore
     * so the cap is defined in exactly one place.
     */
    private const MAX_STUDENTS_PER_CLASS = 20;

    public function index(Request $request)
    {
        $teacher   = Teacher::where('user_id', auth()->id())->first();
        $classList = ClassList::find($request->active_class_id);

        $studentCount    = 0;
        $pendingMessages = 0;
        $students        = collect();

        if ($classList) {
            $studentCount = Student::active()->where('class_list_id', $classList->id)->count();
            $students     = Student::active()->where('class_list_id', $classList->id)
                ->with('parentUser')
                ->get();
        }

        if ($teacher) {
            $pendingMessages = DB::table('messages')
                ->join('engagement_records', 'messages.engagement_id', '=', 'engagement_records.id')
                ->where('engagement_records.teacher_id', $teacher->id)
                ->where('messages.sender_role', 'Parent')
                ->where('messages.is_read', false)
                ->count();
        }

        return view('teacher.dashboard', compact(
            'teacher', 'classList', 'studentCount', 'pendingMessages', 'students'
        ));
    }

    public function students(Request $request)
    {
        $teacher   = Teacher::where('user_id', auth()->id())->first();
        $classList = ClassList::find($request->active_class_id);

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $query = $classList
            ? Student::active()->where('class_list_id', $classList->id)->with('parentUser')
            : Student::active()->whereNull('id');

        $students = $query->paginate($perPage)->appends(request()->query());

        return view('teacher.students', compact('teacher', 'classList', 'students', 'perPage'));
    }

    public function vocabulary(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        $search         = $request->query('search');
        $categoryFilter = $request->query('category');
        $audioFilter    = $request->query('audio_status');

        $query = VocabularyLibrary::where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('english_label', 'ilike', "%{$search}%")
                  ->orWhere('filipino_label', 'ilike', "%{$search}%");
            });
        }

        if ($categoryFilter && in_array($categoryFilter, ['CVC', 'Multi-Syllabic'])) {
            $query->where('category', $categoryFilter);
        }

        if ($audioFilter && in_array($audioFilter, ['Complete', 'Partial', 'Missing'])) {
            $query->where('audio_status', $audioFilter);
        }

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $words = $query->orderBy('english_label')->paginate($perPage)->appends(request()->query());

        $suggestions = $teacher
            ? VocabularySuggestion::where('teacher_id', $teacher->id)
                ->orderByDesc('submitted_at')
                ->get()
            : collect();

        return view('teacher.vocabulary', compact('teacher', 'words', 'suggestions', 'search', 'categoryFilter', 'audioFilter', 'perPage'));
    }

    public function suggest(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (!$teacher) {
            return back()->withErrors(['english_label' => 'Teacher profile not found. Please contact the administrator.']);
        }

        $validated = $request->validate([
            'english_label'  => 'required|string|max:255',
            'filipino_label' => 'required|string|max:255',
            'category'       => 'required|string|max:255',
        ]);

        if (VocabularyLibrary::where('english_label', $validated['english_label'])->exists()) {
            return back()
                ->withErrors(['english_label' => 'This word already exists in the vocabulary library.'])
                ->withInput();
        }

        if (VocabularySuggestion::where('teacher_id', $teacher->id)
            ->where('english_label', $validated['english_label'])
            ->where('status', 'Pending')
            ->exists()) {
            return back()
                ->withErrors(['english_label' => 'You have already submitted this word. Please wait for the admin\'s review.'])
                ->withInput();
        }

        VocabularySuggestion::create([
            'teacher_id'     => $teacher->id,
            'english_label'  => $validated['english_label'],
            'filipino_label' => $validated['filipino_label'],
            'category'       => $validated['category'],
            'status'         => 'Pending',
            'submitted_at'   => now(),
        ]);

        $adminUserIds = DB::table('administrators')->pluck('user_id');
        if ($adminUserIds->isEmpty()) {
            $adminUserIds = DB::table('users')->where('role', 'Admin')->pluck('id');
        }

        $now = now();
        try {
            foreach ($adminUserIds as $adminUserId) {
                DB::table('notifications')->insert([
                    'recipient_id'      => $adminUserId,
                    'recipient_role'    => 'Admin',
                    'notification_type' => 'Suggestion',
                    'action_url'        => route('admin.vocabulary-suggestions.index'),
                    'title'             => 'New Vocabulary Suggestion',
                    'message'           => "{$teacher->name} suggested a new word: \"{$validated['english_label']}\".",
                    'is_read'           => false,
                    'created_at'        => $now,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to insert vocabulary suggestion notification', [
                'error'       => $e->getMessage(),
                'teacher_id'  => $teacher->id,
                'word'        => $validated['english_label'],
            ]);
        }

        self::log('create', "submitted vocabulary suggestion '{$validated['english_label']}'");

        return back()->with('success', 'Word suggestion submitted successfully!');
    }

    public function enrollment(Request $request)
    {
        $teacher    = Teacher::where('user_id', auth()->id())->first();

        // Source the teacher's sections from class_subjects (not class_lists.teacher_id).
        // Enrollment adds a student to a class, so dedupe by class_list_id; keep the
        // subjects the teacher handles for that class as a display label.
        $classLists = $teacher
            ? \App\Models\ClassSubject::active()
                ->where('teacher_id', $teacher->id)
                ->with('classList')
                ->get()
                ->filter(fn ($cs) => $cs->classList && is_null($cs->classList->archived_at))
                ->groupBy('class_list_id')
                ->map(function ($rows) {
                    $class = $rows->first()->classList;
                    return (object) [
                        'id'         => $class->id,
                        'class_name' => $class->class_name,
                        'subjects'   => $rows->pluck('subject')->unique()->values()->all(),
                    ];
                })
                ->sortBy('class_name')
                ->values()
            : collect();

        // Teacher's own class ids — same class_subjects source of truth that
        // TeacherMiddleware's $ownClasses uses to confirm class ownership.
        $ownClassIds = $teacher
            ? \App\Models\ClassSubject::active()
                ->where('teacher_id', $teacher->id)
                ->pluck('class_list_id')
                ->unique()
            : collect();

        // Roster scope comes from the query string. Only honor the selection if
        // it is one of the teacher's own classes; otherwise default to the
        // teacher's first own class so the table populates on a normal page load
        // (and never leak another teacher's roster). Falls back to null only when
        // the teacher has no assigned class, which keeps the empty-roster branch.
        $selectedClassId = $request->query('class_list_id');
        if ($selectedClassId === null || ! $ownClassIds->contains((int) $selectedClassId)) {
            // No explicit page-local selection (or it failed validation). Prefer the
            // teacher's globally active class — the one TeacherMiddleware resolved into
            // session/$request->active_class_id and the navbar "Switch Class" sets — but
            // only when it's one of the teacher's own classes (it may be a substitute-only
            // class, which this enrollment roster doesn't cover). Otherwise fall back to
            // the teacher's first own class so the table still populates.
            $activeClassId = $request->active_class_id;
            if ($activeClassId !== null && $ownClassIds->contains((int) $activeClassId)) {
                $selectedClassId = (int) $activeClassId;
            } else {
                $selectedClassId = $ownClassIds->first();
            }
        }

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        if ($selectedClassId !== null) {
            $rosterQuery = Student::active()->where('class_list_id', $selectedClassId);

            // Total active students for the "X / 20 enrolled" indicator.
            $activeCount = (clone $rosterQuery)->count();

            // Eager-load only the parent's name and contact_number — never the
            // student's parent_password.
            $students = $rosterQuery
                ->with(['parentUser:id,name,contact_number', 'classList'])
                ->paginate($perPage)
                ->appends(['class_list_id' => $selectedClassId, 'per_page' => $perPage]);
        } else {
            $activeCount = 0;
            $students    = Student::active()->whereNull('id')->paginate($perPage);
        }

        $maxStudents = self::MAX_STUDENTS_PER_CLASS;

        return view('teacher.enrollment', compact(
            'teacher', 'classLists', 'selectedClassId', 'students', 'perPage', 'activeCount', 'maxStudents'
        ));
    }

    public function enrollmentStore(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'profile_icon' => 'nullable|string|in:cat,dog,bear,rabbit,fox,frog,penguin,lion',
        ]);

        $activeClassId = $request->active_class_id;
        if ($activeClassId) {
            $classCount = Student::active()->where('class_list_id', $activeClassId)->count();
            if ($classCount >= self::MAX_STUDENTS_PER_CLASS) {
                $cap = self::MAX_STUDENTS_PER_CLASS;
                return back()->withErrors(['class_list_id' => "This class already has {$cap} students. No additional students can be enrolled."])->withInput();
            }
        }

        Student::create([
            'name'            => $validated['name'],
            'class_list_id'   => $activeClassId,
            'profile_icon'    => $validated['profile_icon'] ?? 'cat',
            'parent_password' => \Illuminate\Support\Str::random(8),
        ]);

        self::log('create', "enrolled student {$validated['name']}");

        $adminUserIds = DB::table('administrators')->pluck('user_id');
        if ($adminUserIds->isEmpty()) {
            $adminUserIds = DB::table('users')->where('role', 'Admin')->pluck('id');
        }

        $now = now();
        foreach ($adminUserIds as $adminUserId) {
            DB::table('notifications')->insert([
                'recipient_id'      => $adminUserId,
                'recipient_role'    => 'Admin',
                'notification_type' => 'Milestone',
                'action_url'        => route('admin.students'),
                'title'             => 'New Student Added',
                'message'           => "{$teacher->name} added a new student: \"{$validated['name']}\". Please link a parent account to complete enrollment.",
                'is_read'           => false,
                'created_at'        => $now,
            ]);
        }

        return back()->with('success', 'Student added to the roster.');
    }

    /**
     * Bulk-enroll EXISTING students into a selected class from an uploaded CSV.
     *
     * The CSV is a single column (header: student_name). Each row names an
     * existing, non-archived student to look up and move into the target class.
     * Nothing is created here — no users, parents, or students. Returns a JSON
     * summary the frontend renders.
     */
    public function enrollViaCsv(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'file'          => 'required|file|mimes:csv,txt|max:2048',
            'class_list_id' => 'required|integer',
        ]);

        $classListId = (int) $request->input('class_list_id');

        // Ownership check — same class_subjects source of truth used by enrollment().
        $ownClassIds = ClassSubject::active()
            ->where('teacher_id', $teacher->id)
            ->pluck('class_list_id')
            ->unique();

        if (! $ownClassIds->contains($classListId)) {
            return response()->json([
                'message' => 'You are not assigned to this class.',
            ], 403);
        }

        $classList = ClassList::find($classListId);
        $className = $classList?->class_name ?? 'the class';

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if ($handle === false) {
            return response()->json(['message' => 'Could not read the uploaded file.'], 422);
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);
            return response()->json(['message' => 'The CSV file is empty.'], 422);
        }

        // Header must contain 'student_name' (trim + lowercase; strip a leading
        // UTF-8 BOM that spreadsheet exports often prepend to the first cell).
        $normalizedHeader = array_map(fn ($h) => strtolower(trim((string) ($h ?? ''))), $headerRow);
        if (isset($normalizedHeader[0])) {
            $normalizedHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $normalizedHeader[0]);
        }

        if (! in_array('student_name', $normalizedHeader, true)) {
            fclose($handle);
            // Return 200 with the standard result shape so the bad-header message
            // flows through the same Failed Rows table the frontend already renders,
            // rather than tripping the generic fetch() catch on a non-2xx status.
            return response()->json([
                'enrolled'         => [],
                'errors'           => [[
                    'row'    => 1,
                    'name'   => '',
                    'reason' => 'Invalid CSV format. Expected header: student_name',
                ]],
                'already_enrolled' => [],
                'class_full'       => false,
            ]);
        }

        $enrolled        = [];
        $errors          = [];
        $alreadyEnrolled = [];
        $classFull       = false;

        // Running tally of active students in the target class. Seeded from the DB
        // and incremented on each enrollment so the 20-cap is enforced mid-batch.
        $activeCount = Student::active()->where('class_list_id', $classListId)->count();

        $rowNumber = 1; // header consumed above
        while (($rawRow = fgetcsv($handle)) !== false) {
            $rowNumber++;

            $name = trim((string) ($rawRow[0] ?? ''));

            // Skip blank physical lines without recording an error.
            if ($name === '' && count($rawRow) <= 1) {
                continue;
            }

            // Cap already reached: this row and every remaining row are rejected.
            if ($classFull || $activeCount >= self::MAX_STUDENTS_PER_CLASS) {
                $classFull = true;
                $errors[]  = [
                    'row'    => $rowNumber,
                    'name'   => $name,
                    'reason' => 'Class is full (' . self::MAX_STUDENTS_PER_CLASS . ' student limit reached).',
                ];
                continue;
            }

            if ($name === '') {
                $errors[] = [
                    'row'    => $rowNumber,
                    'name'   => $name,
                    'reason' => 'Student name is required.',
                ];
                continue;
            }

            $matches = Student::active()->where('name', $name)->get();

            if ($matches->isEmpty()) {
                $errors[] = [
                    'row'    => $rowNumber,
                    'name'   => $name,
                    'reason' => 'No student found with this name.',
                ];
                continue;
            }

            if ($matches->count() > 1) {
                $errors[] = [
                    'row'    => $rowNumber,
                    'name'   => $name,
                    'reason' => 'Multiple students found with this name — please enroll manually.',
                ];
                continue;
            }

            $student = $matches->first();

            // Already in the target class — not an error, just noted separately.
            if ((int) $student->class_list_id === $classListId) {
                $alreadyEnrolled[] = $name;
                continue;
            }

            // Assigned to a different class — block the silent move. Reassigning a
            // student between classes must be a deliberate manual action, never a
            // side effect of a CSV upload.
            if (! is_null($student->class_list_id) && (int) $student->class_list_id !== $classListId) {
                $errors[] = [
                    'row'    => $rowNumber,
                    'name'   => $name,
                    'reason' => 'Student is already assigned to another class. Please unenroll them first.',
                ];
                continue;
            }

            try {
                $student->class_list_id = $classListId;
                $student->save();

                $enrolled[] = $name;
                $activeCount++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'row'    => $rowNumber,
                    'name'   => $name,
                    'reason' => 'Could not enroll this student. Please try again.',
                ];
            }
        }

        fclose($handle);

        // Single batch-level activity entry (the trait prepends "Teacher [Name]").
        self::log(
            'csv_enrollment',
            'bulk-enrolled ' . count($enrolled) . ' students via CSV into ' . $className . '. '
                . count($errors) . ' rows failed. ' . count($alreadyEnrolled) . ' already enrolled.'
        );

        return response()->json([
            'enrolled'         => $enrolled,
            'errors'           => $errors,
            'already_enrolled' => $alreadyEnrolled,
            'class_full'       => $classFull,
        ]);
    }

    /**
     * Stream the bulk-enrollment CSV template — a single header column.
     */
    public function csvTemplate()
    {
        return response()->streamDownload(function () {
            echo "student_name\n";
        }, 'enrollment_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function pin(Request $request)
    {
        $teacher   = Teacher::where('user_id', auth()->id())->first();
        $classList = ClassList::find($request->active_class_id);

        return view('teacher.pin', compact('teacher', 'classList'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
