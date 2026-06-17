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
            {{-- Download CSV template — subtle text link, sits next to Upload CSV --}}
            <a href="{{ route('teacher.enrollment.template') }}"
               style="display:inline-flex; align-items:center; gap:0.3rem; font-size:0.8125rem;
                      font-weight:600; color:#2563eb; text-decoration:none;">
                <i data-lucide="download" style="width:14px; height:14px;"></i>
                Download CSV Template
            </a>
            {{-- Upload CSV --}}
            <button onclick="openCsvModal()"
                    style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem;
                           background:#ffffff; color:#374151; font-size:0.875rem; font-weight:600;
                           border:1px solid #d1d5db; border-radius:0.5rem; cursor:pointer;">
                <i data-lucide="upload" style="width:16px; height:16px;"></i>
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

    {{-- Search bar + capacity indicator + per-page selector --}}
    @php
        // Cap indicator color-coding (Session 15 conventions): green comfortably
        // under cap, amber nearing it (>= 80%), red at/over the 20-student cap.
        $capColor = $activeCount >= $maxStudents
            ? '#dc2626'
            : ($activeCount >= $maxStudents * 0.8 ? '#d97706' : '#16a34a');
        $capBg = $activeCount >= $maxStudents
            ? '#fef2f2'
            : ($activeCount >= $maxStudents * 0.8 ? '#fffbeb' : '#f0fdf4');
    @endphp
    <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
        {{-- Class selector — owned classes only ($classLists shares $ownClassIds'
             source). Auto-submits to ?class_list_id=<id>, resetting to page 1 and
             preserving per_page; same bare-select + inline onchange pattern as the
             working per-page selector below. The icon is a normal inline sibling,
             NOT an absolute overlay, so it can never sit over the select's hit area
             and swallow the change event. --}}
        <div style="display:inline-flex; align-items:center; gap:0.375rem; flex-shrink:0;">
            <i data-lucide="layers" style="width:15px; height:15px; color:#6b7280;"></i>
            <select id="class-roster-select"
                    style="padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem;
                           font-size:0.875rem; font-weight:600; color:#111827; background:#ffffff; outline:none; cursor:pointer;">
                @foreach($classLists as $cl)
                    <option value="{{ $cl->id }}" {{ (int) $selectedClassId === (int) $cl->id ? 'selected' : '' }}>{{ $cl->class_name }}</option>
                @endforeach
            </select>
        </div>
        <span style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.875rem;
                     border-radius:9999px; background:{{ $capBg }}; color:{{ $capColor }};
                     border:1px solid {{ $capColor }}; font-size:0.8125rem; font-weight:600; flex-shrink:0;">
            <i data-lucide="users" style="width:14px; height:14px;"></i>
            {{ $activeCount }} / {{ $maxStudents }} enrolled
        </span>
        <div style="position:relative; flex:1; min-width:200px; max-width:320px;">
            <i data-lucide="search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%);
                                            width:16px; height:16px; color:#9ca3af; pointer-events:none;"></i>
            <input type="text" id="enroll-search" placeholder="Search students..."
                   oninput="filterEnrollment()"
                   style="width:100%; padding:0.5rem 0.75rem 0.5rem 2.25rem; border:1px solid #d1d5db;
                          border-radius:0.5rem; font-size:0.875rem; color:#111827; outline:none; box-sizing:border-box;">
        </div>
        <select onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.assign(u.toString());})(this.value)"
                style="padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem;
                       font-size:0.875rem; color:#374151; background:#ffffff; outline:none; margin-left:auto;">
            <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10 / page</option>
            <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20 / page</option>
            <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50 / page</option>
        </select>
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
                        <th style="padding:0.875rem 1.5rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.8125rem;">Parent</th>
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
                            <td style="padding:1rem 1.5rem;">
                                <div style="display:flex; align-items:center; gap:0.625rem;">
                                    <x-student-avatar :student="$student" size="32" />
                                    <span style="font-weight:700; color:#111827;">{{ $student->name }}</span>
                                </div>
                            </td>
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
                            <td style="padding:1rem 1.5rem;">
                                @if($student->parentUser)
                                    <div style="color:#111827; font-weight:600;">{{ $student->parentUser->name }}</div>
                                    <div style="color:#6b7280; font-size:0.8125rem;">{{ $student->parentUser->contact_number ?? '—' }}</div>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:3rem 1.5rem; text-align:center; color:#9ca3af;">
                                <i data-lucide="clipboard-list" style="width:32px; height:32px; margin:0 auto 0.5rem; opacity:0.4; display:block;"></i>
                                <p style="font-size:0.875rem; margin:0;">No students enrolled in your class yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div style="padding:1rem 1.5rem; border-top:1px solid #e5e7eb; background:#f9fafb;">
                {{ $students->links() }}
            </div>
        @endif
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
                            <option value="{{ $cl->id }}">{{ $cl->class_name }}@if(!empty($cl->subjects)) ({{ implode(', ', $cl->subjects) }})@endif</option>
                        @endforeach
                    </select>
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

{{-- Bulk Enroll via CSV Modal --}}
<div id="csv-modal"
     style="display:none; position:fixed; inset:0; z-index:100; background:rgba(0,0,0,0.4);
            align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#ffffff; border-radius:0.875rem; box-shadow:0 20px 60px rgba(0,0,0,0.2);
                width:100%; max-width:520px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden;">

        {{-- Modal header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between;
                    padding:1.25rem 1.5rem 1rem; border-bottom:1px solid #f3f4f6; flex-shrink:0;">
            <div>
                <h3 style="font-size:1rem; font-weight:700; color:#111827; margin:0 0 0.25rem 0;">Bulk Enroll via CSV</h3>
                <p style="font-size:0.8125rem; color:#6b7280; margin:0;">Enroll existing students into a class from a CSV file.</p>
            </div>
            <button onclick="closeCsvModal()"
                    style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:0.25rem; margin-top:-0.125rem;">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        {{-- Scrollable body --}}
        <div style="overflow-y:auto; padding:1.5rem;">
            <form id="csv-form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:1.125rem;">

                    {{-- Select Class --}}
                    <div>
                        <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                            Select Class
                        </label>
                        <select id="csv-class-select" required
                                style="width:100%; padding:0.625rem 0.875rem; border:1px solid #d1d5db; border-radius:0.5rem;
                                       font-size:0.875rem; color:#111827; background:#ffffff; outline:none; box-sizing:border-box;">
                            <option value="">Select a class…</option>
                            @foreach($classLists as $cl)
                                <option value="{{ $cl->id }}" {{ (int) $selectedClassId === (int) $cl->id ? 'selected' : '' }}>{{ $cl->class_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- File input --}}
                    <div>
                        <label style="display:block; font-size:0.8125rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">
                            Choose CSV File (.csv)
                        </label>
                        <input type="file" id="csv-file" accept=".csv" required
                               style="width:100%; font-size:0.875rem; color:#374151; box-sizing:border-box;">
                    </div>

                    {{-- Note --}}
                    <p style="font-size:0.75rem; color:#6b7280; margin:0; line-height:1.5;">
                        Each row should contain one student name exactly as it appears in the system.
                    </p>

                    {{-- Template link --}}
                    <a href="{{ route('teacher.enrollment.template') }}"
                       style="display:inline-flex; align-items:center; gap:0.3rem; font-size:0.8125rem;
                              font-weight:600; color:#2563eb; text-decoration:none;">
                        <i data-lucide="download" style="width:14px; height:14px;"></i>
                        Need the template? Download it here
                    </a>

                    {{-- Submit --}}
                    <button type="submit" id="csv-submit"
                            style="display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
                                   padding:0.625rem 1.125rem; border:none; border-radius:0.5rem;
                                   background:#1e3a5f; color:#ffffff; font-size:0.875rem; font-weight:600; cursor:pointer;">
                        <i data-lucide="upload" style="width:16px; height:16px;"></i>
                        Upload &amp; Enroll
                    </button>
                </div>
            </form>

            {{-- Results area (revealed after response) --}}
            <div id="csv-results" style="display:none; margin-top:1.25rem; flex-direction:column; gap:1rem;"></div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
{{-- Blade URLs exposed to JS — never hardcode endpoints in scripts. --}}
<script>
    const csvUploadUrl = "{{ route('teacher.enrollment.csv') }}";
</script>
<script>
(function () {
    var modal = document.getElementById('enroll-modal');

    window.openEnrollModal = function () {
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

    // Class roster selector — auto-navigate to the chosen class. Bound via
    // addEventListener (not an inline onchange) because the inline handler was
    // reliably swallowed on this element; this attaches directly to the node.
    var classRosterSelect = document.getElementById('class-roster-select');
    if (classRosterSelect) {
        classRosterSelect.addEventListener('change', function () {
            var u = new URL(window.location.href);
            u.searchParams.set('class_list_id', this.value);
            u.searchParams.delete('page');
            window.location.assign(u.toString());
        });
    }

    // ── Bulk Enroll via CSV ──────────────────────────────────────────────────
    var csvModal   = document.getElementById('csv-modal');
    var csvForm    = document.getElementById('csv-form');
    var csvClass   = document.getElementById('csv-class-select');
    var csvFile    = document.getElementById('csv-file');
    var csvSubmit  = document.getElementById('csv-submit');
    var csvResults = document.getElementById('csv-results');

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function syncCsvSubmit() {
        var disabled = !csvClass.value;
        csvSubmit.disabled = disabled;
        csvSubmit.style.opacity = disabled ? '0.5' : '1';
        csvSubmit.style.cursor  = disabled ? 'not-allowed' : 'pointer';
    }

    function resetCsvResults() {
        csvResults.innerHTML = '';
        csvResults.style.display = 'none';
    }

    function setCsvLoading(loading) {
        if (loading) {
            csvSubmit.disabled = true;
            csvSubmit.style.opacity = '0.7';
            csvSubmit.style.cursor = 'wait';
            csvSubmit.innerHTML =
                '<i data-lucide="loader-2" class="animate-spin" style="width:16px; height:16px;"></i> Uploading…';
            if (window.lucide) lucide.createIcons();
        } else {
            csvSubmit.innerHTML =
                '<i data-lucide="upload" style="width:16px; height:16px;"></i> Upload &amp; Enroll';
            if (window.lucide) lucide.createIcons();
            syncCsvSubmit();
        }
    }

    window.openCsvModal = function () {
        resetCsvResults();
        csvForm.reset();
        syncCsvSubmit();
        csvModal.style.display = 'flex';
    };

    window.closeCsvModal = function () {
        csvModal.style.display = 'none';
    };

    if (csvModal) {
        csvModal.addEventListener('click', function (e) {
            if (e.target === csvModal) closeCsvModal();
        });
    }

    if (csvClass) {
        csvClass.addEventListener('change', syncCsvSubmit);
    }

    function renderCsvResults(data) {
        var html = '';

        if (data.class_full === true) {
            html +=
                '<div style="display:flex; align-items:flex-start; gap:0.5rem; padding:0.75rem 1rem; border-radius:0.5rem;' +
                ' background:#fffbeb; border:1px solid #f59e0b; color:#92400e; font-size:0.8125rem; font-weight:600;">' +
                '<i data-lucide="alert-triangle" style="width:16px; height:16px; flex-shrink:0; margin-top:1px;"></i>' +
                '<span>Class is full (20-student limit reached). Some rows were not processed.</span></div>';
        }

        if (Array.isArray(data.enrolled) && data.enrolled.length > 0) {
            html +=
                '<div style="padding:0.875rem 1rem; border-radius:0.5rem; background:#f0fdf4; border:1px solid #16a34a;">' +
                '<h4 style="margin:0 0 0.5rem 0; font-size:0.8125rem; font-weight:700; color:#15803d;">Successfully Enrolled (' +
                data.enrolled.length + ')</h4><ul style="margin:0; padding-left:1.125rem; color:#166534; font-size:0.8125rem;">' +
                data.enrolled.map(function (n) { return '<li>' + escapeHtml(n) + '</li>'; }).join('') +
                '</ul></div>';
        }

        if (Array.isArray(data.already_enrolled) && data.already_enrolled.length > 0) {
            html +=
                '<div style="padding:0.875rem 1rem; border-radius:0.5rem; background:#fffbeb; border:1px solid #f59e0b;">' +
                '<h4 style="margin:0 0 0.25rem 0; font-size:0.8125rem; font-weight:700; color:#92400e;">Already Enrolled (' +
                data.already_enrolled.length + ')</h4>' +
                '<p style="margin:0 0 0.5rem 0; font-size:0.75rem; color:#b45309;">These students were already in this class and were skipped.</p>' +
                '<ul style="margin:0; padding-left:1.125rem; color:#92400e; font-size:0.8125rem;">' +
                data.already_enrolled.map(function (n) { return '<li>' + escapeHtml(n) + '</li>'; }).join('') +
                '</ul></div>';
        }

        if (Array.isArray(data.errors) && data.errors.length > 0) {
            var rows = data.errors.map(function (err) {
                return '<tr style="border-bottom:1px solid #fecaca;">' +
                    '<td style="padding:0.5rem 0.75rem; color:#991b1b;">' + escapeHtml(err.row) + '</td>' +
                    '<td style="padding:0.5rem 0.75rem; color:#991b1b;">' + escapeHtml(err.name) + '</td>' +
                    '<td style="padding:0.5rem 0.75rem; color:#991b1b;">' + escapeHtml(err.reason) + '</td>' +
                    '</tr>';
            }).join('');
            html +=
                '<div style="padding:0.875rem 1rem; border-radius:0.5rem; background:#fef2f2; border:1px solid #dc2626;">' +
                '<h4 style="margin:0 0 0.5rem 0; font-size:0.8125rem; font-weight:700; color:#b91c1c;">Failed Rows (' +
                data.errors.length + ')</h4>' +
                '<div style="overflow-x:auto;"><table style="width:100%; border-collapse:collapse; font-size:0.75rem;">' +
                '<thead><tr style="text-align:left; color:#b91c1c;">' +
                '<th style="padding:0.375rem 0.75rem; font-weight:600;">Row #</th>' +
                '<th style="padding:0.375rem 0.75rem; font-weight:600;">Student Name</th>' +
                '<th style="padding:0.375rem 0.75rem; font-weight:600;">Reason</th>' +
                '</tr></thead><tbody>' + rows + '</tbody></table></div></div>';
        }

        csvResults.innerHTML = html;
        csvResults.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }

    function renderCsvError() {
        csvResults.innerHTML =
            '<div style="padding:0.875rem 1rem; border-radius:0.5rem; background:#fef2f2; border:1px solid #dc2626;' +
            ' color:#b91c1c; font-size:0.8125rem; font-weight:600;">Upload failed. Please try again.</div>';
        csvResults.style.display = 'flex';
    }

    if (csvForm) {
        csvForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!csvClass.value || !csvFile.files.length) return;

            var tokenInput = csvForm.querySelector('input[name="_token"]');
            var fd = new FormData();
            fd.append('file', csvFile.files[0]);
            fd.append('class_list_id', csvClass.value);
            if (tokenInput) fd.append('_token', tokenInput.value);

            resetCsvResults();
            setCsvLoading(true);

            fetch(csvUploadUrl, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('Request failed');
                    return res.json();
                })
                .then(function (data) {
                    setCsvLoading(false);
                    renderCsvResults(data);
                    if (Array.isArray(data.enrolled) && data.enrolled.length > 0) {
                        setTimeout(function () { window.location.reload(); }, 2000);
                    }
                })
                .catch(function () {
                    setCsvLoading(false);
                    renderCsvError();
                });
        });
    }
})();
</script>
@endpush
