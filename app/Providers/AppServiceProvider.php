<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Teacher;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('layouts.teacher', function ($view) {
            if (!auth()->check()) {
                $view->with(['teacherNotifications' => collect(), 'teacherUnreadCount' => 0]);
                return;
            }

            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                $view->with(['teacherNotifications' => collect(), 'teacherUnreadCount' => 0]);
                return;
            }

            $teacherUnreadCount = DB::table('notifications')
                ->where('recipient_id', $teacher->id)
                ->where('recipient_role', 'Teacher')
                ->where('is_read', false)
                ->count();

            $teacherNotifications = DB::table('notifications')
                ->where('recipient_id', $teacher->id)
                ->where('recipient_role', 'Teacher')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $view->with(compact('teacherUnreadCount', 'teacherNotifications'));
        });
    }
}

