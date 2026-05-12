<x-app-layout>
    <div x-data="{
            search: '',
            logs: [
                { user: 'Dr. Santos', action: 'Created teacher account T-005',          timestamp: '2026-02-22 10:45:12', type: 'user'     },
                { user: 'System',     action: 'Automated backup completed',              timestamp: '2026-02-22 09:00:00', type: 'system'   },
                { user: 'Ms. Reyes',  action: 'Synced 142 student records',              timestamp: '2026-02-22 08:32:15', type: 'sync'     },
                { user: 'Admin',      action: 'Updated vocabulary: added 12 words',      timestamp: '2026-02-21 16:20:33', type: 'content'  },
                { user: 'Mr. Santos', action: 'Conducted video consultation',            timestamp: '2026-02-21 14:15:00', type: 'consult'  },
                { user: 'System',     action: 'Failed login attempt - IP 192.168.1.45', timestamp: '2026-02-21 11:02:47', type: 'security' },
                { user: 'Ms. Cruz',   action: 'Exported institutional report',           timestamp: '2026-02-20 15:30:00', type: 'report'   },
                { user: 'System',     action: 'Database migration completed',            timestamp: '2026-02-20 03:00:00', type: 'system'   },
            ],
            get filtered() {
                if (!this.search) return this.logs;
                const q = this.search.toLowerCase();
                return this.logs.filter(l =>
                    l.user.toLowerCase().includes(q) || l.action.toLowerCase().includes(q)
                );
            },
            badgeClass(type) {
                const map = {
                    user:     'bg-blue-100 text-blue-700',
                    system:   'bg-gray-100 text-gray-600',
                    sync:     'bg-teal-100 text-teal-700',
                    content:  'bg-purple-100 text-purple-700',
                    consult:  'bg-orange-100 text-orange-700',
                    security: 'bg-red-100 text-red-700',
                    report:   'bg-indigo-100 text-indigo-700',
                };
                return map[type] || 'bg-gray-100 text-gray-600';
            }
         }"
         class="p-6 max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-1">System Logs</h1>
            <p class="text-sm text-gray-500">Review system activity and user actions</p>
        </div>

        {{-- Search --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="relative max-w-sm w-full">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                <input type="text" x-model="search" placeholder="Search logs..."
                       class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#2f5597]/30 focus:border-[#2f5597]">
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Timestamp</th>
                            <th class="px-4 py-3 font-medium">User</th>
                            <th class="px-4 py-3 font-medium">Action</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(l, i) in filtered" :key="i">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs text-gray-400" x-text="l.timestamp"></td>
                                <td class="px-4 py-3 font-medium text-gray-900" x-text="l.user"></td>
                                <td class="px-4 py-3 text-gray-700" x-text="l.action"></td>
                                <td class="px-4 py-3">
                                    <span :class="'px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ' + badgeClass(l.type)"
                                          x-text="l.type"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="4" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No log entries match your search.
                            </td>
                        </tr>
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
