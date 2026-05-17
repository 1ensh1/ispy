@extends('layouts.teacher')
@section('title', 'Annotations')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page header --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
        <div>
            <h2 style="font-size:1.5rem; font-weight:700; color:#111827; margin:0 0 0.25rem 0;">Student Annotations</h2>
            <p style="font-size:0.875rem; color:#6b7280; margin:0;">Personal notes and observations about individual students</p>
        </div>
        <button onclick="openAnnotationModal()"
                style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem;
                       background:#2563eb; color:#ffffff; font-size:0.875rem; font-weight:600;
                       border:none; border-radius:0.5rem; cursor:pointer; white-space:nowrap; flex-shrink:0;">
            <i data-lucide="plus" style="width:16px; height:16px;"></i>
            Add Note
        </button>
    </div>

    {{-- Annotation list --}}
    @if($annotations->isEmpty())
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                    padding:5rem 1rem; text-align:center;">
            <i data-lucide="pencil-line" style="width:40px; height:40px; color:#d1d5db; margin-bottom:0.75rem;"></i>
            <p style="font-size:0.875rem; color:#9ca3af; font-weight:500; margin:0 0 0.25rem 0;">No annotations yet.</p>
            <p style="font-size:0.75rem; color:#d1d5db; margin:0;">Click "Add Note" to record your first observation.</p>
        </div>
    @else
        <div style="display:flex; flex-direction:column; gap:0.875rem;">
            @foreach($annotations as $ann)
                @php
                    $initials = collect(explode(' ', $ann->student->name))
                        ->map(fn($p) => strtoupper(substr($p, 0, 1)))
                        ->take(2)
                        ->implode('');
                    $colors = ['#4f46e5','#0891b2','#0d9488','#7c3aed','#db2777','#d97706','#16a34a'];
                    $avatarBg = $colors[crc32($ann->student->name) % count($colors)];
                @endphp
                <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #f3f4f6;
                             box-shadow:0 1px 3px rgba(0,0,0,0.06); padding:1.25rem;">
                    <div style="display:flex; align-items:flex-start; gap:0.875rem;">

                        {{-- Avatar --}}
                        <div style="width:40px; height:40px; border-radius:50%; background:{{ $avatarBg }};
                                    display:flex; align-items:center; justify-content:center;
                                    color:#ffffff; font-size:0.8125rem; font-weight:700; flex-shrink:0;">
                            {{ $initials }}
                        </div>

                        {{-- Content --}}
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem; margin-bottom:0.375rem;">
                                <p style="font-weight:700; font-size:0.9375rem; color:#111827; margin:0; truncate;">
                                    {{ $ann->student->name }}
                                </p>
                                <span style="font-size:0.75rem; color:#9ca3af; white-space:nowrap; flex-shrink:0;">
                                    {{ \Carbon\Carbon::parse($ann->annotation_date)->format('M j, Y') }}
                                </span>
                            </div>

                            <p style="font-size:0.875rem; color:#374151; margin:0 0 0.625rem 0; line-height:1.5;">
                                {{ $ann->note }}
                            </p>

                            @if(!empty($ann->tags))
                                <div style="display:flex; flex-wrap:wrap; gap:0.375rem;">
                                    @foreach($ann->tags as $tag)
                                        <span style="display:inline-block; padding:0.15rem 0.6rem; border-radius:9999px;
                                                     background:#f3f4f6; border:1px solid #e5e7eb;
                                                     font-size:0.75rem; color:#6b7280;">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Add Annotation Modal --}}
<div id="annotation-modal"
     style="display:none; position:fixed; inset:0; z-index:100; background:rgba(0,0,0,0.4);
            align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#ffffff; border-radius:0.875rem; box-shadow:0 20px 60px rgba(0,0,0,0.2);
                width:100%; max-width:480px; overflow:hidden;">

        {{-- Modal header --}}
        <div style="display:flex; align-items:center; justify-content:space-between;
                    padding:1.125rem 1.5rem; border-bottom:1px solid #f3f4f6;">
            <h3 style="font-size:1rem; font-weight:700; color:#111827; margin:0;">Add Annotation</h3>
            <button onclick="closeAnnotationModal()"
                    style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:0.25rem;">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        {{-- Modal form --}}
        <form method="POST" action="{{ route('teacher.annotations.store') }}" id="annotation-form">
            @csrf
            <div style="padding:1.5rem; display:flex; flex-direction:column; gap:1rem;">

                {{-- Student --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.375rem;">
                        Student
                    </label>
                    <select name="student_id" required
                            style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem;
                                   font-size:0.875rem; color:#111827; background:#ffffff; outline:none;">
                        <option value="">Select a student…</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.375rem;">
                        Date
                    </label>
                    <input type="date" name="annotation_date" id="annotation-date" required
                           style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem;
                                  font-size:0.875rem; color:#111827; background:#ffffff; outline:none; box-sizing:border-box;">
                </div>

                {{-- Note --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.375rem;">
                        Note
                    </label>
                    <textarea name="note" rows="4" required placeholder="Write your observation…"
                              style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem;
                                     font-size:0.875rem; color:#111827; resize:vertical; outline:none; box-sizing:border-box;"></textarea>
                </div>

                {{-- Tags --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.375rem;">
                        Tags <span style="font-weight:400; color:#9ca3af;">(comma-separated)</span>
                    </label>
                    <input type="text" name="tags" placeholder="e.g. reading, pronunciation, CVC"
                           style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem;
                                  font-size:0.875rem; color:#111827; background:#ffffff; outline:none; box-sizing:border-box;">
                </div>

            </div>

            {{-- Modal footer --}}
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;
                        padding:1rem 1.5rem; border-top:1px solid #f3f4f6; background:#f9fafb;">
                <button type="button" onclick="closeAnnotationModal()"
                        style="padding:0.5rem 1rem; border:1px solid #d1d5db; border-radius:0.5rem;
                               background:#ffffff; font-size:0.875rem; color:#374151; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:0.5rem 1.125rem; border:none; border-radius:0.5rem;
                               background:#2563eb; color:#ffffff; font-size:0.875rem; font-weight:600; cursor:pointer;">
                    Save Note
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('annotation-modal');

    window.openAnnotationModal = function () {
        var dateInput = document.getElementById('annotation-date');
        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().slice(0, 10);
        }
        modal.style.display = 'flex';
    };

    window.closeAnnotationModal = function () {
        modal.style.display = 'none';
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeAnnotationModal();
    });
})();
</script>
@endpush
