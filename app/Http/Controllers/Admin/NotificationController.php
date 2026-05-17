<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function redirect($id)
    {
        $notif = DB::table('notifications')
            ->where('id', $id)
            ->where('recipient_id', auth()->id())
            ->where('recipient_role', 'Admin')
            ->first();

        abort_if(!$notif, 403);

        DB::table('notifications')->where('id', $id)->update(['is_read' => true]);

        return redirect($notif->action_url ?? route('admin.dashboard'));
    }
}
