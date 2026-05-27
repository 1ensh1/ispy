@extends('layouts.teacher')
@section('title', 'Enrollment')

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    {{-- Page header --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
        <div>
            <h2 style="font-size:1.5rem; font-weight:700; color:#111827; margin:0 0 0.25rem 0;">Enrollment Management</h2>
            <p style="font-size:0.875rem; color:#6b7280; margin:0;">Manage official class list and student enrollment</p>
        </div>
        <div style="display:flex; align-items:center; gap:0.625rem; flex-shrink:0;">
            {{-- Upload CSV --}}
            <button onclick="alert('CSV upload coming soon.')"
                    style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem;
                           background:#ffffff; color:#374151; font-size:0.875rem; font-weight:600;
                           border:1px solid #d1d5db; border-radius:0.5rem; cursor:pointer;">
                <i data-lucide="file-text" style="width:16px; height:16px;"></i>
                Upload CSV
            </button>
            {{-- Add Student --}}
            <button onclick="openEnrollModal()"
                    style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem;
                           background:#1e3a5f; color:#ffffff; font-size:0.875rem; font-weight:600;
                           border:none; border-radius:0.5rem; cursor:pointer;">
                <i data-lucide="user-plus" style="width:16px; height:16px;"></i>
                Add Student
            </button>
        </div>
    </div>

    {{-- Search bar --}}
    <div style="position:relative; max-width:320px;">
        <i data-lucide="search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%);
                                        width:16px; height:16px; color:#9ca3af; pointer-events:none;"></i>
        <input type="text" id="enroll-search" placeholder="Search students..."
               oninput="filterEnrollment()"
               style="width:100%; padding:0.5rem 0.75rem 0.5rem 2.25rem; border:1px solid #d1d5db;
                      border-radius:0.5rem; font-size:0.875rem; color:#111827; outline:none; box-sizing:border-box;">
    </div>

    {{-- Table --}}
    <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #e5e7eb;
                box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;" id="enroll-table">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <th style="padding:0.875rem 1.5rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.8125rem;">Student ID</th>
                        <th style="padding:0.875rem 1.5rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.8125rem;">Name</th>
                        <th style="padding:0.875rem 1.5rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.8125rem;">Section</th>
                        <th style="padding:0.875rem 1.5rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.8125rem;">Status</th>
                    </tr>
                </thead>
                <tbody id="enroll-tbody">
                    @forelse($students as $student)
                        @php
                            $year     = $student->created_at ? $student->created_at->format('Y') : date('Y');
                            $stuId    = 'STU-' . $year . '-' . str_pad($student->id, 3, '0', STR_PAD_LEFT);
                            $enrolled = !is_null($student->parent_id);
                            $section  = optional($student->classList)->class_name ?? '—';
                        @endphp
                        <tr class="enroll-row" data-name="{{ strtolower($student->name) }}"
                            style="border-bottom:1px solid #f3f4f6; transition:background 0.15s;">
                            <td style="padding:1rem 1.5rem; color:#6b7280; font-size:0.8125rem;">{{ $stuId }}</td>
                            <td style="padding:1rem 1.5rem; font-weight:700; color:#111827;">{{ $student->name }}</td>
                            <td style="padding:1rem 1.5rem; color:#6b7280;">{{ $section }}</td>
                            <td style="padding:1rem 1.5rem;">
                                @if($enrolled)
                                    <span style="display:inline-block; padding:0.25rem 0.875rem; border-radius:9999px;
                                                 background:#0d9488; color:#ffffff; font-size:0.75rem; font-weight:600;">
                                        Enrolled
                                    </span>
                                @else
                                    <span style="display:inline-block; padding:0.25rem 0.875rem; border-radius:9999px;
                                                 background:transparent; border:1.5px solid #16a34a; color:#16a34a;
                                                 font-size:0.75rem; font-weight:600;">
                                        Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:3rem 1.5rem; text-align:center; color:#9ca3af;">
                                <i data-lucide="clipboard-list" style="width:32px; height:32px; margin:0 auto 0.5rem; opacity:0.4; display:block;"></i>
                                <p style="font-size:0.875rem; margin:0;">No students enrolled in your class yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Add Student Modal --}}
<div id="enroll-modal"
     style="display:none; position:fixed; inset:0; z-index:100; background:rgba(0,0,0,0.4);
            align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#ffffff; border-radius:0.875rem; box-shadow:0 20px 60px rgba(0,0,0,0.2);
                width:100%; max-width:460px; overflow:hidden;">

        {{-- Modal header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between;
                    padding:1.25rem 1.5rem 1rem; border-bottom:1px solid #f3f4f6;">
            <div>
                <h3 style="font-size:1rem; font-weight:700; color:#111827; margin:0 0 0.25rem 0;">Add Student</h3>
                <p style="font-size:0.8125rem; color:#6b7280; margin:0;">Manually enter a new student into the class roster.</p>
            </div>
            <button onclick="closeEnrollModal()"
                    style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:0.25rem; margin-top:-0.125rem;">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        {{-- Modal form --}}
        <form method="POST" action="{{ route('teacher.enrollment.store') }}" id="enroll-form">
            @csrf
            <div style="padding:1.5rem; display:flex; flex-direction:column; gap:1.125rem;">

                {{-- Student Name --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                        Student Name
                    </label>
                    <input type="text" name="name" required placeholder="e.g. Maria Santos"
                           style="width:100%; padding:0.625rem 0.875rem; border:1.5px solid #2563eb; border-radius:0.5rem;
                                  font-size:0.875rem; color:#111827; outline:none; box-sizing:border-box;">
                </div>

                {{-- Profile Icon --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                        Profile Icon
                    </label>
                    <input type="hidden" id="enroll-profile-icon" name="profile_icon" value="cat">
                    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.5rem;">
                        @foreach([
                            'cat'     => '🐱',
                            'dog'     => '🐶',
                            'bear'    => '🐻',
                            'rabbit'  => '🐰',
                            'fox'     => '🦊',
                            'frog'    => '🐸',
                            'penguin' => '🐧',
                            'lion'    => '🦁',
                        ] as $iconName => $emoji)
                            <button type="button"
                                    onclick="selectEnrollIcon('{{ $iconName }}')"
                                    id="enroll-icon-btn-{{ $iconName }}"
                                    data-icon="{{ $iconName }}"
                                    style="display:flex; flex-direction:column; align-items:center; gap:0.25rem;
                                           padding:0.625rem 0.375rem; border-radius:0.5rem; cursor:pointer;
                                           border:2px solid {{ $iconName === 'cat' ? '#1e3a5f' : '#e5e7eb' }};
                                           background:{{ $iconName === 'cat' ? 'rgba(30,58,95,0.06)' : '#ffffff' }};
                                           transition:border-color 0.15s, background 0.15s;">
                                <span style="font-size:1.375rem; line-height:1;">{{ $emoji }}</span>
                                <span style="font-size:0.625rem; color:#6b7280; text-transform:capitalize;">{{ $iconName }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Assigned Section (teacher's class lists only) --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                        Assigned Section
                    </label>
                    <select name="class_list_id" required
                            style="width:100%; padding:0.625rem 0.875rem; border:1px solid #d1d5db; border-radius:0.5rem;
                                   font-size:0.875rem; color:#111827; background:#ffffff; outline:none; box-sizing:border-box;">
                        <option value="">Select a section…</option>
                        @foreach($classLists as $cl)
                            <option value="{{ $cl->id }}">{{ $cl->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Parent Password --}}
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                        Parent Password
                    </label>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="text" id="enroll-parent-password" name="parent_password"
                               style="flex:1; padding:0.625rem 0.875rem; border:1px solid #d1d5db; border-radius:0.5rem;
                                      font-size:0.875rem; font-family:monospace; color:#111827; outline:none; box-sizing:border-box;">
                        <button type="button" onclick="generateEnrollPassword()"
                                style="padding:0.625rem 0.875rem; border:none; border-radius:0.5rem;
                                       background:#1e3a5f; color:#ffffff; font-size:0.8125rem; font-weight:600; cursor:pointer; white-space:nowrap;">
                            Generate
                        </button>
                    </div>
                    <p style="margin:0.375rem 0 0; font-size:0.75rem; color:#9ca3af;">This password is used by the student's mobile app at home.</p>
                </div>

            </div>

            {{-- Modal footer --}}
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;
                        padding:1rem 1.5rem; border-top:1px solid #f3f4f6;">
                <button type="button" onclick="closeEnrollModal()"
                        style="padding:0.5rem 1.125rem; border:1px solid #d1d5db; border-radius:0.5rem;
                               background:#ffffff; font-size:0.875rem; color:#374151; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="display:inline-flex; align-items:center; gap:0.375rem;
                               padding:0.5rem 1.125rem; border:none; border-radius:0.5rem;
                               background:#1e3a5f; color:#ffffff; font-size:0.875rem; font-weight:600; cursor:pointer;">
                    <i data-lucide="plus" style="width:14px; height:14px;"></i>
                    Add to Roster
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('enroll-modal');

    window.generateEnrollPassword = function () {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        var pw = '';
        for (var i = 0; i < 8; i++) pw += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('enroll-parent-password').value = pw;
    };

    window.openEnrollModal = function () {
        generateEnrollPassword();
        modal.style.display = 'flex';
    };

    window.closeEnrollModal = function () {
        modal.style.display = 'none';
    };

    window.selectEnrollIcon = function (name) {
        document.getElementById('enroll-profile-icon').value = name;
        document.querySelectorAll('[id^="enroll-icon-btn-"]').forEach(function (btn) {
            var selected = btn.dataset.icon === name;
            btn.style.borderColor = selected ? '#1e3a5f' : '#e5e7eb';
            btn.style.background  = selected ? 'rgba(30,58,95,0.06)' : '#ffffff';
        });
    };

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeEnrollModal();
    });

    window.filterEnrollment = function () {
        var query = document.getElementById('enroll-search').value.toLowerCase();
        document.querySelectorAll('#enroll-tbody .enroll-row').forEach(function (row) {
            row.style.display = row.dataset.name.includes(query) ? '' : 'none';
        });
    };
})();
</script>
@endpush
