<x-app-layout>
<div class="p-6 max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Progress Reports</h1>
            <p class="text-sm text-gray-500 mt-0.5">Student mastery and learning activity overview</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap no-print">
            <a href="{{ route('admin.reports.export.progress', array_filter(request()->only(['teacher_id', 'search', 'proficiency']))) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                Progress CSV
            </a>
            <a href="{{ route('admin.reports.export.mastery', array_filter(request()->only(['teacher_id', 'search', 'proficiency']))) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                Mastery CSV
            </a>
            <a href="{{ route('admin.reports.export.captured', array_filter(request()->only(['teacher_id', 'search', 'proficiency']))) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                Captured CSV
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print / PDF
            </button>
        </div>
    </div>

    <style>
    @media print {
        nav, aside, header, .no-print, footer { display: none !important; }
        body { background: white !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; font-size: 12px; }
        a { color: black !important; text-decoration: none !important; }
        .shadow-sm { box-shadow: none !important; }
    }
    </style>

    {{-- System overview --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">System Overview</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $totalStudents }}</p>
                <p class="text-sm text-gray-500 mt-0.5">Total Students</p>
            </div>

            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $avgMastery }}%</p>
                <p class="text-sm text-gray-500 mt-0.5">Avg Mastery Score</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">Proficiency Distribution</p>
                <div class="flex flex-wrap gap-2">
                    <span style="display:inline-block; padding:0.2rem 0.7rem; border-radius:9999px; background:#0d9488; color:#fff; font-size:0.75rem; font-weight:600;">
                        Mastered: {{ $profDist['Mastered'] ?? 0 }}
                    </span>
                    <span style="display:inline-block; padding:0.2rem 0.7rem; border-radius:9999px; background:#f59e0b; color:#fff; font-size:0.75rem; font-weight:600;">
                        Developing: {{ $profDist['Developing'] ?? 0 }}
                    </span>
                    <span style="display:inline-block; padding:0.2rem 0.7rem; border-radius:9999px; background:#ef4444; color:#fff; font-size:0.75rem; font-weight:600;">
                        Beginning: {{ $profDist['Beginning'] ?? 0 }}
                    </span>
                </div>
            </div>

        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.reports') }}" id="filter-form" class="flex flex-wrap items-end gap-3">

        {{-- Search --}}
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input type="text" name="search" id="search-students-report"
                   value="{{ request('search') }}"
                   placeholder="Search students…"
                   class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 outline-none w-52">
        </div>

        {{-- Teacher filter --}}
        <div class="relative">
            <select name="teacher_id" onchange="document.getElementById('filter-form').submit()"
                    class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 outline-none bg-white appearance-none min-w-[160px]">
                <option value="">All Teachers</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </div>

        {{-- Proficiency filter --}}
        <div class="relative">
            <select name="proficiency" onchange="document.getElementById('filter-form').submit()"
                    class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 outline-none bg-white appearance-none min-w-[160px]">
                <option value="">All Proficiency</option>
                <option value="Mastered"   {{ request('proficiency') === 'Mastered'   ? 'selected' : '' }}>Mastered</option>
                <option value="Developing" {{ request('proficiency') === 'Developing' ? 'selected' : '' }}>Developing</option>
                <option value="Beginning"  {{ request('proficiency') === 'Beginning'  ? 'selected' : '' }}>Beginning</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </div>

        {{-- Per-page selector --}}
        <div class="relative">
            <select name="per_page" onchange="document.getElementById('filter-form').submit()"
                    class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 outline-none bg-white appearance-none min-w-[120px]">
                <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10 / page</option>
                <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20 / page</option>
                <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50 / page</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </div>

        @if(request('search') || request('teacher_id') || request('proficiency'))
            <a href="{{ route('admin.reports') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">Clear</a>
        @endif

    </form>

    {{-- Student table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" id="report-table">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Student</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Class</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Teacher</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Mastered</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Developing</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide text-center">Beginning</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Dominant</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="tbody-students-report">
                    @forelse($students as $student)
                        @php
                            $badgeBg = match($student->dominant) {
                                'Mastered'   => '#0d9488',
                                'Developing' => '#f59e0b',
                                'Beginning'  => '#ef4444',
                                default      => '#9ca3af',
                            };
                        @endphp
                        <tr class="report-row hover:bg-gray-50 transition-colors" data-name="{{ strtolower($student->name) }} {{ strtolower($student->class_name ?? '') }} {{ strtolower($student->teacher ?? '') }}">
                            <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $student->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $student->class_name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $student->teacher }}</td>
                            <td class="px-5 py-3.5 text-center font-medium text-teal-700">{{ $student->mastered }}</td>
                            <td class="px-5 py-3.5 text-center font-medium text-yellow-600">{{ $student->developing }}</td>
                            <td class="px-5 py-3.5 text-center font-medium text-red-500">{{ $student->beginning }}</td>
                            <td class="px-5 py-3.5">
                                <span style="display:inline-block; padding:0.2rem 0.65rem; border-radius:9999px; background:{{ $badgeBg }}; color:#fff; font-size:0.75rem; font-weight:600;">
                                    {{ $student->dominant }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.reports.show', $student->id) }}"
                                   class="text-[#2f5597] text-sm font-medium hover:underline whitespace-nowrap">
                                    View Details →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                                <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <p class="text-sm">No students found matching your search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->total() > 0)
            <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 no-print">
                <p class="text-sm text-gray-500">
                    Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }} students
                </p>
                @if($students->hasPages())
                    <div>{{ $students->links() }}</div>
                @endif
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
@endpush
</x-app-layout>
