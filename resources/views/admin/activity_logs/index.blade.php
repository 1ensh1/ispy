<x-app-layout>
<div class="p-6 max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Activity Logs</h1>
        <p class="text-sm text-gray-500 mt-0.5">Audit trail of all portal actions</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.activity.logs') }}" class="flex flex-wrap items-end gap-3">

        {{-- Role --}}
        <div class="relative">
            <select name="role"
                    class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 outline-none bg-white appearance-none min-w-[140px]">
                <option value="all" {{ request('role', 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
                <option value="Admin"   {{ request('role') === 'Admin'   ? 'selected' : '' }}>Admin</option>
                <option value="Teacher" {{ request('role') === 'Teacher' ? 'selected' : '' }}>Teacher</option>
                <option value="Parent"  {{ request('role') === 'Parent'  ? 'selected' : '' }}>Parent</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </div>

        {{-- Action --}}
        <div class="relative">
            <select name="action"
                    class="pl-3 pr-8 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 outline-none bg-white appearance-none min-w-[140px]">
                <option value="all" {{ request('action', 'all') === 'all' ? 'selected' : '' }}>All Actions</option>
                <option value="login"   {{ request('action') === 'login'   ? 'selected' : '' }}>Login</option>
                <option value="create"  {{ request('action') === 'create'  ? 'selected' : '' }}>Create</option>
                <option value="update"  {{ request('action') === 'update'  ? 'selected' : '' }}>Update</option>
                <option value="delete"  {{ request('action') === 'delete'  ? 'selected' : '' }}>Delete</option>
                <option value="archive" {{ request('action') === 'archive' ? 'selected' : '' }}>Archive</option>
                <option value="restore" {{ request('action') === 'restore' ? 'selected' : '' }}>Restore</option>
                <option value="approve" {{ request('action') === 'approve' ? 'selected' : '' }}>Approve</option>
                <option value="reject"  {{ request('action') === 'reject'  ? 'selected' : '' }}>Reject</option>
                <option value="Assign Class"            {{ request('action') === 'Assign Class'            ? 'selected' : '' }}>Assign Class</option>
                <option value="Unassign Class"          {{ request('action') === 'Unassign Class'          ? 'selected' : '' }}>Unassign Class</option>
                <option value="Create and Assign Class" {{ request('action') === 'Create and Assign Class' ? 'selected' : '' }}>Create and Assign Class</option>
                <option value="Update Class Subject"    {{ request('action') === 'Update Class Subject'    ? 'selected' : '' }}>Update Class Subject</option>
                <option value="Archive Class"           {{ request('action') === 'Archive Class'           ? 'selected' : '' }}>Archive Class</option>
                <option value="Restore Class"           {{ request('action') === 'Restore Class'           ? 'selected' : '' }}>Restore Class</option>
                <option value="Assign Substitute"       {{ request('action') === 'Assign Substitute'       ? 'selected' : '' }}>Assign Substitute</option>
                <option value="Remove Substitute"       {{ request('action') === 'Remove Substitute'       ? 'selected' : '' }}>Remove Substitute</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </div>

        {{-- Search description --}}
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input type="text" name="search"
                   value="{{ request('search') }}"
                   placeholder="Search description…"
                   class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 outline-none w-56">
        </div>

        <button type="submit"
                class="px-4 py-2 bg-[#2f5597] hover:bg-[#264880] text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>

        @if(request('role') || request('action') || request('search'))
            <a href="{{ route('admin.activity.logs') }}"
               class="text-sm text-gray-500 hover:text-gray-700 underline self-center">Clear</a>
        @endif

    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide whitespace-nowrap">Timestamp</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">User</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Role</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Action</th>
                        <th class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wide">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        @php
                            $badgeClass = match($log->action) {
                                'login'   => 'bg-blue-100 text-blue-700',
                                'create'  => 'bg-green-100 text-green-700',
                                'update'  => 'bg-yellow-100 text-yellow-700',
                                'delete'  => 'bg-red-100 text-red-700',
                                'archive' => 'bg-orange-100 text-orange-700',
                                'restore' => 'bg-teal-100 text-teal-700',
                                'approve' => 'bg-emerald-100 text-emerald-700',
                                'reject'  => 'bg-red-100 text-red-700',
                                default   => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ $log->created_at?->format('M j, Y H:i') ?? '—' }}
                            </td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ $log->user->name ?? 'System' }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $log->role }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center">
                                <i data-lucide="clipboard-list" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                                <p class="text-sm text-gray-400">No activity logs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="flex justify-end">
            {{ $logs->links() }}
        </div>
    @endif

</div>

<script>lucide.createIcons();</script>
</x-app-layout>
