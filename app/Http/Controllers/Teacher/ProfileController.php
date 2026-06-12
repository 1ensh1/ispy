<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\SupabaseStorageService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $teacher   = Teacher::where('user_id', auth()->id())->firstOrFail();
        $user      = auth()->user();
        $classList = $teacher->classLists()->first();
        return view('teacher.profile', compact('teacher', 'user', 'classList'));
    }

    public function update(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Teacher::where('user_id', auth()->id())->update(['name' => $request->name]);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function uploadPicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $file        = $request->file('profile_picture');
        $ext         = strtolower($file->getClientOriginalExtension());
        $filename    = 'profile_teacher_' . auth()->id() . '_' . time() . '.' . $ext;
        $binary      = file_get_contents($file->getRealPath());
        $mimeMap     = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $contentType = $mimeMap[$ext] ?? 'image/jpeg';

        $url = (new SupabaseStorageService)->uploadImage($binary, $filename, 'vocabulary', $contentType);

        if (! $url) {
            return back()->with('error', 'Failed to upload profile picture. Please try again.');
        }

        $teacher->update(['profile_picture' => $url]);

        self::log('Update', 'updated their profile picture');

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function passwordForm()
    {
        return view('teacher.password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        if ($request->new_password === $request->current_password) {
            return back()->withErrors(['new_password' => 'New password must be different from current password.'])->withInput();
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }
}
