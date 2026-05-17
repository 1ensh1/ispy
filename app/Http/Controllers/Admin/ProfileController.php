<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
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
