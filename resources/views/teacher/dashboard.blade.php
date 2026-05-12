@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Teacher Dashboard</h1>
        <p class="text-sm text-gray-500">
            {{ $classList ? $classList->class_name : 'No class assigned yet' }}
        </p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-sm font-medium text-gray-500">My Students</h3>
                <i data-lucide="users" class="w-5 h-5 text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold">{{ $studentCount }}</div>
            <p class="text-xs text-gray-500 mt-1">Enrolled in class</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-sm font-medium text-gray-500">Classroom PIN</h3>
                <i data-lucide="key-round" class="w-5 h-5 text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold font-mono tracking-widest">
                {{ ($classList && $classList->unified_classroom_pin) ? $classList->unified_classroom_pin : '—' }}
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ ($classList && $classList->unified_classroom_pin) ? 'Active PIN' : 'Not set' }}
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-sm font-medium text-gray-500">Pending Messages</h3>
                <i data-lucide="message-square" class="w-5 h-5 text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold">{{ $pendingMessages }}</div>
            <p class="text-xs text-gray-500 mt-1">Unread from parents</p>
        </div>

    </div>

    {{-- Students Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">My Class</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Student</th>
                        <th class="px-6 py-4 font-medium">Parent</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($students as $student)
                    @php
                        $iconEmoji = [
                            'cat' => '🐱', 'dog' => '🐶', 'bear' => '🐻', 'rabbit' => '🐰',
                            'fox' => '🦊', 'frog' => '🐸', 'penguin' => '🐧', 'lion' => '🦁',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($student->profile_icon && isset($iconEmoji[$student->profile_icon]))
                                    <div class="w-8 h-8 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0 text-lg leading-none">
                                        {{ $iconEmoji[$student->profile_icon] }}
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0">
                                        <span class="text-[#2f5597] text-xs font-bold">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <span class="font-medium text-gray-900">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($student->parentUser)
                                <span class="text-gray-700">{{ $student->parentUser->name }}</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($student->parentUser)
                                <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-teal-500 text-white tracking-wide">Complete</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-red-100 text-red-600 border border-red-200 tracking-wide">Incomplete</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                            <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                            <p class="text-sm">No students assigned to your class yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
