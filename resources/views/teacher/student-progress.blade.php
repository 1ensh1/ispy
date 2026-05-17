@extends('layouts.teacher')
@section('title', 'Student Progress')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Student Progress</h2>
        <p class="text-sm text-gray-500 mt-1">Review performance snapshots for each student</p>
    </div>

    {{-- Search bar --}}
    <div>
        <div class="relative w-full max-w-sm">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input id="student-search"
                   type="text"
                   placeholder="Search students..."
                   oninput="filterStudents()"
                   class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 bg-white">
        </div>
    </div>

    {{-- Student cards grid --}}
    @if($students->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <i data-lucide="users" class="w-10 h-10 text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-400 font-medium">No students enrolled yet.</p>
            <p class="text-xs text-gray-300 mt-1">Students will appear here once they join your class.</p>
        </div>
    @else
        <div id="students-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($students as $student)
                <div class="student-card-wrapper" data-name="{{ strtolower($student->name) }}">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200 p-5">

                        {{-- Name + last active --}}
                        <div class="flex items-start justify-between gap-2 mb-4">
                            <span class="font-semibold text-gray-900 text-sm leading-snug">{{ $student->name }}</span>
                            <span class="text-xs text-gray-400 whitespace-nowrap shrink-0">
                                @if($student->last_active)
                                    {{ \Carbon\Carbon::parse($student->last_active)->diffForHumans() }}
                                @else
                                    No activity
                                @endif
                            </span>
                        </div>

                        {{-- Stat boxes --}}
                        <div class="grid grid-cols-3 gap-3">

                            {{-- Words Mastered --}}
                            <div class="bg-gray-50 rounded-lg p-3 flex flex-col items-center gap-1.5">
                                <i data-lucide="book-open" class="w-5 h-5 text-blue-500"></i>
                                <span class="font-bold text-base text-gray-800 leading-none">{{ $student->words_mastered }}</span>
                                <span class="text-[11px] text-gray-400">Words</span>
                            </div>

                            {{-- Scans --}}
                            <div class="bg-gray-50 rounded-lg p-3 flex flex-col items-center gap-1.5">
                                <i data-lucide="eye" class="w-5 h-5 text-teal-500"></i>
                                <span class="font-bold text-base text-gray-800 leading-none">{{ $student->scans }}</span>
                                <span class="text-[11px] text-gray-400">Scans</span>
                            </div>

                            {{-- Pronunciation --}}
                            <div class="bg-gray-50 rounded-lg p-3 flex flex-col items-center gap-1.5">
                                <i data-lucide="volume-2" class="w-5 h-5 text-orange-400"></i>
                                <span class="font-bold text-base text-orange-400 leading-none">{{ $student->pronunciation }}%</span>
                                <span class="text-[11px] text-gray-400">Pronun.</span>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- No results message (hidden by default) --}}
        <div id="no-results" class="hidden flex flex-col items-center justify-center py-16 text-center">
            <i data-lucide="search-x" class="w-8 h-8 text-gray-300 mb-2"></i>
            <p class="text-sm text-gray-400">No students match your search.</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function filterStudents() {
        const q = document.getElementById('student-search').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.student-card-wrapper');
        let visible = 0;

        cards.forEach(function (card) {
            const name = card.dataset.name || '';
            const show = name.includes(q);
            card.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        const noResults = document.getElementById('no-results');
        if (noResults) noResults.classList.toggle('hidden', visible > 0);
    }
</script>
@endpush
