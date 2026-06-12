<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        if (strtolower(auth()->user()->role ?? '') !== 'parent') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Unauthorized access.']);
        }
        return $next($request);
    }
}
