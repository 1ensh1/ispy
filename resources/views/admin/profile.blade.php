<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your administrator account details</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required maxlength="255"
                           class="w-full px-3 py-2 border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }} rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Email cannot be changed. Contact your system administrator.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <input type="text" value="Administrator" disabled
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
</x-app-layout>
