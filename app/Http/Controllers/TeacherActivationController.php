<?php

namespace App\Http\Controllers;

use App\Services\ActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherActivationController extends Controller
{
    /**
     * GET — read-only. Show the consent gate (or invalid/expired views).
     */
    public function activate(string $token)
    {
        $service = app(ActivationService::class);
        $check = $service->checkToken('teacher_activation_tokens', $token);

        if ($check['status'] === 'invalid') {
            return view('auth.activation_invalid');
        }

        if ($check['status'] === 'expired') {
            return view('auth.activation_expired');
        }

        $teacher = DB::table('teachers')->where('id', $check['row']->teacher_id)->first();

        return view('auth.activation_consent', [
            'name'         => $teacher->name ?? 'Teacher',
            'roleLabel'    => 'Teacher',
            'token'        => $token,
            'postUrl'      => route('teacher.activate.consent', $token),
            'termsVersion' => ActivationService::TERMS_VERSION,
        ]);
    }

    /**
     * POST — performs activation once the user agrees to the consent gate.
     */
    public function consent(Request $request, string $token)
    {
        if (!$request->boolean('agree')) {
            return redirect()->route('teacher.activate', $token);
        }

        $service = app(ActivationService::class);

        // Re-check now to grab the teacher name before processConsent deletes the token row.
        $check = $service->checkToken('teacher_activation_tokens', $token);

        if ($check['status'] === 'invalid') {
            return view('auth.activation_invalid');
        }

        if ($check['status'] === 'expired') {
            return view('auth.activation_expired');
        }

        $teacher = DB::table('teachers')->where('id', $check['row']->teacher_id)->first();
        $name = $teacher->name ?? 'Teacher';

        $result = $service->processConsent('teacher_activation_tokens', $token, 'teachers', 'teacher_id', 'Teacher');

        if ($result['success'] === false) {
            return $result['reason'] === 'expired'
                ? view('auth.activation_expired')
                : view('auth.activation_invalid');
        }

        return view('auth.activation_success', [
            'name'      => $name,
            'roleLabel' => 'Teacher',
            'email'     => $result['email'],
            'password'  => $result['password'],
        ]);
    }
}
