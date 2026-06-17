@extends('layouts.teacher')
@section('title', 'Consultations Report')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Page header --}}
    <a href="{{ route('teacher.reports') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 no-print" style="margin-bottom:0.75rem; text-decoration:none;">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
    </a>

    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Consultations Report</h2>
            <p class="text-sm text-gray-500 mt-1">Face-to-face consultation bookings, grouped by status</p>
        </div>
        <div class="no-print" style="display:flex; align-items:center; gap:0.5rem;">
            <a href="{{ route('teacher.reports.consultations.export', ['status' => $status]) }}"
               style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; background:#1e3a5f; color:#fff; border-radius:0.5rem; font-size:0.875rem; font-weight:500; text-decoration:none;">
                <i data-lucide="download" style="width:16px; height:16px;"></i>
                Export CSV
            </a>
            <button onclick="window.print()"
                    style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; background:#4b5563; color:#fff; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; cursor:pointer;"
                    onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#4b5563'">
                <i data-lucide="printer" style="width:16px; height:16px;"></i>
                Print / PDF
            </button>
        </div>
    </div>

    <style>
    @media print {
        nav, aside, header, .no-print, footer { display: none !important; }
        body { background: white !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; font-size: 12px; }
        a { color: black !important; text-decoration: none !important; }
    }
    </style>

    {{-- Status filter --}}
    <form method="GET" action="{{ route('teacher.reports.consultations') }}"
          class="no-print" style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
        <div style="position:relative;">
            <select name="status" onchange="this.form.submit()"
                    style="padding:0.5rem 2rem 0.5rem 0.75rem; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; color:#374151; outline:none; background:#fff; appearance:none; min-width:10rem; cursor:pointer;">
                @foreach(['All', 'Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rejected', 'No-show'] as $opt)
                    <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <svg style="pointer-events:none; position:absolute; right:0.625rem; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#9ca3af;"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </div>
    </form>

    @php
        $sections = [
            'Confirmed' => ['title' => 'Confirmed Consultations', 'rows' => $confirmed, 'badge' => ['bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4']],
            'Pending'   => ['title' => 'Pending Consultations',   'rows' => $pending,   'badge' => ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a']],
            'Completed' => ['title' => 'Completed Consultations', 'rows' => $completed, 'badge' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#bfdbfe']],
            'Cancelled' => ['title' => 'Cancelled Consultations', 'rows' => $cancelled, 'badge' => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#e5e7eb']],
            'Rejected'  => ['title' => 'Rejected Consultations',  'rows' => $rejected,  'badge' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca']],
            'No-show'   => ['title' => 'No-show Consultations',   'rows' => $noShow,    'badge' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca']],
        ];
    @endphp

    @foreach($sections as $key => $section)
        @if($status === 'All' || $status === $key)
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $section['title'] }}</h3>

                @if($section['rows']->isEmpty())
                    <p style="font-size:0.875rem; color:#9ca3af; padding:1rem 0;">No records.</p>
                @else
                    <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:hidden;">
                        <div style="overflow-x:auto;">
                            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                                <thead>
                                    <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                                        <th class="text-center" style="padding:0.875rem 1.25rem; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Parent Name</th>
                                        <th class="text-center" style="padding:0.875rem 1.25rem; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Date</th>
                                        <th class="text-center" style="padding:0.875rem 1.25rem; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Time Slot</th>
                                        <th class="text-center" style="padding:0.875rem 1.25rem; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Purpose</th>
                                        <th class="text-center" style="padding:0.875rem 1.25rem; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($section['rows'] as $row)
                                        <tr style="border-bottom:1px solid #f3f4f6;">
                                            <td class="text-center" style="padding:1rem 1.25rem; font-weight:600; color:#111827;">{{ $row->parent_name ?? '—' }}</td>
                                            <td class="text-center" style="padding:1rem 1.25rem; color:#374151;">{{ $row->scheduled_date ? \Carbon\Carbon::parse($row->scheduled_date)->format('M d, Y') : '—' }}</td>
                                            <td class="text-center" style="padding:1rem 1.25rem; color:#374151; white-space:nowrap;">{{ date('g:i A', strtotime($row->time_start)) }} – {{ date('g:i A', strtotime($row->time_end)) }}</td>
                                            <td class="text-center break-words whitespace-normal" style="padding:1rem 1.25rem; color:#374151; overflow-wrap: break-word; word-break: break-all; max-width: 300px;">{{ $row->purpose_of_meeting ?? '—' }}</td>
                                            <td class="text-center" style="padding:1rem 1.25rem;">
                                                <span style="display:inline-block; padding:0.15rem 0.65rem; border-radius:9999px; background:{{ $section['badge']['bg'] }}; color:{{ $section['badge']['text'] }}; border:1px solid {{ $section['badge']['border'] }}; font-size:0.75rem; font-weight:600;">
                                                    {{ $row->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endforeach

</div>
@endsection
