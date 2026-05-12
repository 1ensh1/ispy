<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Data Sync</h1>
            <p class="text-sm text-gray-500">Execute manual data pushes to the portal</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Sync Control Panel --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Sync Control Panel</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="flex items-center gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Last Sync</p>
                                <p class="text-xs text-gray-500">Feb 22, 2026 — 10:32 AM</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500 text-white">Success</span>
                    </div>
                    <button class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        Push Local Data to Portal
                    </button>
                </div>
            </div>

            {{-- Sync History --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Sync History</h3>
                <div class="divide-y divide-gray-100">
                    @foreach([
                        ['date' => 'Feb 22, 10:32 AM', 'status' => 'success', 'records' => 142],
                        ['date' => 'Feb 21, 3:15 PM',  'status' => 'success', 'records' => 89],
                        ['date' => 'Feb 20, 9:00 AM',  'status' => 'failed',  'records' => 0],
                        ['date' => 'Feb 19, 2:45 PM',  'status' => 'success', 'records' => 203],
                    ] as $s)
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-2">
                            @if($s['status'] === 'success')
                                <i data-lucide="check-circle" class="w-4 h-4 text-teal-500"></i>
                            @else
                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                            @endif
                            <span class="text-sm text-gray-700">{{ $s['date'] }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $s['records'] }} records</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
