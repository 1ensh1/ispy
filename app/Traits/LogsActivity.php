<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

trait LogsActivity
{
    /**
     * Write an activity log entry.
     *
     * Pass $description as the action phrase only — "[past-tense verb] [subject]"
     * (e.g. "archived student Paolo Reyes"). The actor prefix "[Role] [Name] " is
     * resolved here from the authenticated user and prepended automatically, so the
     * stored description follows the canonical "[Role] [Name] [past-tense verb] [subject]".
     */
    public static function log(string $action, string $description): void
    {
        $user = auth()->user();
        $rawRole = $user?->role ?? 'System';
        $roleMap = ['admin' => 'Admin', 'teacher' => 'Teacher', 'parent' => 'Parent'];
        $role = $roleMap[strtolower($rawRole)] ?? ucfirst(strtolower($rawRole));

        // Resolve the actor's display name from the role-specific table,
        // falling back to the users table when no profile row exists.
        $name = null;
        if ($user) {
            $table = match ($role) {
                'Admin'   => 'administrators',
                'Teacher' => 'teachers',
                'Parent'  => 'parents',
                default   => null,
            };
            if ($table) {
                $name = DB::table($table)->where('user_id', $user->id)->value('name');
            }
            $name = $name ?: $user->name;
        }

        $prefix = trim($role . ' ' . ($name ?? ''));
        $fullDescription = $prefix !== '' ? "{$prefix} {$description}" : $description;

        ActivityLog::create([
            'user_id'     => $user?->id,
            'role'        => $role,
            'action'      => $action,
            'description' => $fullDescription,
            'created_at'  => now(),
        ]);
    }
}
