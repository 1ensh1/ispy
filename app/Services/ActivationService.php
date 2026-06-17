<?php

namespace App\Services;

use App\Models\UserConsent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivationService
{
    public const TOKEN_TTL_HOURS = 48;

    public const TERMS_VERSION = '1.0';

    /**
     * Generate a fresh activation token plus its expiry timestamp.
     * Reused by teacher/parent account-creation and resend flows so token
     * generation and TTL live in one place.
     */
    public function generateToken(): array
    {
        return [
            'token'      => Str::random(64),
            'expires_at' => now()->addHours(self::TOKEN_TTL_HOURS),
        ];
    }

    /**
     * Look up a token and classify it as valid, expired, or invalid.
     *
     * @param string $tokenTable 'teacher_activation_tokens' or 'parent_activation_tokens'
     */
    public function checkToken(string $tokenTable, string $token): array
    {
        $row = DB::table($tokenTable)->where('token', $token)->first();

        if (!$row) {
            return ['status' => 'invalid', 'row' => null];
        }

        // Grandfathered pre-Session-18 tokens have a null expires_at and never expire.
        if (!is_null($row->expires_at) && now()->greaterThan($row->expires_at)) {
            return ['status' => 'expired', 'row' => $row];
        }

        return ['status' => 'valid', 'row' => $row];
    }

    /**
     * Validate the token, regenerate credentials, flip status to Active,
     * record consent, log activity, and consume the one-time token.
     *
     * @param string $tokenTable   'teacher_activation_tokens' or 'parent_activation_tokens'
     * @param string $roleTable    'teachers' or 'parents'
     * @param string $roleIdColumn 'teacher_id' or 'parent_id'
     * @param string $roleLabel    'Teacher' or 'Parent'
     */
    public function processConsent(
        string $tokenTable,
        string $token,
        string $roleTable,
        string $roleIdColumn,
        string $roleLabel
    ): array {
        $check = $this->checkToken($tokenTable, $token);

        if ($check['status'] !== 'valid') {
            return ['success' => false, 'reason' => $check['status']];
        }

        $tokenRow = $check['row'];

        return DB::transaction(function () use ($tokenTable, $tokenRow, $roleTable, $roleIdColumn, $roleLabel) {
            $roleRow = DB::table($roleTable)->where('id', $tokenRow->{$roleIdColumn})->first();

            if (!$roleRow) {
                return ['success' => false, 'reason' => 'invalid'];
            }

            $user = DB::table('users')->where('id', $roleRow->user_id)->first();

            if (!$user) {
                return ['success' => false, 'reason' => 'invalid'];
            }

            $newPassword = Str::random(10);

            DB::table('users')->where('id', $user->id)->update([
                'password'          => bcrypt($newPassword),
                'email_verified_at' => now(),
            ]);

            DB::table($roleTable)->where('id', $roleRow->id)->update([
                'status' => 'Active',
            ]);

            UserConsent::create([
                'user_id'       => $user->id,
                'terms_version' => self::TERMS_VERSION,
                'agreed_at'     => now(),
            ]);

            DB::table('activity_logs')->insert([
                'user_id'     => $user->id,
                'role'        => $roleLabel,
                'action'      => 'agree_terms',
                'description' => "{$roleLabel} {$roleRow->name} agreed to the Data Privacy Notice and Terms & Conditions (v" . self::TERMS_VERSION . ")",
                'created_at'  => now(),
            ]);

            DB::table('activity_logs')->insert([
                'user_id'     => $user->id,
                'role'        => $roleLabel,
                'action'      => 'activate',
                'description' => "{$roleLabel} {$roleRow->name} activated their account via email link",
                'created_at'  => now(),
            ]);

            // One-time use: remove the token so the link cannot be reused.
            DB::table($tokenTable)->where('token', $tokenRow->token)->delete();

            return ['success' => true, 'email' => $user->email, 'password' => $newPassword];
        });
    }
}
