<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassList;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    use LogsActivity;

    /**
     * List active classes (plus archived when ?show_archived=1) with their
     * active subjects/teachers and active student counts.
     */
    public function index(Request $request)
    {
        $showArchived = $request->query('show_archived') == '1';

        $with = [
            'classSubjects' => function ($q) {
                $q->whereNull('archived_at')->with('teacher:id,name');
            },
        ];

        $countActiveStudents = ['students as active_students_count' => function ($q) {
            $q->whereNull('archived_at');
        }];

        $active = ClassList::whereNull('archived_at')
            ->with($with)
            ->withCount($countActiveStudents)
            ->orderBy('class_name')
            ->get();

        $totalActive = $active->count();

        if ($showArchived) {
            $archived = ClassList::whereNotNull('archived_at')
                ->with($with)
                ->withCount($countActiveStudents)
                ->orderBy('class_name')
                ->get();
            $classes = $active->concat($archived);
        } else {
            $classes = $active;
        }

        return view('admin.classes.index', compact('classes', 'showArchived', 'totalActive'));
    }

    /**
     * Create a class. teacher_id and subject stay null — subjects live in
     * class_subjects, and class_lists.subject is left untouched for the app.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:50'],
        ]);

        if ($this->nameExists($validated['class_name'])) {
            return back()
                ->withInput()
                ->withErrors(['class_name' => 'A class with this name already exists.']);
        }

        do {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (ClassList::where('unified_classroom_pin', $pin)->exists());

        $class = ClassList::create([
            'class_name'            => $validated['class_name'],
            'teacher_id'            => null,
            'subject'               => null,
            'unified_classroom_pin' => $pin,
        ]);

        self::log('Create Class', "created class {$class->class_name}");

        return redirect()->route('admin.classes.index')
            ->with('success', "Class \"{$class->class_name}\" created. PIN: {$pin}.");
    }

    /**
     * Edit page for a single class (modal in the index is the primary path).
     */
    public function edit(int $id)
    {
        $class = ClassList::with(['classSubjects' => function ($q) {
            $q->whereNull('archived_at')->with('teacher:id,name');
        }])->findOrFail($id);

        return view('admin.classes.edit', compact('class'));
    }

    /**
     * Update the class name only. PIN, teacher_id, subject and archived_at
     * are intentionally left as-is.
     */
    public function update(Request $request, int $id)
    {
        $class = ClassList::findOrFail($id);

        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:50'],
        ]);

        if ($this->nameExists($validated['class_name'], $id)) {
            return back()
                ->withInput()
                ->withErrors(['class_name' => 'A class with this name already exists.']);
        }

        $class->class_name = $validated['class_name'];
        $class->save();

        self::log('Update Class', "updated class {$class->class_name}");

        return redirect()->route('admin.classes.index')
            ->with('success', "Class \"{$class->class_name}\" updated.");
    }

    /**
     * Archive a class and all of its active subject assignments.
     */
    public function archive(int $id)
    {
        $class = ClassList::findOrFail($id);

        $class->classSubjects()->whereNull('archived_at')->update(['archived_at' => now()]);

        $class->archived_at = now();
        $class->save();

        self::log('Archive Class', "archived class {$class->class_name}");

        return redirect()->route('admin.classes.index')
            ->with('success', "Class \"{$class->class_name}\" archived.");
    }

    /**
     * Restore a class. Subject assignments stay archived by design.
     */
    public function restore(int $id)
    {
        $class = ClassList::findOrFail($id);

        $class->archived_at = null;
        $class->save();

        self::log('Restore Class', "restored class {$class->class_name}");

        return redirect()->route('admin.classes.index')
            ->with('success', "Class \"{$class->class_name}\" restored.");
    }

    /**
     * Case-insensitive duplicate check across ALL rows (including archived),
     * matching the LOWER(class_name) unique index. Optionally excludes one id.
     */
    private function nameExists(string $className, ?int $exceptId = null): bool
    {
        $query = ClassList::whereRaw('LOWER(class_name) = ?', [mb_strtolower($className)]);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}
