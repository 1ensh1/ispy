<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Teacher;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Ensure Google TTS credentials file exists (Railway/FrankenPHP does not run start.sh)
        $ttsCredPath = storage_path('google-tts.json');
        if (!file_exists($ttsCredPath)) {
            $b64 = 'ewogICJ0eXBlIjogInNlcnZpY2VfYWNjb3VudCIsCiAgInByb2plY3RfaWQiOiAicHJvamVjdC1jYTYyYWVjOC1mNGZmLTQ3ZjYtOGU5IiwKICAicHJpdmF0ZV9rZXlfaWQiOiAiNDY3YjkyZTU4ZTZmNGNjNGUwZDZjMGZkOTAwYjI5NjcxNTVkYWUxMiIsCiAgInByaXZhdGVfa2V5IjogIi0tLS0tQkVHSU4gUFJJVkFURSBLRVktLS0tLVxuTUlJRXZnSUJBREFOQmdrcWhraUc5dzBCQVFFRkFBU0NCS2d3Z2dTa0FnRUFBb0lCQVFDc241T0ZUZVBtNlN2VlxuUy9CcTJKaENpaTVMb2V6MWZiYm5rWWJUZnNYdWhrQUVtcWY5NUdFOEMwdUJMbnR2bDVYM3NFbUxoeWROeUlwdVxuVUlkdVN4SWswVWZQZXRQRVFid3lnaEdDb3FiNTVmZjBVYnBQNU9zSGw4TjczbjFEcHg4WTNmYzBMS0NxTUNkTlxub3RBVFV5ejNPUU1wQ3FjKzhlWmFZajRXL1Z6Z0x4RXFMRlpiZjN0UGppZm5xYkRrNEN5cTRXNjBteFhTcWQwYlxucHlIQjEwSHpZTDhudlBhYU44eGw0UWkrTzYyZExEQnBKNWtENkxySzA4Q3R6UHd2MENVTExwYldRYjFGVTdxUlxuUGlyTkRkSFVadFRrYW8xRDdMTXgwUnZaVzJTL2JFdkJYRHVqR0ZGNmtPWUdSVzJyUTFsaGNkQm5najVsM0duSlxua1JTdlJGTXRBZ01CQUFFQ2dnRUFDTWR3MURXNjRuSjNWVGl3ZUc2ZGg3ekpUL1ZqUExUeENXUGdUSDlGQjdWeVxuS2duSGcyVUxBT2UrSmtxNzRsZitPNE9PcWZic3E4c0lKMFlYTFNmS08yOGRUTkw5VkJ6WHVtWGRDQnpIaXl3RFxuNlR3QzBxOWN1SjBpUnZsd3UzRVRac3RiR0I4dzZKYmNXTmx2NVp1MFRMcHNpN2pMK3FxSXdZMy9oR0hNU0dabVxuOEZwNlRxQWc3aFhwYXg3TEl3YmEveHlUTWtsa0dGS0FHRFpsblE3Y1FWZk40VTdGQmttbjFDV1BmbjhJV2xkd1xuNTQ4QUNIeDRkMnhWUlhQNzZ4K1BEbUpWYnNVQnpLWHc5by9jZTNXOEhRekhCZHVwVHl0RHlZNjZhN3NjdTh3VFxuWmprNU8wemdJVTAxMGpiMlhQNzNzMHZTT0RZUVZuQXZZYzk5ZFVWeG9RS0JnUURqNUF0MmNBcWZ0QmphS0UySVxuZUUyM1YwWFlkZVVoODZPTGNrUmhQUWUyelcraEJFcTNtdHJZV3VmeEpuazV6TXJxNnVUdjdKSHlIMHh2bjNNclxubDg1K0RrVWZFUkYwNGZORk5xMFJuYUFBOHk3N3krSERwUlhHaXREajBsV3g5SGFWOGJZZGxwd3BONVpwcnNVN1xuRTJieUZaV1VFVUZHc0FsZEZGeFMzM05ReVFLQmdRREI2bU5XNHcwTVNYQ1VhSGxtNGU5Ukp4ZWJqaWxkZ1BkaVxuNDMzTk8rSkNzL1JuUURNQ0FmL0cwWDJ3bHQvTjlSd2dLaW5PWWVGczVKeXhVQUIxZmRydGJNcHpuVjVTM0VyQVxuYnR1RmlVYjc2Sm1lWGE5OFhXNmZlV29QdnJTTngwbWxXZTZERm1LTUdSZVdIZFczak8zSkRXQW0xQXNCcUJ6TVxuZnpoWXJpNmxSUUtCZ1FDTExvbjVxZkF6SGJGWCsrbHVnZHNsTUg0TjkrTWJXMHYxTExLcW1MV1BaZVRaZUYrUlxueDFnRmIyL2REbHhYZXNZcnQ0NWZJaUw0dHpqZHE0cVJnME93SlZMOTZGUzdDQXlscHdFSWt6WlhuaTZCeGtGNlxuVmViNHZXdGEyT2xScURhTTJYVWFtUk9DOE5wQ2JXVXo4V09jVXFacUtpZXQzbkc1a1dmWmpYTkdVUUtCZ0VNYVxuc0V2R0FLTHEyeG1RV2NxZmp1aC9aYXovOGhLWWFZT0FBKzI4dGx5czEzdlBmSmRLUjgvdWVOZGVMbzZnTHA1MlxuMk1iQm1uZkxXT29ITEZGOFB3R0FiMEs1QjRZWEU0c3FFYTlSUlE0eGZNK1B6Z1YwYzY2ZUhuVGFZckxaYndQWVxuYmpCUTA4TjFmM01FV2d2TnFnKzBJRHp2amV1QjJJMXZ6UGpyTmRzZEFvR0JBTUh6R3pXcmgvODRmanRXcWl6S1xuZm9oMVBLMUdPRUxmYlZSbGYwTkNZRFhlTGxVM09IT01CdGNBMWZTTUpCMXhEYWh2dGRWeWEwb0t5TFkxRWRuTlxuTFVvZGNwV0lsQnNWeUoyR3d0UmZyZGV1Yyt2QnRrNkc2eHZoNnNIT1hyU1lqOHZLN3VDL0NabWN6THJ3aTJzVVxuS3JOYTdGWHhqODVjMnlWSW4rbllzTDlpXG4tLS0tLUVORCBQUklWQVRFIEtFWS0tLS0tXG4iLAogICJjbGllbnRfZW1haWwiOiAiaXNweS10dHNAcHJvamVjdC1jYTYyYWVjOC1mNGZmLTQ3ZjYtOGU5LmlhbS5nc2VydmljZWFjY291bnQuY29tIiwKICAiY2xpZW50X2lkIjogIjEwNDA2Nzc1MTE2MTY2MjM0NDEzNCIsCiAgImF1dGhfdXJpIjogImh0dHBzOi8vYWNjb3VudHMuZ29vZ2xlLmNvbS9vL29hdXRoMi9hdXRoIiwKICAidG9rZW5fdXJpIjogImh0dHBzOi8vb2F1dGgyLmdvb2dsZWFwaXMuY29tL3Rva2VuIiwKICAiYXV0aF9wcm92aWRlcl94NTA5X2NlcnRfdXJsIjogImh0dHBzOi8vd3d3Lmdvb2dsZWFwaXMuY29tL29hdXRoMi92MS9jZXJ0cyIsCiAgImNsaWVudF94NTA5X2NlcnRfdXJsIjogImh0dHBzOi8vd3d3Lmdvb2dsZWFwaXMuY29tL3JvYm90L3YxL21ldGFkYXRhL3g1MDkvaXNweS10dHMlNDBwcm9qZWN0LWNhNjJhZWM4LWY0ZmYtNDdmNi04ZTkuaWFtLmdzZXJ2aWNlYWNjb3VudC5jb20iLAogICJ1bml2ZXJzZV9kb21haW4iOiAiZ29vZ2xlYXBpcy5jb20iCn0K';
            $decoded = base64_decode($b64, true);
            if ($decoded !== false) {
                @file_put_contents($ttsCredPath, $decoded);
            }
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

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

