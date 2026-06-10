<x-app-layout>
    <div class="p-6 max-w-5xl mx-auto">

        {{-- Back button --}}
        <div class="mb-6">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- ===================== PROFILE CARD ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-[#2f5597] flex items-center justify-center shrink-0">
                    @if($parent->profile_picture)
                        <img src="{{ $parent->profile_picture }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <span class="text-white text-2xl font-bold select-none">
                            {{ strtoupper(substr($parent->name, 0, 1)) }}
                        </span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $parent->name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
                </div>

                <div class="shrink-0">
                    <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-teal-500 text-white tracking-wide">
                        Active
                    </span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-4 pt-5 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Contact</p>
                    <p class="text-sm text-gray-700">{{ $parent->contact_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Active Students</p>
                    <p class="text-sm text-gray-700">{{ $activeStudents->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Archived Students</p>
                    <p class="text-sm text-gray-700">{{ $archivedStudents->count() }}</p>
                </div>
            </div>
        </div>

        {{-- ===================== ACTIVE STUDENTS ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Active Students</h2>
            </div>

            @if($activeStudents->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400">
                    <i data-lucide="inbox" class="w-7 h-7 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">No active students linked to this parent.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Student</th>
                                <th class="px-6 py-3 font-medium">Enrolled</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php
                                $iconEmoji = [
                                    'cat'     => '🐱', 'dog'     => '🐶',
                                    'bear'    => '🐻', 'rabbit'  => '🐰',
                                    'fox'     => '🦊', 'frog'    => '🐸',
                                    'penguin' => '🐧', 'lion'    => '🦁',
                                ];
                            @endphp
                            @foreach($activeStudents as $student)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($student->profile_icon && isset($iconEmoji[$student->profile_icon]))
                                            <div class="w-7 h-7 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0 text-base leading-none">
                                                {{ $iconEmoji[$student->profile_icon] }}
                                            </div>
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0">
                                                <span class="text-[#2f5597] text-xs font-bold">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <span class="font-medium text-gray-900">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    {{ $student->created_at ? $student->created_at->format('M d, Y') : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ===================== ARCHIVED STUDENTS ===================== --}}
        @if($archivedStudents->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="archive" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Archived Students</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-medium">Name</th>
                            <th class="px-6 py-3 font-medium">Archived On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($archivedStudents as $student)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-gray-500">{{ $student->name }}</td>
                            <td class="px-6 py-3 text-gray-400 text-xs">
                                {{ $student->archived_at ? \Carbon\Carbon::parse($student->archived_at)->format('M d, Y') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
