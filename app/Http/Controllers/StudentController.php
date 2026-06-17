<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\ParentUser;
use App\Models\ClassList;
use App\Services\SupabaseStorageService;

class StudentController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $showArchived = $request->boolean('show_archived');

        $students = $showArchived
            ? Student::archived()->with(['parentUser', 'classList.teacher'])->paginate(15)
            : Student::active()->with(['parentUser', 'classList.teacher'])->paginate(15);

        $parents    = ParentUser::orderBy('name')->get();
        $classLists = ClassList::with('teacher')->orderBy('class_name')->get();

        return view('admin.students.index', compact('students', 'parents', 'classLists', 'showArchived'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'profile_icon'    => 'required|string|in:cat,dog,bear,rabbit,fox,frog,penguin,lion',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'parent_id'       => 'nullable|exists:parents,id',
            'class_list_id'   => 'nullable|exists:class_lists,id',
            'parent_password' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['parent_id'])) {
            $activeCount = Student::active()->where('parent_id', $validated['parent_id'])->count();
            if ($activeCount >= 10) {
                return back()->withErrors(['parent_id' => 'This parent already has 10 active students.'])->withInput();
            }
        }

        if (!empty($validated['class_list_id'])) {
            $classCount = Student::active()->where('class_list_id', $validated['class_list_id'])->count();
            if ($classCount >= 20) {
                return back()->withErrors(['class_list_id' => 'This class already has 20 students. No additional students can be enrolled.'])->withInput();
            }
        }

        $parentPassword = $request->input('parent_password') ?: Str::random(8);

        $student = Student::create([
            'name'            => $validated['name'],
            'profile_icon'    => $validated['profile_icon'],
            'parent_id'       => $validated['parent_id'] ?? null,
            'class_list_id'   => $validated['class_list_id'] ?? null,
            'parent_password' => $parentPassword,
        ]);

        self::log('create', "created student {$validated['name']}");

        if ($request->hasFile('profile_picture')) {
            $url = $this->uploadProfilePicture($request->file('profile_picture'), $student->id);
            if ($url) {
                $student->update(['profile_picture' => $url]);
                self::log('update', "uploaded profile picture for student {$student->name}");
            }
        }

        return redirect()->route('admin.teachers.index', ['tab' => 'students'])
            ->with('new_student_name', $validated['name'])
            ->with('new_student_password', $parentPassword);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'parent_id'       => 'nullable|exists:parents,id',
            'class_list_id'   => 'nullable|exists:class_lists,id',
            'profile_icon'    => 'required|string|in:cat,dog,bear,rabbit,fox,frog,penguin,lion',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'parent_password' => 'nullable|string|max:255',
        ]);

        $newParentId = $validated['parent_id'] ?: null;

        if ($newParentId && $newParentId != $student->parent_id) {
            $activeCount = Student::active()
                ->where('parent_id', $newParentId)
                ->where('id', '!=', $student->id)
                ->count();
            if ($activeCount >= 10) {
                return back()->withErrors(['parent_id' => 'This parent already has 10 active students.'])->withInput();
            }
        }

        $newClassId = $validated['class_list_id'] ?: null;
        if ($newClassId && $newClassId != $student->class_list_id) {
            $classCount = Student::active()->where('class_list_id', $newClassId)->count();
            if ($classCount >= 20) {
                return back()->withErrors(['class_list_id' => 'This class already has 20 students. No additional students can be enrolled.'])->withInput();
            }
        }

        $updateData = [
            'parent_id'     => $newParentId,
            'class_list_id' => $validated['class_list_id'] ?: null,
            'profile_icon'  => $validated['profile_icon'],
        ];

        if (!empty($validated['parent_password'])) {
            $updateData['parent_password'] = $validated['parent_password'];
        }

        // Profile picture: new upload takes precedence, then explicit removal,
        // otherwise the existing picture is left untouched.
        if ($request->hasFile('profile_picture')) {
            $url = $this->uploadProfilePicture($request->file('profile_picture'), $student->id);
            if ($url) {
                $updateData['profile_picture'] = $url;
                self::log('update', "uploaded profile picture for student {$student->name}");
            }
        } elseif ($request->boolean('remove_profile_picture')) {
            $updateData['profile_picture'] = null;
            self::log('update', "removed profile picture for student {$student->name}");
        }

        $student->update($updateData);

        return redirect()->route('admin.teachers.index', ['tab' => 'students'])
            ->with('success', "Assignments for \"{$student->name}\" saved successfully.");
    }

    /**
     * Upload a student profile picture to Supabase Storage and return its
     * public URL, or null on failure. Filename includes the student id and a
     * timestamp to avoid collisions and stale-cache issues.
     */
    private function uploadProfilePicture(\Illuminate\Http\UploadedFile $file, int $studentId): ?string
    {
        $ext         = strtolower($file->getClientOriginalExtension());
        $mimeMap     = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $contentType = $mimeMap[$ext] ?? 'image/jpeg';
        $filename    = 'students/student_' . $studentId . '_' . time() . '.' . $ext;
        $binary      = file_get_contents($file->getRealPath());

        return (new SupabaseStorageService)->uploadImage($binary, $filename, 'cms-images', $contentType);
    }

    public function archive(Student $student)
    {
        $student->update(['archived_at' => now()]);

        self::log('archive', "archived student {$student->name}");

        return redirect()->route('admin.teachers.index', ['tab' => 'students'])
            ->with('success', "Student \"{$student->name}\" has been archived.");
    }

    public function restore(Student $student)
    {
        if ($student->parent_id) {
            $activeCount = Student::active()->where('parent_id', $student->parent_id)->count();
            if ($activeCount >= 10) {
                return back()->withErrors(['restore' => 'Cannot restore: this parent already has 10 active students.']);
            }
        }

        $student->update(['archived_at' => null]);

        self::log('restore', "restored student {$student->name}");

        return redirect()->route('admin.teachers.index', ['tab' => 'students'])
            ->with('success', "Student \"{$student->name}\" has been restored.");
    }
}
