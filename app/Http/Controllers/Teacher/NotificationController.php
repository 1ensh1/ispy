<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function redirect($id)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $notif = DB::table('notifications')
            ->where('id', $id)
            ->where('recipient_id', $teacher->id)
            ->where('recipient_role', 'Teacher')
            ->first();

        abort_if(!$notif, 403);

        DB::table('notifications')->where('id', $id)->update(['is_read' => true]);

        return redirect($notif->action_url ?? route('teacher.dashboard'));
    }
}
