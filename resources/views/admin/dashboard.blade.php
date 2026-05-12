<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Administrator Dashboard</h1>
            <p class="text-sm text-gray-500">System overview and management controls</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
                    <i data-lucide="users" class="w-5 h-5 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold">{{ $totalUsers }}</div>
                <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-3 h-3"></i> 12% this month
                </p>
            </div>
            
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-gray-500">Active Students</h3>
                    <i data-lucide="graduation-cap" class="w-5 h-5 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold">15</div>
                <p class="text-xs text-gray-500 mt-1">Enrolled</p>
            </div>
            
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-gray-500">Vocabulary Items</h3>
                    <i data-lucide="book-open" class="w-5 h-5 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold">0</div>
                <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-3 h-3"></i> 48 new this week
                </p>
            </div>
            
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-gray-500">System Health</h3>
                    <i data-lucide="shield" class="w-5 h-5 text-gray-400"></i>
                </div>
                <div class="text-2xl font-bold">98.5%</div>
                <p class="text-xs text-gray-500 mt-1">All services running</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <h3 class="font-semibold mb-4 text-gray-800">Weekly Session Activity</h3>
                <div class="relative h-60 w-full">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <h3 class="font-semibold mb-4 text-gray-800">User Distribution</h3>
                <div class="relative h-48 w-full flex justify-center">
                    <canvas id="roleChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h3 class="font-semibold mb-4 text-gray-800">Recent Activity</h3>
            <div class="space-y-3">
                @foreach($recentLogs as $log)
                <div class="flex items-center justify-between py-3 border-b last:border-0 border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                            <i data-lucide="activity" class="w-4 h-4 text-gray-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $log['user'] }}</p>
                            <p class="text-xs text-gray-500">{{ $log['action'] }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $log['time'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Re-initialize Lucide icons for the new content
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // 1. Weekly Activity Bar Chart
            const activityCtx = document.getElementById('activityChart').getContext('2d');
            new Chart(activityCtx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Sessions',
                        data: [42, 58, 35, 67, 52, 18, 12],
                        backgroundColor: '#1e40af', // Matches hsl(210,60%,38%)
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [3, 3] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. User Distribution Pie (Doughnut) Chart
            const roleCtx = document.getElementById('roleChart').getContext('2d');
            new Chart(roleCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Teachers', 'Parents', 'Students'],
                    datasets: [{
                        data: [{{ $teacherCount }}, {{ $parentCount }}, {{ $studentCount }}],
                        backgroundColor: ['#14b8a6', '#f59e0b', '#1e40af'], 
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    }
                }
            });
        });
    </script>
</x-app-layout>