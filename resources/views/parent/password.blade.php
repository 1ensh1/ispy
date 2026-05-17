@extends('layouts.parent')
@section('title', 'Change Password')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
        <p class="text-sm text-gray-500 mt-0.5">Update your web portal login password</p>
    </div>

    <div class="flex items-start gap-3 p-4 rounded-xl text-sm text-blue-800 border border-blue-200 bg-blue-50">
        <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5 text-blue-500"></i>
        <p>This changes your <strong>web portal login password</strong>. To change your child's mobile app access password, go to the <a href="{{ route('parent.dashboard') }}" class="underline font-medium">Dashboard</a>.</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('parent.portal.password.change') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full px-3 py-2 border {{ $errors->has('current_password') ? 'border-red-400' : 'border-gray-200' }} rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="new_password" required minlength="8"
                       class="w-full px-3 py-2 border {{ $errors->has('new_password') ? 'border-red-400' : 'border-gray-200' }} rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                @error('new_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background:#1a2332;">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
