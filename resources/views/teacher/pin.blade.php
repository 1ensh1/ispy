@extends('layouts.teacher')

@section('title', 'Classroom PIN')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Classroom PIN</h1>
        <p class="text-sm text-gray-500">
            {{ $classList ? $classList->class_name : 'No class assigned' }}
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        @if($classList)

            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="key-round" class="w-8 h-8 text-indigo-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-700">{{ $classList->class_name }}</h2>
            </div>

            @if($classList->unified_classroom_pin)

                <div class="bg-gray-50 border border-gray-200 rounded-xl px-8 py-8 text-center mb-6">
                    <p class="text-xs text-gray-500 uppercase tracking-widest font-semibold mb-3">Classroom PIN</p>
                    <p class="font-mono text-5xl font-bold text-[#2f5597] tracking-[0.3em] select-all">
                        {{ $classList->unified_classroom_pin }}
                    </p>
                </div>

                <p class="text-sm text-gray-500 text-center mb-6">
                    Share this PIN with your students for classroom login on the iSpy World app.
                </p>

            @else

                <div class="bg-amber-50 border border-amber-200 rounded-xl px-8 py-8 text-center mb-6">
                    <i data-lucide="alert-triangle" class="w-10 h-10 text-amber-500 mx-auto mb-3"></i>
                    <p class="text-sm font-semibold text-amber-800">No PIN generated yet.</p>
                    <p class="text-sm text-amber-600 mt-1">Please contact the administrator.</p>
                </div>

            @endif

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                    <p class="text-sm text-blue-700">
                        This PIN is generated and managed by the administrator. Students use this PIN to log in at
                        school. For home access, parents use their individual parent passwords.
                    </p>
                </div>
            </div>

        @else

            <div class="py-10 text-center">
                <i data-lucide="key-round" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                <p class="text-gray-500 font-medium">No class has been assigned to your account yet.</p>
                <p class="text-sm text-gray-400 mt-1">Please contact the administrator.</p>
            </div>

        @endif

    </div>

</div>
@endsection
