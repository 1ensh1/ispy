@extends('layouts.teacher')
@section('title', 'Milestones')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Student Milestones</h2>
        <p class="text-sm text-gray-500 mt-1">Track CVC mastery and multi-syllable progression</p>
    </div>

    {{-- Student cards --}}
    @if($students->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <i data-lucide="award" class="w-10 h-10 text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-400 font-medium">No students enrolled yet.</p>
            <p class="text-xs text-gray-300 mt-1">Student milestones will appear here once students join your class.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($students as $student)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">

                    {{-- Top row: name + badges --}}
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem;">
                        <span style="font-weight:600; color:#111827; font-size:0.875rem; line-height:1.4;">{{ $student->name }}</span>

                        @if($student->cvc_champion || $student->fast_learner || $student->consistent)
                            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; justify-content:flex-end; flex-shrink:0;">
                                @if($student->cvc_champion)
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.125rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:500; background:#ccfbf1; color:#0f766e; border:1px solid #99f6e4;">
                                        <i data-lucide="star" style="width:0.75rem; height:0.75rem;"></i>
                                        CVC Champion
                                    </span>
                                @endif
                                @if($student->fast_learner)
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.125rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:500; background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe;">
                                        <i data-lucide="zap" style="width:0.75rem; height:0.75rem;"></i>
                                        Fast Learner
                                    </span>
                                @endif
                                @if($student->consistent)
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.125rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:500; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;">
                                        <i data-lucide="check-circle" style="width:0.75rem; height:0.75rem;"></i>
                                        Consistent
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Progress bars: side by side --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-top:1rem;">

                        {{-- CVC Mastery --}}
                        <div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.375rem;">
                                <span style="font-size:0.75rem; color:#6b7280;">CVC Mastery</span>
                                <span style="font-size:0.75rem; font-weight:600; color:#374151;">{{ $student->cvc_mastery }}%</span>
                            </div>
                            <div style="width:100%; background:#f3f4f6; border-radius:9999px; height:8px; overflow:hidden;">
                                <div style="height:8px; border-radius:9999px; background:#1e3a5f; width:{{ $student->cvc_mastery }}%; transition:width 0.4s ease;"></div>
                            </div>
                        </div>

                        {{-- Multi-syllable Mastery --}}
                        <div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.375rem;">
                                <span style="font-size:0.75rem; color:#6b7280;">Multi-syllable</span>
                                <span style="font-size:0.75rem; font-weight:600; color:#374151;">{{ $student->multi_mastery }}%</span>
                            </div>
                            <div style="width:100%; background:#f3f4f6; border-radius:9999px; height:8px; overflow:hidden;">
                                <div style="height:8px; border-radius:9999px; background:#14b8a6; width:{{ $student->multi_mastery }}%; transition:width 0.4s ease;"></div>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
