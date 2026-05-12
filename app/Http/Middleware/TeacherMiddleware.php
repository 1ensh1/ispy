<?php

namespace App\Http\Middleware;

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

        return $next($request);
    }
}
