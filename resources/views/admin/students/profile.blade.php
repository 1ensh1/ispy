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

        {{-- ===================== PROFILE CARD ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            @php
                $iconEmoji = [
                    'cat'     => '🐱', 'dog'     => '🐶',
                    'bear'    => '🐻', 'rabbit'  => '🐰',
                    'fox'     => '🦊', 'frog'    => '🐸',
                    'penguin' => '🐧', 'lion'    => '🦁',
                ];
            @endphp
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0 text-4xl leading-none">
                    @if($student->profile_icon && isset($iconEmoji[$student->profile_icon]))
                        {{ $iconEmoji[$student->profile_icon] }}
                    @else
                        <span class="text-[#2f5597] text-2xl font-bold">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $student->name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Parent: {{ $student->parentUser->name ?? '—' }}</p>
                </div>

                <div class="shrink-0">
                    @if($student->archived_at)
                        <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-yellow-100 text-yellow-700 border border-yellow-200 tracking-wide">
                            Archived
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-teal-500 text-white tracking-wide">
                            Active
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-4 pt-5 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Class</p>
                    <p class="text-sm text-gray-700">{{ $student->classList->class_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Enrolled</p>
                    <p class="text-sm text-gray-700">{{ $student->created_at ? $student->created_at->format('M d, Y') : '—' }}</p>
                </div>
                @if($student->archived_at)
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Archived</p>
                    <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($student->archived_at)->format('M d, Y') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ===================== MASTERY SCORES ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="star" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Top Mastery Scores</h2>
            </div>

            @if($masteryScores->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400">
                    <i data-lucide="inbox" class="w-7 h-7 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">No mastery data yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Word</th>
                                <th class="px-6 py-3 font-medium">Score</th>
                                <th class="px-6 py-3 font-medium">Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($masteryScores as $score)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $score->english_label }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ number_format($score->total_score, 1) }}</td>
                                <td class="px-6 py-3">
                                    @php $level = strtolower($score->proficiency_level ?? ''); @endphp
                                    @if(str_contains($level, 'master'))
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-700 border border-teal-200">Mastered</span>
                                    @elseif(str_contains($level, 'develop'))
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Developing</span>
                                    @elseif($score->proficiency_level)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600 border border-red-200">{{ $score->proficiency_level }}</span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ===================== RECENT ACTIVITY ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Recent Activity</h2>
            </div>

            @if($recentActivity->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400">
                    <i data-lucide="inbox" class="w-7 h-7 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">No activity recorded yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Word</th>
                                <th class="px-6 py-3 font-medium">Mode</th>
                                <th class="px-6 py-3 font-medium">Score</th>
                                <th class="px-6 py-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentActivity as $activity)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $activity->english_label }}</td>
                                <td class="px-6 py-3 text-gray-500 capitalize">{{ $activity->mode }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ number_format($activity->score, 1) }}</td>
                                <td class="px-6 py-3 text-gray-400 text-xs">
                                    {{ $activity->attempted_at ? \Carbon\Carbon::parse($activity->attempted_at)->format('M d, Y') : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
