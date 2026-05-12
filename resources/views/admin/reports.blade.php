<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Institutional Reports</h1>
                <p class="text-sm text-gray-500">Generate and export analytics for vocabulary mastery and spelling performance</p>
            </div>
            <button class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i> Export Report
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Word Mastery Trends --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Word Mastery Trends</h3>
                <canvas id="masteryChart"></canvas>
            </div>

            {{-- Top Spelling Errors --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Top Spelling Errors</h3>
                <canvas id="errorsChart"></canvas>
            </div>

        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            function initCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initCharts, 100);
                    return;
                }

                new Chart(document.getElementById('masteryChart'), {
                    type: 'line',
                    data: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        datasets: [
                            {
                                label: 'Mastered',
                                data: [45, 62, 78, 95],
                                borderColor: 'rgb(20, 184, 166)',
                                backgroundColor: 'rgba(20, 184, 166, 0.08)',
                                borderWidth: 2,
                                pointRadius: 4,
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Attempted',
                                data: [120, 135, 142, 150],
                                borderColor: 'rgb(47, 85, 151)',
                                backgroundColor: 'rgba(47, 85, 151, 0.08)',
                                borderWidth: 2,
                                pointRadius: 4,
                                tension: 0.3,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { font: { size: 12 }, boxWidth: 12 } },
                        },
                        scales: {
                            x: { grid: { color: 'rgba(0,0,0,0.05)' } },
                            y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true },
                        },
                    },
                });

                new Chart(document.getElementById('errorsChart'), {
                    type: 'bar',
                    data: {
                        labels: ['Kutsara', 'Silya', 'Pinto', 'Lapis', 'Baso'],
                        datasets: [{
                            label: 'Errors',
                            data: [42, 35, 28, 22, 18],
                            backgroundColor: 'rgba(220, 38, 38, 0.75)',
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            x: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true },
                            y: { grid: { display: false } },
                        },
                    },
                });
            }

            initCharts();
        });
    </script>
</x-app-layout>
