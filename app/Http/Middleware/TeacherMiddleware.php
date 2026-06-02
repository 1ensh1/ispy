<?php

namespace App\Http\Middleware;

use App\Models\ClassList;
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

        // Own classes
        $ownClasses = ClassList::where('teacher_id', $teacher->id)
            ->get()
            ->map(fn($cl) => [
                'id'         => $cl->id,
                'class_name' => $cl->class_name,
                'pin'        => $cl->unified_classroom_pin,
                'is_sub'     => false,
                'label'      => $cl->class_name,
            ]);

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
