@extends('layouts.teacher')
@section('title', 'Student Reports')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Student Reports</h2>
        <p class="text-sm text-gray-500 mt-1">View and send progress reports for each student</p>
    </div>

    {{-- Filters row --}}
    <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">

        {{-- Search --}}
        <div style="position:relative;">
            <i data-lucide="search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#9ca3af; pointer-events:none;"></i>
            <input id="report-search"
                   type="text"
                   placeholder="Search students..."
                   oninput="filterReports()"
                   style="padding:0.5rem 1rem 0.5rem 2.25rem; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; color:#111827; outline:none; background:#fff; width:13rem;">
        </div>

        {{-- Proficiency filter --}}
        <div style="position:relative;">
            <select id="proficiency-filter" onchange="filterReports()"
                    style="padding:0.5rem 2rem 0.5rem 0.75rem; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; color:#374151; outline:none; background:#fff; appearance:none; min-width:10rem; cursor:pointer;">
                <option value="">All Proficiency</option>
                <option value="Mastered">Mastered</option>
                <option value="Developing">Developing</option>
                <option value="Beginning">Beginning</option>
                <option value="None">No Data</option>
            </select>
            <svg style="pointer-events:none; position:absolute; right:0.625rem; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#9ca3af;"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </div>

    </div>

    {{-- Student table --}}
    @if($students->isEmpty())
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:5rem 1rem; text-align:center;">
            <i data-lucide="file-bar-chart" style="width:40px; height:40px; color:#d1d5db; margin-bottom:0.75rem;"></i>
            <p style="font-size:0.875rem; color:#9ca3af; font-weight:500; margin:0 0 0.25rem 0;">No students enrolled yet.</p>
            <p style="font-size:0.75rem; color:#d1fae5; margin:0; color:#d1d5db;">Student reports will appear here once students join your class.</p>
        </div>
    @else
        <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:hidden;">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;" id="reports-table">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                            <th style="padding:0.875rem 1.25rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Student</th>
                            <th style="padding:0.875rem 1.25rem; text-align:center; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Mastered</th>
                            <th style="padding:0.875rem 1.25rem; text-align:center; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Developing</th>
                            <th style="padding:0.875rem 1.25rem; text-align:center; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Beginning</th>
                            <th style="padding:0.875rem 1.25rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Overall</th>
                            <th style="padding:0.875rem 1.25rem;"></th>
                        </tr>
                    </thead>
                    <tbody id="reports-tbody">
                        @foreach($students as $student)
                            @php
                                $badgeColor = match($student->dominant_proficiency) {
                                    'Mastered'   => ['bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4'],
                                    'Developing' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                                    'Beginning'  => ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a'],
                                    default      => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#e5e7eb'],
                                };
                            @endphp
                            <tr class="report-row"
                                data-name="{{ strtolower($student->name) }}"
                                data-proficiency="{{ $student->dominant_proficiency }}"
                                style="border-bottom:1px solid #f3f4f6; transition:background 0.15s;"
                                onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                                <td style="padding:1rem 1.25rem; font-weight:600; color:#111827;">{{ $student->name }}</td>
                                <td style="padding:1rem 1.25rem; text-align:center;">
                                    <span style="display:inline-block; padding:0.15rem 0.65rem; border-radius:9999px; background:#ccfbf1; color:#0f766e; border:1px solid #99f6e4; font-size:0.75rem; font-weight:600;">
                                        {{ $student->mastered_count }}
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem; text-align:center;">
                                    <span style="display:inline-block; padding:0.15rem 0.65rem; border-radius:9999px; background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe; font-size:0.75rem; font-weight:600;">
                                        {{ $student->developing_count }}
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem; text-align:center;">
                                    <span style="display:inline-block; padding:0.15rem 0.65rem; border-radius:9999px; background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:0.75rem; font-weight:600;">
                                        {{ $student->beginning_count }}
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem;">
                                    <span style="display:inline-block; padding:0.15rem 0.65rem; border-radius:9999px; background:{{ $badgeColor['bg'] }}; color:{{ $badgeColor['text'] }}; border:1px solid {{ $badgeColor['border'] }}; font-size:0.75rem; font-weight:600;">
                                        {{ $student->dominant_proficiency }}
                                    </span>
                                </td>
                                <td style="padding:1rem 1.25rem;">
                                    <a href="{{ route('teacher.reports.show', $student->id) }}"
                                       style="display:inline-flex; align-items:center; gap:0.375rem; font-size:0.875rem; font-weight:500; color:#1e3a5f; white-space:nowrap; text-decoration:none;">
                                        View Report
                                        <i data-lucide="arrow-right" style="width:14px; height:14px;"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- No results state --}}
        <div id="no-results" style="display:none; flex-direction:column; align-items:center; justify-content:center; padding:4rem 1rem; text-align:center;">
            <i data-lucide="search-x" style="width:32px; height:32px; color:#d1d5db; margin-bottom:0.5rem;"></i>
            <p style="font-size:0.875rem; color:#9ca3af; margin:0;">No students found matching your search.</p>
        </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
    function filterReports() {
        var query      = document.getElementById('report-search').value.toLowerCase().trim();
        var profFilter = document.getElementById('proficiency-filter').value;
        var rows       = document.querySelectorAll('.report-row');
        var visible    = 0;

        rows.forEach(function (row) {
            var nameMatch = row.dataset.name.includes(query);
            var profMatch = profFilter === '' || row.dataset.proficiency === profFilter;
            var show      = nameMatch && profMatch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        var noResults = document.getElementById('no-results');
        if (noResults) noResults.style.display = visible > 0 ? 'none' : 'flex';
    }
</script>
@endpush
