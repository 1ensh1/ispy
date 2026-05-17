<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $parent   = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $user     = auth()->user();
        $students = Student::where('parent_id', $parent->id)->with('classList')->get();
        return view('parent.profile', compact('parent', 'user', 'students'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        $parent = ParentProfile::where('user_id', auth()->id())->firstOrFail();
        $parent->update([
            'name'           => $request->name,
            'contact_number' => $request->contact_number,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function passwordForm()
    {
        return view('parent.password');
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
