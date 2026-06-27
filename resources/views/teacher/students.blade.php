@extends('layouts.teacher')

@section('title', 'My Students')

@section('content')
<div class="max-w-7xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Students</h1>
        <p class="text-sm text-gray-500">
            {{ $classList ? $classList->class_name : 'No class assigned' }}
        </p>
    </div>

    <div class="flex justify-end mb-3">
        <select onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.assign(u.toString());})(this.value)"
                class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#2f5597]/30">
            <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10 / page</option>
            <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20 / page</option>
            <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50 / page</option>
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
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
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <x-student-avatar :student="$student" size="32" />
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
        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
