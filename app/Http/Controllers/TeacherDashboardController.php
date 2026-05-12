<?php

namespace App\Http\Controllers;

use App\Models\ClassList;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\VocabularyLibrary;
use App\Models\VocabularySuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherDashboardController extends Controller
{
    private function getTeacherAndClass(): array
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $classList = $teacher
            ? ClassList::where('teacher_id', $teacher->id)->first()
            : null;

        return [$teacher, $classList];
    }

    public function index()
    {
        [$teacher, $classList] = $this->getTeacherAndClass();

        $studentCount    = 0;
        $pendingMessages = 0;
        $students        = collect();

        if ($classList) {
            $studentCount = Student::where('class_list_id', $classList->id)->count();
            $students     = Student::where('class_list_id', $classList->id)
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

    public function students()
    {
        [$teacher, $classList] = $this->getTeacherAndClass();

        $students = $classList
            ? Student::where('class_list_id', $classList->id)->with('parentUser')->get()
            : collect();

        return view('teacher.students', compact('teacher', 'classList', 'students'));
    }

    public function vocabulary()
    {
        [$teacher] = $this->getTeacherAndClass();

        $words = VocabularyLibrary::where('is_active', true)->paginate(15);

        $suggestions = $teacher
            ? VocabularySuggestion::where('teacher_id', $teacher->id)
                ->orderByDesc('submitted_at')
                ->get()
            : collect();

        return view('teacher.vocabulary', compact('teacher', 'words', 'suggestions'));
    }

    public function suggest(Request $request)
    {
        [$teacher] = $this->getTeacherAndClass();

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

        return back()->with('success', 'Word suggestion submitted successfully!');
    }

    public function enrollment()
    {
        [$teacher, $classList] = $this->getTeacherAndClass();

        $students = $classList
            ? Student::where('class_list_id', $classList->id)->with('parentUser')->get()
            : collect();

        return view('teacher.enrollment', compact('teacher', 'classList', 'students'));
    }

    public function pin()
    {
        [$teacher, $classList] = $this->getTeacherAndClass();

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
