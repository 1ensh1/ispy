@extends('layouts.teacher')
@section('title', 'Spelling Analysis')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Spelling Error Analysis</h2>
        <p class="text-sm text-gray-500 mt-1">Analyze common spelling errors and phoneme breakdowns</p>
    </div>

    {{-- Two-panel grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        {{-- Left: Most Frequent Error Words --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-5">Most Frequent Error Words</h3>

            @if(empty($barChartData['labels']))
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <i data-lucide="bar-chart-2" class="w-9 h-9 text-gray-200 mb-3"></i>
                    <p class="text-sm text-gray-400">No error data yet.</p>
                    <p class="text-xs text-gray-300 mt-1">Error data will appear once students complete spelling activities.</p>
                </div>
            @else
                <div class="relative">
                    <canvas id="errorWordsChart" height="220"></canvas>
                </div>
            @endif
        </div>

        {{-- Right: Phoneme Error Heatmap --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-5">Phoneme Error Heatmap</h3>

            @php
                $phonemeList = ['a','e','i','o','u','k','t','s','n','l','p','r'];

                function phonemeBg(int $count): string {
                    if ($count >= 70) return '#dc2626';
                    if ($count >= 50) return '#ea580c';
                    if ($count >= 30) return '#ca8a04';
                    return '#16a34a';
                }

                function phonemeText(int $count): string {
                    return 'text-white';
                }

                function phonemeSubtext(int $count): string {
                    return 'text-white/80';
                }
            @endphp

            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.75rem;">
                @foreach($phonemeList as $letter)
                    @php $count = $phonemeCounts[$letter] ?? 0; @endphp
                    <div style="background-color:{{ phonemeBg($count) }}; border-radius:0.75rem; padding:1rem 0.5rem; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:0.25rem; aspect-ratio:1;">
                        <span style="font-size:1.5rem; font-weight:700; font-style:italic; color:#ffffff;">{{ $letter }}</span>
                        <span style="font-size:0.6875rem; color:rgba(255,255,255,0.85);">{{ $count }} errors</span>
                    </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-4 mt-5 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-sm shrink-0" style="background:#16a34a;"></span>
                    <span class="text-xs text-gray-500">Low</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-sm shrink-0" style="background:#ca8a04;"></span>
                    <span class="text-xs text-gray-500">Medium</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-sm shrink-0" style="background:#ea580c;"></span>
                    <span class="text-xs text-gray-500">High</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-sm shrink-0" style="background:#dc2626;"></span>
                    <span class="text-xs text-gray-500">Critical</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const d = @json($barChartData);
    const canvas = document.getElementById('errorWordsChart');
    if (!canvas || !d.labels.length) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: '#ef4444',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f3f4f6' },
                },
                x: {
                    grid: { display: false },
                }
            }
        }
    });
})();
</script>
@endpush
