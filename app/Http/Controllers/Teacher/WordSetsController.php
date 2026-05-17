<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherWordSetPreference;
use App\Models\VocabularyLibrary;
use Illuminate\Http\Request;

class WordSetsController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $prefs = TeacherWordSetPreference::where('teacher_id', $teacher->id)
            ->pluck('is_active', 'category');

        $grouped = VocabularyLibrary::where('is_active', true)
            ->orderBy('category')
            ->orderBy('filipino_label')
            ->get()
            ->groupBy('category');

        $wordSets = $grouped->map(function ($words, $category) use ($prefs) {
            $allCvc = $words->every(fn($w) => $w->complexity_level === 1);
            return (object) [
                'category'  => $category,
                'badge'     => $allCvc ? 'CVC' : 'Multi-syllabic',
                'is_active' => (bool) ($prefs[$category] ?? true),
                'words'     => $words->take(5)->pluck('filipino_label'),
                'extra'     => max(0, $words->count() - 5),
            ];
        })->values();

        return view('teacher.word-sets', compact('wordSets'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'category'  => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        TeacherWordSetPreference::updateOrCreate(
            ['teacher_id' => $teacher->id, 'category' => $request->category],
            ['is_active'  => $request->boolean('is_active')]
        );

        return back();
    }
}
