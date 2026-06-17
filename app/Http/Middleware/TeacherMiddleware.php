<?php

namespace App\Http\Middleware;

use App\Models\ClassSubject;
use App\Models\ClassSubstitute;
use App\Models\Teacher;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (strtolower(auth()->user()->role ?? '') !== 'teacher') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Unauthorized access.']);
        }

        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (!$teacher) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Teacher record not found.']);
        }

        // Own classes — source of truth is class_subjects, not the deprecated,
        // null-by-design class_lists.teacher_id column. A teacher may hold
        // multiple subjects on one class, so dedupe to one entry per class.
        $ownClasses = ClassSubject::where('teacher_id', $teacher->id)
            ->whereNull('archived_at')
            ->with('classList')
            ->get()
            ->filter(fn($cs) => $cs->classList !== null)
            ->map(fn($cs) => [
                'id'         => $cs->classList->id,
                'class_name' => $cs->classList->class_name,
                'pin'        => $cs->classList->unified_classroom_pin,
                'is_sub'     => false,
                'label'      => $cs->classList->class_name,
            ])
            ->unique('id')
            ->values();

        // Substitute classes (active scope)
        $subClasses = ClassSubstitute::active()
            ->where('substitute_teacher_id', $teacher->id)
            ->with('classList')
            ->get()
            ->map(fn($sub) => [
                'id'         => $sub->classList->id,
                'class_name' => $sub->classList->class_name,
                'pin'        => $sub->classList->unified_classroom_pin,
                'is_sub'     => true,
                'label'      => $sub->classList->class_name . ' — Substitute',
            ]);

        $allClasses = $ownClasses->concat($subClasses)->values();

        // Resolve active class
        $sessionId    = session('active_class_id');
        $allIds       = $allClasses->pluck('id');
        $activeEntry  = $allIds->contains($sessionId)
            ? $allClasses->firstWhere('id', $sessionId)
            : ($ownClasses->first() ?? $subClasses->first());

        if ($activeEntry) {
            session(['active_class_id' => $activeEntry['id']]);
        }

        view()->share('teacherAllClasses',  $allClasses);
        view()->share('teacherActiveClass', $activeEntry);
        view()->share('currentTeacher',     $teacher);

        $request->merge([
            'active_class_id' => $activeEntry['id'] ?? null,
            'active_class'    => $activeEntry,
        ]);

        return $next($request);
    }
}
