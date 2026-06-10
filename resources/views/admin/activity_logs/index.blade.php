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
                <option value="Login"         {{ request('action') === 'Login'         ? 'selected' : '' }}>Login</option>
                <option value="Create"        {{ request('action') === 'Create'        ? 'selected' : '' }}>Create</option>
                <option value="Update"        {{ request('action') === 'Update'        ? 'selected' : '' }}>Update</option>
                <option value="Archive"       {{ request('action') === 'Archive'       ? 'selected' : '' }}>Archive</option>
                <option value="Restore"       {{ request('action') === 'Restore'       ? 'selected' : '' }}>Restore</option>
                <option value="Approve"       {{ request('action') === 'Approve'       ? 'selected' : '' }}>Approve</option>
                <option value="Reject"        {{ request('action') === 'Reject'        ? 'selected' : '' }}>Reject</option>
                <option value="Delete"        {{ request('action') === 'Delete'        ? 'selected' : '' }}>Delete</option>
                <option value="Assign"        {{ request('action') === 'Assign'        ? 'selected' : '' }}>Assign</option>
                <option value="Remove"        {{ request('action') === 'Remove'        ? 'selected' : '' }}>Remove</option>
                <option value="Activate"      {{ request('action') === 'Activate'      ? 'selected' : '' }}>Activate</option>
                <option value="Export"        {{ request('action') === 'Export'        ? 'selected' : '' }}>Export</option>
                <option value="Ticket"        {{ request('action') === 'Ticket'        ? 'selected' : '' }}>Ticket</option>
                <option value="CMS Edit"      {{ request('action') === 'CMS Edit'      ? 'selected' : '' }}>CMS Edit</option>
                <option value="Scan Attempt"  {{ request('action') === 'Scan Attempt'  ? 'selected' : '' }}>Scan Attempt</option>
                <option value="Scan Success"  {{ request('action') === 'Scan Success'  ? 'selected' : '' }}>Scan Success</option>
                <option value="Scan Fail"        {{ request('action') === 'Scan Fail'        ? 'selected' : '' }}>Scan Fail</option>
                <option value="Scan_unmatched" {{ request('action') === 'Scan_unmatched' ? 'selected' : '' }}>Scan Unmatched</option>
                <option value="Matching"       {{ request('action') === 'Matching'       ? 'selected' : '' }}>Matching</option>
                <option value="Sentence"      {{ request('action') === 'Sentence'      ? 'selected' : '' }}>Sentence</option>
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

        <a href="{{ route('admin.activity-logs.export', array_filter(request()->only(['role', 'action', 'search']))) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i>
            Export CSV
        </a>

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
                            $action = $log->action ?? '';
                            $al = strtolower($action);
                            if (str_contains($al, 'create and assign')) {
                                $badgeClass = 'bg-indigo-100 text-indigo-700';
                            } elseif (str_contains($al, 'unassign')) {
                                $badgeClass = 'bg-slate-100 text-slate-600';
                            } elseif (str_contains($al, 'assign substitute') || str_contains($al, 'assign class') || $al === 'assign') {
                                $badgeClass = 'bg-blue-100 text-blue-700';
                            } elseif (str_contains($al, 'archive')) {
                                $badgeClass = 'bg-orange-100 text-orange-700';
                            } elseif (str_contains($al, 'restore')) {
                                $badgeClass = 'bg-teal-100 text-teal-700';
                            } elseif (str_contains($al, 'remove')) {
                                $badgeClass = 'bg-rose-100 text-rose-700';
                            } elseif (str_contains($al, 'delete')) {
                                $badgeClass = 'bg-red-100 text-red-700';
                            } elseif (str_contains($al, 'create')) {
                                $badgeClass = 'bg-green-100 text-green-700';
                            } elseif (str_contains($al, 'update')) {
                                $badgeClass = 'bg-yellow-100 text-yellow-700';
                            } elseif ($al === 'login') {
                                $badgeClass = 'bg-sky-100 text-sky-700';
                            } elseif ($al === 'activate') {
                                $badgeClass = 'bg-purple-100 text-purple-700';
                            } elseif ($al === 'approve') {
                                $badgeClass = 'bg-emerald-100 text-emerald-700';
                            } elseif ($al === 'reject') {
                                $badgeClass = 'bg-pink-100 text-pink-700';
                            } elseif ($al === 'export') {
                                $badgeClass = 'bg-cyan-100 text-cyan-700';
                            } elseif ($al === 'ticket') {
                                $badgeClass = 'bg-violet-100 text-violet-700';
                            } elseif ($al === 'cms edit') {
                                $badgeClass = 'bg-amber-100 text-amber-700';
                            } elseif ($al === 'cancel') {
                                $badgeClass = 'bg-neutral-100 text-neutral-600';
                            } elseif (str_contains($al, 'scan')) {
                                $badgeClass = 'bg-lime-100 text-lime-700';
                            } elseif ($al === 'matching') {
                                $badgeClass = 'bg-fuchsia-100 text-fuchsia-700';
                            } elseif ($al === 'sentence') {
                                $badgeClass = 'bg-indigo-50 text-indigo-500';
                            } else {
                                $badgeClass = 'bg-gray-100 text-gray-600';
                            }

                            $isMobile = in_array($al, ['matching', 'sentence', 'scan attempt', 'scan success', 'scan fail']);

                            $rawDesc = $log->description ?? '';
                            $decoded = json_decode($rawDesc);
                            if ($decoded !== null && (is_object($decoded) || is_array($decoded))) {
                                if ($al === 'matching') {
                                    $correct = isset($decoded->is_correct) ? ($decoded->is_correct ? 'Correct' : 'Incorrect') : '?';
                                    $formattedDesc = 'Student ' . ($decoded->student_id ?? '?') . ' attempted Matching — selected attribute \'' . ($decoded->selected_attribute ?? '?') . '\' — ' . $correct;
                                } elseif ($al === 'sentence') {
                                    $correct = isset($decoded->is_correct) ? ($decoded->is_correct ? 'Correct' : 'Incorrect') : '?';
                                    $formattedDesc = 'Student ' . ($decoded->student_id ?? '?') . ' attempted Sentence — selected word \'' . ($decoded->selected_word ?? '?') . '\' (Word ID: ' . ($decoded->learning_word_id ?? '?') . ') — ' . $correct;
                                } elseif (str_contains($al, 'scan')) {
                                    $formattedDesc = 'Student ' . ($decoded->student_id ?? '?') . ' performed ' . $action . ' on Word ID ' . ($decoded->learning_word_id ?? '?');
                                } else {
                                    $pairs = [];
                                    foreach ((array) $decoded as $k => $v) {
                                        $pairs[] = ucfirst(str_replace('_', ' ', $k)) . ': ' . (is_bool($v) ? ($v ? 'true' : 'false') : $v);
                                    }
                                    $formattedDesc = implode(' | ', $pairs);
                                }
                            } else {
                                $formattedDesc = $rawDesc;
                            }
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
                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                    @if($isMobile)
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200">Mobile</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $formattedDesc }}</td>
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
