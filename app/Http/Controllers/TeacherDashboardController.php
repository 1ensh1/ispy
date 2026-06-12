<?php

namespace App\Http\Controllers;

use App\Models\ClassList;
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

        $classList  = ClassList::find($request->active_class_id);

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $query = $classList
            ? Student::active()->where('class_list_id', $classList->id)->with('parentUser', 'classList')
            : Student::active()->whereNull('id');

        $students = $query->paginate($perPage)->appends(request()->query());

        return view('teacher.enrollment', compact('teacher', 'classLists', 'classList', 'students', 'perPage'));
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
            if ($classCount >= 20) {
                return back()->withErrors(['class_list_id' => 'This class already has 20 students. No additional students can be enrolled.'])->withInput();
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
