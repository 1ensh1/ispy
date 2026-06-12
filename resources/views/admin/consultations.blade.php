<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Face-to-Face Consultations</h1>
                <p class="text-sm text-gray-500">Track and manage scheduled consultation appointments between teachers and parents</p>
            </div>
            <a id="export-link" href="{{ url('/admin/consultations/export') }}"
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
        <form method="GET" action="{{ url('/admin/consultations') }}" class="flex flex-wrap items-center gap-3 mb-4">
            <div class="relative max-w-xs w-full">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search parent or student..."
                       class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#2f5597]/30 focus:border-[#2f5597]">
            </div>
            <select name="teacher_id" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#2f5597]/30 focus:border-[#2f5597]">
                <option value="all">All Teachers</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ (string) request('teacher_id') === (string) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                Search
            </button>
            @if(request('search') || (request('teacher_id') && request('teacher_id') !== 'all'))
                <a href="{{ url('/admin/consultations') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 underline">Clear filters</a>
            @endif

            <select name="per_page" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#2f5597]/30 focus:border-[#2f5597] ml-auto">
                <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10 / page</option>
                <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20 / page</option>
                <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50 / page</option>
            </select>
        </form>

        @php
            $statusBadge = [
                'Pending'   => 'bg-blue-100 text-blue-700',
                'Confirmed' => 'bg-green-100 text-green-700',
                'Completed' => 'bg-teal-100 text-teal-700',
                'Cancelled' => 'bg-gray-100 text-gray-600',
                'Rejected'  => 'bg-red-100 text-red-700',
                'No-show'   => 'bg-red-100 text-red-700',
            ];
        @endphp

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
                    <tbody id="consultations-tbody" class="divide-y divide-gray-100">
                        @forelse($rows as $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row['teacher'] }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $row['parent'] }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $row['student'] }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $row['date'] }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $row['time'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$row['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">No appointments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rows->hasPages())
                <div class="px-4 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>

    </div>

    <script>
    // Keep the export link in sync with the active teacher filter.
    document.addEventListener('DOMContentLoaded', function () {
        const teacherFilter = @json(request('teacher_id'));
        const exportLink    = document.getElementById('export-link');
        if (exportLink && teacherFilter && teacherFilter !== 'all') {
            exportLink.href = '{{ url('/admin/consultations/export') }}?teacher_id=' + encodeURIComponent(teacherFilter);
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
    </script>
</x-app-layout>
