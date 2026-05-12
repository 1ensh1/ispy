<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">System Snapshots</h1>
                <p class="text-sm text-gray-500">Create and manage system backup restore points</p>
            </div>
            <button class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                <i data-lucide="database" class="w-4 h-4"></i> Create Snapshot
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Snapshot ID</th>
                            <th class="px-6 py-4 font-medium">Date</th>
                            <th class="px-6 py-4 font-medium">Size</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach([
                            ['id' => 'SNP-2026-0222', 'date' => 'Feb 22, 2026', 'size' => '2.4 GB'],
                            ['id' => 'SNP-2026-0215', 'date' => 'Feb 15, 2026', 'size' => '2.3 GB'],
                            ['id' => 'SNP-2026-0208', 'date' => 'Feb 08, 2026', 'size' => '2.1 GB'],
                            ['id' => 'SNP-2026-0201', 'date' => 'Feb 01, 2026', 'size' => '2.0 GB'],
                        ] as $s)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $s['id'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $s['date'] }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $s['size'] }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500 text-white">complete</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-md text-gray-600 hover:bg-gray-100 transition-colors">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Restore
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
