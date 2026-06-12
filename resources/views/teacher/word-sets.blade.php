@extends('layouts.teacher')
@section('title', 'Word Sets')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Active Word Sets</h2>
        <p class="text-sm text-gray-500 mt-1">Select and manage vocabulary lists for your students</p>
    </div>

    {{-- Category cards --}}
    @if($wordSets->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <i data-lucide="book-open" class="w-10 h-10 text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-400 font-medium">No vocabulary sets available.</p>
            <p class="text-xs text-gray-300 mt-1">Ask your administrator to add vocabulary words first.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($wordSets as $set)
                <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #f3f4f6; box-shadow:0 1px 3px rgba(0,0,0,0.06); padding:1.25rem;">

                    {{-- Top row: category info + toggle --}}
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">

                        {{-- Left: name + badge --}}
                        <div>
                            <p style="font-weight:700; font-size:1rem; color:#111827; margin:0 0 0.375rem 0;">{{ $set->category }}</p>
                            @if($set->badge === 'CVC')
                                <span style="display:inline-block; background:#1e3a5f; color:#ffffff; font-size:0.7rem; font-weight:600; padding:0.2rem 0.65rem; border-radius:9999px; letter-spacing:0.03em;">CVC</span>
                            @else
                                <span style="display:inline-block; background:#0d9488; color:#ffffff; font-size:0.7rem; font-weight:600; padding:0.2rem 0.65rem; border-radius:9999px; letter-spacing:0.03em;">Multi-syllabic</span>
                            @endif
                        </div>

                        {{-- Right: label + toggle button --}}
                        <div style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
                            <span class="ws-toggle-label" style="font-size:0.8125rem; color:{{ $set->is_active ? '#374151' : '#9ca3af' }};">
                                {{ $set->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <form method="POST" action="{{ route('teacher.word-sets.toggle') }}" style="margin:0;">
                                @csrf
                                <input type="hidden" name="category"  value="{{ $set->category }}">
                                <input type="hidden" name="is_active" value="{{ $set->is_active ? '0' : '1' }}">
                                <label style="display:inline-flex; align-items:center; cursor:pointer;">
                                    <input type="checkbox" class="ws-toggle-cb" style="display:none;"
                                           {{ $set->is_active ? 'checked' : '' }}>
                                    {{-- Track --}}
                                    <span class="ws-track"
                                          style="position:relative; display:inline-block; width:44px; height:24px; border-radius:9999px; transition:background 0.2s;
                                                 background:{{ $set->is_active ? '#2563eb' : '#d1d5db' }};">
                                        {{-- Knob --}}
                                        <span class="ws-knob"
                                              style="position:absolute; top:2px; width:20px; height:20px; border-radius:50%; background:#ffffff;
                                                     box-shadow:0 1px 3px rgba(0,0,0,0.25); transition:left 0.2s;
                                                     left:{{ $set->is_active ? '22px' : '2px' }};"></span>
                                    </span>
                                </label>
                            </form>
                        </div>
                    </div>

                    {{-- Word chips --}}
                    <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.875rem;">
                        @foreach($set->words as $word)
                            <span style="display:inline-block; padding:0.25rem 0.75rem; border-radius:9999px; border:1px solid #e5e7eb; font-size:0.8125rem; color:#374151; background:#ffffff;">
                                {{ $word }}
                            </span>
                        @endforeach
                        @if($set->extra > 0)
                            <span style="display:inline-block; padding:0.25rem 0.75rem; border-radius:9999px; border:1px solid #e5e7eb; font-size:0.8125rem; color:#9ca3af; background:#f9fafb;">
                                +{{ $set->extra }} more
                            </span>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ws-toggle-cb').forEach(function (cb) {
        var label = cb.closest('div[style]').querySelector('.ws-toggle-label');
        var track = cb.closest('label').querySelector('.ws-track');
        var knob  = cb.closest('label').querySelector('.ws-knob');

        function applyState(checked) {
            track.style.background = checked ? '#2563eb' : '#d1d5db';
            knob.style.left        = checked ? '22px'    : '2px';
            if (label) {
                label.textContent  = checked ? 'Active'   : 'Inactive';
                label.style.color  = checked ? '#374151'  : '#9ca3af';
            }
            // Sync the hidden is_active field so the correct value is submitted
            var hiddenInput = cb.closest('form').querySelector('input[name="is_active"]');
            if (hiddenInput) hiddenInput.value = checked ? '1' : '0';
        }

        cb.addEventListener('change', function () {
            applyState(cb.checked);
            cb.closest('form').submit();
        });
    });
});
</script>
@endpush
