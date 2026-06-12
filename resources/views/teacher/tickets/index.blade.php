@extends('layouts.teacher')
@section('title', 'My Support Tickets')

@section('content')
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

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Support Tickets</h1>
            <p class="text-sm text-gray-500 mt-0.5">Submit and track your support requests</p>
        </div>
        <button type="button" onclick="openSubmitModal()"
                class="shrink-0 flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Submit New Ticket
        </button>
    </div>

    {{-- Status Guide --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ticket Status Guide</p>
        <div style="display:flex; gap:12px;">
            <div class="flex-1 bg-white rounded-lg p-3 border border-gray-100" style="border-left:4px solid #3b82f6;">
                <p class="text-sm font-semibold text-blue-700 mb-1">Open</p>
                <p class="text-xs text-gray-500 leading-relaxed">Your ticket has been received and is awaiting action.</p>
            </div>
            <div class="flex-1 bg-white rounded-lg p-3 border border-gray-100" style="border-left:4px solid #f59e0b;">
                <p class="text-sm font-semibold text-amber-700 mb-1">In Progress</p>
                <p class="text-xs text-gray-500 leading-relaxed">An admin is currently working on your ticket.</p>
            </div>
            <div class="flex-1 bg-white rounded-lg p-3 border border-gray-100" style="border-left:4px solid #10b981;">
                <p class="text-sm font-semibold text-green-700 mb-1">Resolved</p>
                <p class="text-xs text-gray-500 leading-relaxed">The admin has addressed your ticket.</p>
            </div>
            <div class="flex-1 bg-white rounded-lg p-3 border border-gray-100" style="border-left:4px solid #9ca3af;">
                <p class="text-sm font-semibold text-gray-600 mb-1">Closed</p>
                <p class="text-xs text-gray-500 leading-relaxed">The ticket has been closed.</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('teacher.tickets.index') }}" class="flex items-center gap-3 flex-wrap">
        <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">All Statuses</option>
            @foreach(['Open', 'In Progress', 'Resolved', 'Closed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <select name="priority" onchange="this.form.submit()"
                class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">All Priorities</option>
            @foreach(['Low', 'Medium', 'High'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
        <a href="{{ route('teacher.tickets.index') }}"
           class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            Reset Filters
        </a>
    </form>

    {{-- Ticket Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table style="width:100%;" class="text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide" style="width:30%;">Title</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide" style="width:10%;">Priority</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide" style="width:12%;">Status</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide" style="width:15%;">Date Submitted</th>
                    <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide" style="width:33%;">Admin Response</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4 font-medium text-gray-900 leading-snug" style="width:30%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $ticket->title }}</td>
                        <td class="px-4 py-4" style="width:10%;">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityBadge($ticket->priority) }}">
                                {{ $ticket->priority }}
                            </span>
                        </td>
                        <td class="px-4 py-4" style="width:12%;">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge($ticket->status) }}">
                                {{ $ticket->status }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-gray-500" style="width:15%;">{{ $ticket->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-4 text-gray-600 leading-relaxed" style="width:33%;">
                            @if($ticket->resolution_notes)
                                {{ $ticket->resolution_notes }}
                            @else
                                <span class="text-gray-300">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <i data-lucide="inbox" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400">You have not submitted any support tickets yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ── Submit Ticket Modal ── --}}
<div id="submit-modal"
     class="fixed inset-0 flex items-center justify-center z-50 hidden"
     style="background: rgba(0,0,0,0.5);">
    <div class="bg-white rounded-xl shadow-xl p-6" style="width:520px; max-width:95vw;">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Submit a Support Ticket</h2>
            <button onclick="closeSubmitModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('teacher.tickets.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" maxlength="255" required
                       value="{{ old('title') }}"
                       placeholder="Brief summary of your issue"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required
                          placeholder="Describe your issue in detail"
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
            <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:4px;">
                <button type="button" onclick="closeSubmitModal()"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    window.openSubmitModal = function () {
        document.getElementById('submit-modal').classList.remove('hidden');
    };
    window.closeSubmitModal = function () {
        document.getElementById('submit-modal').classList.add('hidden');
    };

    document.getElementById('submit-modal').addEventListener('click', function (e) {
        if (e.target === this) window.closeSubmitModal();
    });
</script>
@endpush
