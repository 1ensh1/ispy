@extends('layouts.parent')
@section('title', 'Proficiency Levels')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Proficiency Levels</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $student ? "Monitor {$student->name}'s progress across learning areas" : 'No student linked yet.' }}
        </p>
    </div>

    @if($student)

    {{-- Overall card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center gap-6">
            <div class="text-center shrink-0">
                <span class="text-5xl font-bold text-teal-600">{{ $overallPercent }}%</span>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider">Overall</p>
            </div>
            <div class="flex-1">
                <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden">
                    <div class="h-4 rounded-full transition-all duration-500"
                         style="width:{{ $overallPercent }}%; background:#14b8a6;"></div>
                </div>
            </div>
            <div class="shrink-0">
                @if($overallLevel === 'Proficient')
                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200">Proficient</span>
                @elseif($overallLevel === 'Developing')
                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold bg-teal-100 text-teal-800 border border-teal-200">Developing</span>
                @else
                    <span class="px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-600 border border-gray-200">Beginner</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Category cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($categories as $cat)
            @php
                $barColor = $cat['level'] === 'Proficient' ? '#22c55e' : ($cat['level'] === 'Developing' ? '#14b8a6' : '#f59e0b');
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-800">{{ $cat['label'] }}</span>
                    @if($cat['level'] === 'Proficient')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">Proficient</span>
                    @elseif($cat['level'] === 'Developing')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 border border-teal-200">Developing</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">Beginner</span>
                    @endif
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden mb-2">
                    <div class="h-2.5 rounded-full transition-all duration-500"
                         style="width:{{ $cat['percent'] }}%; background:{{ $barColor }};"></div>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-gray-700">{{ $cat['percent'] }}%</span>
                </div>
            </div>
        @endforeach
    </div>

    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-gray-400">
            <i data-lucide="graduation-cap" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
            <p>No student linked to your account yet.</p>
        </div>
    @endif

</div>
@endsection
