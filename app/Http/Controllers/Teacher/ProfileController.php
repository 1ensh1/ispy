<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
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
