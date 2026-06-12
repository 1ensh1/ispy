<x-app-layout>
<div class="p-6 max-w-7xl mx-auto space-y-6">

    <style>
    @media print {
        nav, aside, header, .no-print, footer { display: none !important; }
        body { background: white !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; font-size: 12px; }
        a { color: black !important; text-decoration: none !important; }
        .shadow-sm { box-shadow: none !important; }
        select { display: none; }
    }
    </style>

    {{-- Back + heading --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 no-print" style="margin-bottom:0.75rem; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $student->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ optional($student->classList)->class_name ?? 'No class assigned' }}
                @if(optional(optional($student->classList)->teacher)->name)
                    &mdash; {{ $student->classList->teacher->name }}
                @endif
            </p>
        </div>
        <div class="shrink-0 no-print">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print / Save as PDF
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.reports.show', $student->id) }}" id="detail-filter-form"
          class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
            <div class="relative">
                <select name="category" onchange="document.getElementById('detail-filter-form').submit()"
                        class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 outline-none bg-white appearance-none min-w-[160px]">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Mode</label>
            <div class="relative">
                <select name="mode" onchange="document.getElementById('detail-filter-form').submit()"
                        class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 outline-none bg-white appearance-none min-w-[160px]">
                    <option value="">All Modes</option>
                    @foreach($modes as $mode)
                        <option value="{{ $mode }}" {{ request('mode') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
            </div>
        </div>
        @if(request('category') || request('mode'))
            <a href="{{ route('admin.reports.show', $student->id) }}"
               class="text-sm text-gray-500 hover:text-gray-700 underline self-end pb-2">Clear</a>
        @endif
    </form>

    {{-- Learning Activity --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Learning Activity</h2>
            <p class="text-xs text-gray-500 mt-0.5">Student progress records by word and mode</p>
        </div>
        @if($progress->isEmpty())
            <div class="px-5 py-12 text-center text-gray-400">
                <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                <p class="text-sm">No learning activity recorded for this student yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Word</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Category</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Mode</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Attempts</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Score</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Errors</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Weight</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Attempted At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($progress as $row)
                            @php
                                $errors = is_array($row->errors) ? implode(', ', $row->errors) : ($row->errors ?? '—');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 font-medium text-gray-900">
                                    {{ optional($row->vocabulary)->filipino_label ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ optional($row->vocabulary)->category ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $row->mode }}</td>
                                <td class="px-5 py-3 text-center text-gray-700">{{ $row->attempts }}</td>
                                <td class="px-5 py-3 text-center font-medium text-gray-900">{{ $row->score }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs max-w-xs truncate" title="{{ $errors }}">{{ $errors }}</td>
                                <td class="px-5 py-3 text-center text-gray-600">{{ $row->mastery_weight }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs">
                                    {{ $row->attempted_at ? \Carbon\Carbon::parse($row->attempted_at)->format('M j, Y H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Mastery Scores --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Mastery Scores</h2>
            <p class="text-xs text-gray-500 mt-0.5">Per-word proficiency levels</p>
        </div>
        @if($masteryScores->isEmpty())
            <div class="px-5 py-10 text-center text-gray-400">
                <p class="text-sm">No mastery scores recorded for this student yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Word</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Category</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Total Score</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Proficiency</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($masteryScores as $score)
                            @php
                                $bg = match($score->proficiency_level) {
                                    'Mastered'   => '#0d9488',
                                    'Developing' => '#f59e0b',
                                    'Beginning'  => '#ef4444',
                                    default      => '#9ca3af',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ optional($score->vocabulary)->filipino_label ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ optional($score->vocabulary)->category ?? '—' }}</td>
                                <td class="px-5 py-3 text-center font-semibold text-gray-900">{{ $score->total_score }}</td>
                                <td class="px-5 py-3">
                                    <span style="display:inline-block; padding:0.2rem 0.65rem; border-radius:9999px; background:{{ $bg }}; color:#fff; font-size:0.75rem; font-weight:600;">
                                        {{ $score->proficiency_level }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Captured Objects --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Captured Objects</h2>
            <p class="text-xs text-gray-500 mt-0.5">Images captured by the student in the mobile app</p>
        </div>
        @if($capturedObjects->isEmpty())
            <div class="px-5 py-12 text-center text-gray-400">
                <i data-lucide="camera" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                <p class="text-sm">No captured objects recorded for this student yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Word</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Image URL</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Match?</th>
                            <th class="px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Captured At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($capturedObjects as $obj)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ optional($obj->vocabulary)->filipino_label ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs max-w-xs truncate">
                                    @if($obj->captured_image_url)
                                        <a href="{{ $obj->captured_image_url }}" target="_blank"
                                           class="text-[#2f5597] hover:underline truncate block max-w-xs">
                                            {{ $obj->captured_image_url }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($obj->is_successful_match)
                                        <span class="inline-flex items-center gap-1 text-teal-700 font-medium text-xs">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i> Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-red-500 font-medium text-xs">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i> No
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-500 text-xs">
                                    {{ $obj->captured_at ? \Carbon\Carbon::parse($obj->captured_at)->format('M j, Y H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
</x-app-layout>
