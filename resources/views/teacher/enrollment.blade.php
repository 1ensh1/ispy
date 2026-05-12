@extends('layouts.teacher')

@section('title', 'Enrollment')

@section('content')
<div class="max-w-7xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $classList ? $classList->class_name : 'Enrollment' }}
        </h1>
        <p class="text-sm text-gray-500">Student enrollment and parent contact information</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Student</th>
                        <th class="px-6 py-4 font-medium">Parent</th>
                        <th class="px-6 py-4 font-medium">Contact Number</th>
                        <th class="px-6 py-4 font-medium">Parent Password</th>
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
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">No Parent</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($student->parentUser && $student->parentUser->contact_number)
                                <span class="text-gray-700">{{ $student->parentUser->contact_number }}</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($student->parent_password)
                                <span class="font-mono text-sm text-gray-800 tracking-widest select-all">{{ $student->parent_password }}</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                            <i data-lucide="clipboard-list" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                            <p class="text-sm">No students enrolled in your class yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
