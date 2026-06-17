<x-app-layout>
@php
    $priorityBadge = fn($p) => match($p) {
        'Low'  => 'bg-gray-100 text-gray-600',
        'High' => 'bg-red-100 text-red-700',
        default => 'bg-yellow-100 text-yellow-700',
    };
    $statusBadge = fn($s) => match($s) {
        'Open'        => 'bg-blue-100 text-blue-700',
        'In Progress' => 'bg-amber-100 text-amber-700',
        'Resolved'    => 'bg-green-100 text-green-700',
        default       => 'bg-gray-100 text-gray-600',
    };
    $activeStatus   = request('status');
    $activePriority = request('priority');
    $activeRole     = request('role');
    $hasFilters     = $activeStatus || $activePriority || $activeRole;
@endphp

<div class="p-6 max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <a href="{{ route('admin.tickets.index', array_filter(request()->only(['status', 'priority', 'role']))) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 no-print" style="margin-bottom:0.75rem; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Tickets Report</h1>
            <p class="text-sm text-gray-500 mt-0.5">Support ticket summary and full filtered listing</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap no-print">
            <a href="{{ route('admin.tickets.report.export', array_filter(request()->only(['status', 'priority', 'role']))) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export CSV
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

    {{-- Active filters summary --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Active Filters</h3>
        <div class="flex flex-wrap gap-2">
            <span style="display:inline-block; padding:0.2rem 0.7rem; border-radius:9999px; background:#eef2ff; color:#4338ca; font-size:0.75rem; font-weight:600;">
                Status: {{ $activeStatus ?: 'All' }}
            </span>
            <span style="display:inline-block; padding:0.2rem 0.7rem; border-radius:9999px; background:#eef2ff; color:#4338ca; font-size:0.75rem; font-weight:600;">
                Priority: {{ $activePriority ?: 'All' }}
            </span>
            <span style="display:inline-block; padding:0.2rem 0.7rem; border-radius:9999px; background:#eef2ff; color:#4338ca; font-size:0.75rem; font-weight:600;">
                Role: {{ $activeRole ?: 'All' }}
            </span>
        </div>
    </div>

    {{-- Summary --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-baseline gap-3 mb-4">
            <p class="text-3xl font-bold text-gray-900">{{ $summary['total'] }}</p>
            <p class="text-sm text-gray-500">Total Tickets</p>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1.5rem;">

            {{-- By status --}}
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">By Status</p>
                <div class="flex flex-wrap gap-2">
                    @forelse($summary['by_status'] as $status => $count)
                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $statusBadge($status) }}">
                            {{ $status }}: {{ $count }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-400">No data</span>
                    @endforelse
                </div>
            </div>

            {{-- By priority --}}
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">By Priority</p>
                <div class="flex flex-wrap gap-2">
                    @forelse($summary['by_priority'] as $priority => $count)
                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $priorityBadge($priority) }}">
                            {{ $priority }}: {{ $count }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-400">No data</span>
                    @endforelse
                </div>
            </div>

            {{-- By role --}}
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">By Role</p>
                <div class="flex flex-wrap gap-2">
                    @forelse($summary['by_role'] as $role => $count)
                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-600">
                            {{ $role }}: {{ $count }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-400">No data</span>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Full ticket list --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">ID</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Subject / Description</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Priority</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Role</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Submitted By</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Created At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 text-gray-500 font-mono">#{{ $ticket->id }}</td>
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-gray-900">{{ $ticket->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ \Illuminate\Support\Str::limit($ticket->description, 80) }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $statusBadge($ticket->status) }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $priorityBadge($ticket->priority) }}">
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $ticket->created_by_role }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $ticket->teacher_display_name ?? optional($ticket->createdByUser)->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">{{ optional($ticket->created_at)->format('M d, Y g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <i data-lucide="ticket" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <p class="text-sm">No tickets match the current filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
