<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function log(string $action, string $description): void
    {
        $user = auth()->user();
        $rawRole = $user?->role ?? 'System';
        $roleMap = ['admin' => 'Admin', 'teacher' => 'Teacher', 'parent' => 'Parent'];
        $role = $roleMap[strtolower($rawRole)] ?? ucfirst(strtolower($rawRole));

        ActivityLog::create([
            'user_id'     => $user?->id,
            'role'        => $role,
            'action'      => $action,
            'description' => $description,
            'created_at'  => now(),
        ]);
    }
}
