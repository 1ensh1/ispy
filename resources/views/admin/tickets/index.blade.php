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
    @endphp

    <div class="max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Support Tickets</h1>
                <p class="text-sm text-gray-500">Manage and resolve support tickets submitted by teachers</p>
            </div>
            <button onclick="openCreateModal()"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i data-lucide="plus" style="width:16px;height:16px;"></i>
                Create Ticket
            </button>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 mb-5 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if($errors->any())
            <div class="flex items-start gap-3 p-4 mb-5 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <ul class="text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="mb-5">
            <div style="display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
                    <option value="">All Statuses</option>
                    @foreach(['Open', 'In Progress', 'Resolved', 'Closed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <select name="priority" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
                    <option value="">All Priorities</option>
                    @foreach(['Low', 'Medium', 'High'] as $p)
                        <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <select name="role" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
                    <option value="">All Roles</option>
                    @foreach(['Admin', 'Teacher'] as $r)
                        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                <a href="{{ route('admin.tickets.index') }}"
                   class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    Reset Filters
                </a>
                <select name="per_page" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
                    <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20 / page</option>
                    <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50 / page</option>
                </select>
            </div>
        </form>

        {{-- Ticket Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">ID</th>
                            <th class="px-4 py-3 font-medium">Title</th>
                            <th class="px-4 py-3 font-medium">Submitted By</th>
                            <th class="px-4 py-3 font-medium">Priority</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Date Submitted</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tickets as $ticket)
                            @php
                                $teacherName = $ticket->teacher_display_name ?? $ticket->createdByUser?->name ?? '—';
                                $isAdminEntry = $ticket->created_by_role === 'Admin';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-400 font-mono text-xs">#{{ $ticket->id }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900" style="max-width: 260px;">
                                    <span class="block truncate" title="{{ $ticket->title }}">{{ $ticket->title }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <span class="text-gray-700">
                                            @if($isAdminEntry)
                                                On behalf of {{ $teacherName }}
                                            @else
                                                {{ $teacherName }}
                                            @endif
                                        </span>
                                        @if($isAdminEntry)
                                            <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Admin</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">Teacher</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityBadge($ticket->priority) }}">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge($ticket->status) }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $ticket->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="openUpdateModal(this)"
                                            data-url="{{ route('admin.tickets.update', $ticket->id) }}"
                                            data-status="{{ $ticket->status }}"
                                            data-notes="{{ $ticket->resolution_notes ?? '' }}"
                                            class="px-3 py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-16 text-center">
                                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
                                    <p class="text-sm text-gray-400">No tickets found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="px-4 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- ── Create Ticket Modal ── --}}
    <div id="create-modal"
         class="fixed inset-0 flex items-center justify-center z-50 hidden"
         style="background: rgba(0,0,0,0.5);">
        <div class="bg-white rounded-xl shadow-xl p-6" style="width: 520px; max-width: 95vw;">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">Create Ticket on Behalf of Teacher</h2>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.tickets.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teacher <span class="text-red-500">*</span></label>
                    <select name="created_by_user_id" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">Select a teacher…</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->user_id }}" {{ old('created_by_user_id') == $t->user_id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" maxlength="255" required
                           value="{{ old('title') }}"
                           placeholder="Brief summary of the issue"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="4" required
                              placeholder="Describe the issue in detail"
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                    <select name="priority" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach(['Low', 'Medium', 'High'] as $p)
                            <option value="{{ $p }}" {{ old('priority', 'Medium') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 4px;">
                    <button type="button" onclick="closeCreateModal()"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Create Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Update Ticket Modal ── --}}
    <div id="update-modal"
         class="fixed inset-0 flex items-center justify-center z-50 hidden"
         style="background: rgba(0,0,0,0.5);">
        <div class="bg-white rounded-xl shadow-xl p-6" style="width: 480px; max-width: 95vw;">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">Update Ticket</h2>
                <button onclick="closeUpdateModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="update-status"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Resolution Notes</label>
                    <textarea id="update-notes" rows="4"
                              placeholder="Optional notes about the resolution"
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                </div>
                <div id="update-error"
                     class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 4px;">
                    <button type="button" onclick="closeUpdateModal()"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="update-submit-btn" onclick="submitUpdate()"
                            class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        let currentUpdateUrl = '';

        window.openCreateModal = function () {
            document.getElementById('create-modal').classList.remove('hidden');
        };
        window.closeCreateModal = function () {
            document.getElementById('create-modal').classList.add('hidden');
        };

        window.openUpdateModal = function (btn) {
            currentUpdateUrl = btn.dataset.url;
            document.getElementById('update-status').value = btn.dataset.status;
            document.getElementById('update-notes').value  = btn.dataset.notes;
            document.getElementById('update-error').classList.add('hidden');
            document.getElementById('update-modal').classList.remove('hidden');
        };
        window.closeUpdateModal = function () {
            document.getElementById('update-modal').classList.add('hidden');
        };

        window.submitUpdate = function () {
            const status  = document.getElementById('update-status').value;
            const notes   = document.getElementById('update-notes').value;
            const errEl   = document.getElementById('update-error');
            const btn     = document.getElementById('update-submit-btn');

            btn.disabled    = true;
            btn.textContent = 'Saving…';
            errEl.classList.add('hidden');

            fetch(currentUpdateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ status: status, resolution_notes: notes }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    errEl.textContent = 'Failed to update ticket. Please try again.';
                    errEl.classList.remove('hidden');
                    btn.disabled    = false;
                    btn.textContent = 'Save Changes';
                }
            })
            .catch(function () {
                errEl.textContent = 'Network error. Please try again.';
                errEl.classList.remove('hidden');
                btn.disabled    = false;
                btn.textContent = 'Save Changes';
            });
        };

        document.getElementById('create-modal').addEventListener('click', function (e) {
            if (e.target === this) window.closeCreateModal();
        });
        document.getElementById('update-modal').addEventListener('click', function (e) {
            if (e.target === this) window.closeUpdateModal();
        });
    })();
    </script>

</x-app-layout>
