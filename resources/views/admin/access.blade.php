<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Access Control</h1>
            <p class="text-sm text-gray-500">Role-based permission matrix for all portal users</p>
        </div>

        {{-- Role Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Administrator --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#2f5597]/10 flex items-center justify-center shrink-0">
                        <i data-lucide="shield" class="w-6 h-6 text-[#2f5597]"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Administrator</h2>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#2f5597] text-white">Full Access</span>
                    </div>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach([
                        'Full System Access',
                        'User Management',
                        'Vocabulary Management',
                        'Bilingual Asset Uploads',
                        'Access Control Settings',
                        'System Logs',
                        'Reports & Analytics',
                        'Data Sync Management',
                        'System Snapshots',
                    ] as $perm)
                    <li class="px-6 py-3 flex items-center gap-3 text-sm text-gray-700">
                        <i data-lucide="eye" class="w-4 h-4 text-[#2f5597] shrink-0"></i>
                        {{ $perm }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Teacher --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-half" class="w-6 h-6 text-teal-500"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Teacher</h2>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500 text-white">Moderate Access</span>
                    </div>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach([
                        'Student Progress View',
                        'Class List Management',
                        'Vocabulary Suggestions',
                        'Consultation Scheduling',
                        'Parent Messaging',
                        'Learning Reports (Own Class)',
                        'Engagement Record View',
                    ] as $perm)
                    <li class="px-6 py-3 flex items-center gap-3 text-sm text-gray-700">
                        <i data-lucide="eye" class="w-4 h-4 text-teal-500 shrink-0"></i>
                        {{ $perm }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Parent --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-alert" class="w-6 h-6 text-orange-400"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Parent</h2>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-400 text-white">Limited Access</span>
                    </div>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach([
                        "Child's Progress View",
                        'Vocabulary Mastery Summary',
                        'Consultation Booking',
                        'Teacher Messaging',
                        'Scan Session History',
                    ] as $perm)
                    <li class="px-6 py-3 flex items-center gap-3 text-sm text-gray-700">
                        <i data-lucide="eye" class="w-4 h-4 text-orange-400 shrink-0"></i>
                        {{ $perm }}
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>

        {{-- Note --}}
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-[#2f5597] shrink-0 mt-0.5"></i>
            <p class="text-sm text-[#2f5597]">
                Access control is enforced at the middleware level. Role assignments are managed through
                the <a href="{{ route('admin.users') }}" class="font-semibold underline underline-offset-2 hover:text-blue-800">User Accounts</a> page.
            </p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
