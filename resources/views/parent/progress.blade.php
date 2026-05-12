@extends('layouts.parent')
@section('title', 'Progress Review')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Progress Review</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $student ? "View {$student->name}'s latest learning snapshots and activity" : 'No student linked yet.' }}
        </p>
    </div>

    @if($student)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Daily Snapshots --}}
        <div>
            <h2 class="font-semibold text-gray-800 mb-3">Daily Snapshots</h2>
            <div class="space-y-3">
                @forelse($snapshots as $snap)
                    @php
                        $color = $snap->pct >= 75 ? 'text-green-600' : ($snap->pct >= 50 ? 'text-amber-600' : 'text-red-500');
                    @endphp
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-semibold text-gray-800 text-sm">
                                {{ \Carbon\Carbon::parse($snap->date)->format('M d, Y') }}
                            </span>
                            <span class="font-bold text-sm {{ $color }}">{{ $snap->pct }}%</span>
                        </div>
                        <div class="flex items-center gap-5 text-sm text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="book-open" class="w-4 h-4 text-teal-500"></i>
                                {{ $snap->words }} words
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i>
                                {{ $snap->scans }} scans
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="mic" class="w-4 h-4 text-purple-500"></i>
                                {{ $snap->attempts }} attempts
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center text-gray-400 text-sm">
                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                        No learning activity in the last 14 days.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity Timeline --}}
        <div>
            <h2 class="font-semibold text-gray-800 mb-3">Recent Activity Timeline</h2>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                @forelse($recentActivity as $item)
                    <div class="flex gap-3 {{ !$loop->last ? 'pb-4 mb-4 border-b border-gray-100' : '' }}">
                        <div class="mt-0.5 w-7 h-7 rounded-full bg-teal-50 flex items-center justify-center shrink-0">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-teal-500"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-800">
                                <span class="font-medium">{{ $item->mode }}</span>
                                — <span class="font-semibold">'{{ $item->filipino_label }}'</span>
                                <span class="text-gray-500">({{ $item->english_label }})</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($item->attempted_at)->format('h:i A · M d') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No recent activity.</p>
                @endforelse
            </div>
        </div>
    </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-gray-400">
            <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
            <p>No student linked to your account yet.</p>
        </div>
    @endif

</div>
@endsection
