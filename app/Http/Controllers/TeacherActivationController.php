<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherActivationToken;
use Illuminate\Support\Facades\DB;

class TeacherActivationController extends Controller
{
    public function activate(string $token)
    {
        $activation = TeacherActivationToken::where('token', $token)->first();

        if (!$activation) {
            return view('auth.activation_invalid');
        }

        $teacher = Teacher::find($activation->teacher_id);

        if (!$teacher) {
            return view('auth.activation_invalid');
        }

        $teacher->status = 'Active';
        $teacher->save();

        // One-time use: remove the token so the link cannot be reused.
        $activation->delete();

        // Convenience signal on the user record.
        DB::table('users')
            ->where('id', $teacher->user_id)
            ->update(['email_verified_at' => now()]);

        DB::table('activity_logs')->insert([
            'user_id'     => $teacher->user_id,
            'role'        => 'Teacher',
            'action'      => 'activate',
            'description' => 'Teacher account activated via email link',
            'created_at'  => now(),
        ]);

        return redirect()->route('login')
            ->with('status', 'Your account has been activated. You may now log in with your temporary password.');
    }
}
