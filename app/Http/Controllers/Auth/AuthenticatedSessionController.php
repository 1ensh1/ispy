<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use LogsActivity;
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Block teachers whose account has not been activated yet.
        $user = auth()->user();
        if (strtolower($user->role ?? '') === 'teacher') {
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            if ($teacher && $teacher->status === 'Inactive') {
                Auth::guard('web')->logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is not yet activated. Please check your email for the activation link.',
                ]);
            }
        }

        $request->session()->regenerate();

        $role = strtolower(auth()->user()->role ?? '');
        self::log('login', 'logged in');

        return match($role) {
            'admin'   => redirect('/admin/dashboard'),
            'teacher' => redirect('/teacher/dashboard'),
            'parent'  => redirect('/parent/dashboard'),
            default   => redirect()->intended(route('dashboard', absolute: false)),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
