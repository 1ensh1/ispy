<?php

namespace App\Http\Controllers;

use App\Services\ActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentActivationController extends Controller
{
    /**
     * GET — read-only. Show the consent gate (or invalid/expired views).
     */
    public function activate(string $token)
    {
        $service = app(ActivationService::class);
        $check = $service->checkToken('parent_activation_tokens', $token);

        if ($check['status'] === 'invalid') {
            return view('auth.activation_invalid');
        }

        if ($check['status'] === 'expired') {
            return view('auth.activation_expired');
        }

        $parent = DB::table('parents')->where('id', $check['row']->parent_id)->first();

        return view('auth.activation_consent', [
            'name'         => $parent->name ?? 'Parent',
            'roleLabel'    => 'Parent',
            'token'        => $token,
            'postUrl'      => route('parent.activate.consent', $token),
            'termsVersion' => ActivationService::TERMS_VERSION,
        ]);
    }

    /**
     * POST — performs activation once the user agrees to the consent gate.
     */
    public function consent(Request $request, string $token)
    {
        if (!$request->boolean('agree')) {
            return redirect()->route('parent.activate', $token);
        }

        $service = app(ActivationService::class);

        // Re-check now to grab the parent name before processConsent deletes the token row.
        $check = $service->checkToken('parent_activation_tokens', $token);

        if ($check['status'] === 'invalid') {
            return view('auth.activation_invalid');
        }

        if ($check['status'] === 'expired') {
            return view('auth.activation_expired');
        }

        $parent = DB::table('parents')->where('id', $check['row']->parent_id)->first();
        $name = $parent->name ?? 'Parent';

        $result = $service->processConsent('parent_activation_tokens', $token, 'parents', 'parent_id', 'Parent');

        if ($result['success'] === false) {
            return $result['reason'] === 'expired'
                ? view('auth.activation_expired')
                : view('auth.activation_invalid');
        }

        return view('auth.activation_success', [
            'name'      => $name,
            'roleLabel' => 'Parent',
            'email'     => $result['email'],
            'password'  => $result['password'],
        ]);
    }
}
