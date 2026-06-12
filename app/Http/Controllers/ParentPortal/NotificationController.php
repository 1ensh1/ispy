<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function redirect($id)
    {
        $parent = ParentProfile::where('user_id', auth()->id())->firstOrFail();

        $notif = DB::table('notifications')
            ->where('id', $id)
            ->where('recipient_id', $parent->id)
            ->where('recipient_role', 'Parent')
            ->first();

        abort_if(!$notif, 403);

        DB::table('notifications')->where('id', $id)->update(['is_read' => true]);

        return redirect($notif->action_url ?? route('parent.dashboard'));
    }
}
