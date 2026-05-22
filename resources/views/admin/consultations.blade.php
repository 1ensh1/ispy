<x-app-layout>
    <div x-data="{
            searchQuery: '',
            teacherFilter: 'all',
            appointments: @json($rows),
            get filtered() {
                const q = this.searchQuery.toLowerCase();
                return this.appointments.filter(a => {
                    const matchSearch  = !q || a.parent.toLowerCase().includes(q) || a.student.toLowerCase().includes(q);
                    const matchTeacher = this.teacherFilter === 'all' || a.teacher_id === parseInt(this.teacherFilter);
                    return matchSearch && matchTeacher;
                });
            },
            statusBadge(status) {
                const map = {
                    'Pending':   'bg-blue-100 text-blue-700',
                    'Confirmed': 'bg-green-100 text-green-700',
                    'Completed': 'bg-teal-100 text-teal-700',
                    'Cancelled': 'bg-gray-100 text-gray-600',
                    'No-show':   'bg-red-100 text-red-700',
                };
                return map[status] || 'bg-gray-100 text-gray-600';
            },
            statusLabel(status) { return status; }
         }"
         class="p-6 max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Face-to-Face Consultations</h1>
                <p class="text-sm text-gray-500">Track and manage scheduled consultation appointments between teachers and parents</p>
            </div>
            <a :href="`{{ url('/admin/consultations/export') }}` + (teacherFilter !== 'all' ? `?teacher_id=${teacherFilter}` : '')"
               class="flex items-center gap-2 px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i> Export Records
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i data-lucide="calendar-check" class="w-5 h-5 text-blue-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $upcoming }}</p>
                        <p class="text-xs text-gray-500">Upcoming</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $completed }}</p>
                        <p class="text-xs text-gray-500">Completed</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-5 h-5 text-gray-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $cancelled }}</p>
                        <p class="text-xs text-gray-500">Cancelled</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $noshow }}</p>
                        <p class="text-xs text-gray-500">No-show</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <div class="relative max-w-xs w-full">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                <input type="text" x-model="searchQuery" placeholder="Search parent or student..."
                       class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#2f5597]/30 focus:border-[#2f5597]">
            </div>
            <select x-model="teacherFilter"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#2f5597]/30 focus:border-[#2f5597]">
                <option value="all">All Teachers</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Teacher</th>
                            <th class="px-4 py-3 font-medium">Parent</th>
                            <th class="px-4 py-3 font-medium">Student</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">Time</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(a, i) in filtered" :key="i">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900" x-text="a.teacher"></td>
                                <td class="px-4 py-3 text-gray-700" x-text="a.parent"></td>
                                <td class="px-4 py-3 text-gray-500" x-text="a.student"></td>
                                <td class="px-4 py-3 text-gray-500" x-text="a.date"></td>
                                <td class="px-4 py-3 text-gray-500" x-text="a.time"></td>
                                <td class="px-4 py-3">
                                    <span :class="'px-2.5 py-0.5 rounded-full text-xs font-medium ' + statusBadge(a.status)"
                                          x-text="statusLabel(a.status)"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                                No appointments found.
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
