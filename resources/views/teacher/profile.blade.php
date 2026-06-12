@extends('layouts.teacher')
@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your teacher account details</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Profile Picture --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Profile Picture</h2>
        <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
            <div class="w-20 h-20 rounded-full overflow-hidden shrink-0 bg-indigo-100 flex items-center justify-center">
                @if($teacher->profile_picture)
                    <img src="{{ $teacher->profile_picture }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <span class="text-indigo-700 text-2xl font-bold select-none">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                @endif
            </div>
            <form method="POST" action="{{ route('teacher.profile.upload-picture') }}" enctype="multipart/form-data">
                @csrf
                <label for="teacher-pic-input"
                       class="inline-block px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                    Change Photo
                </label>
                <input id="teacher-pic-input" type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp"
                       class="hidden" onchange="this.form.submit()">
                @error('profile_picture')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </form>
        </div>
        <p class="mt-3 text-xs text-gray-400">JPG, JPEG, PNG, or WEBP. Max 2 MB. Selecting a file saves immediately.</p>
    </div>

    {{-- Profile Info --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('teacher.profile.update') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required maxlength="255"
                       class="w-full px-3 py-2 border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }} rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">Email cannot be changed. Contact your administrator.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <input type="text" value="Teacher" disabled
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Class</label>
                <input type="text" value="{{ $classList->class_name ?? 'No class assigned yet' }}" disabled
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-colors bg-indigo-600 hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
