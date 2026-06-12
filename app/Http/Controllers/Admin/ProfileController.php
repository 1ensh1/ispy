<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Services\SupabaseStorageService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $admin = Administrator::firstOrCreate(
            ['user_id' => auth()->id()],
            ['name'    => auth()->user()->name]
        );
        $user = auth()->user();
        return view('admin.profile', compact('admin', 'user'));
    }

    public function update(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $admin = Administrator::firstOrCreate(
            ['user_id' => auth()->id()],
            ['name'    => auth()->user()->name]
        );
        $admin->update(['name' => $request->name]);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function uploadPicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $admin = Administrator::firstOrCreate(
            ['user_id' => auth()->id()],
            ['name'    => auth()->user()->name]
        );

        $file        = $request->file('profile_picture');
        $ext         = strtolower($file->getClientOriginalExtension());
        $filename    = 'profile_admin_' . auth()->id() . '_' . time() . '.' . $ext;
        $binary      = file_get_contents($file->getRealPath());
        $mimeMap     = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $contentType = $mimeMap[$ext] ?? 'image/jpeg';

        $url = (new SupabaseStorageService)->uploadImage($binary, $filename, 'vocabulary', $contentType);

        if (! $url) {
            return back()->with('error', 'Failed to upload profile picture. Please try again.');
        }

        $admin->update(['profile_picture' => $url]);

        self::log('Update', 'updated their profile picture');

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function passwordForm()
    {
        return view('admin.password');
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
