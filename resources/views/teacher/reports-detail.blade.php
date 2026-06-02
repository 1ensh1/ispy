@extends('layouts.teacher')
@section('title', $student->name . ' — Report')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <style>
    @media print {
        nav, aside, header, .no-print, footer { display: none !important; }
        body { background: white !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; font-size: 12px; }
        a { color: black !important; text-decoration: none !important; }
    }
    </style>

    {{-- Back + heading --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
        <div>
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 no-print" style="margin-bottom:0.75rem; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <h2 style="font-size:1.5rem; font-weight:700; color:#111827; margin:0 0 0.25rem 0;">{{ $student->name }}</h2>
            <p style="font-size:0.875rem; color:#6b7280; margin:0;">
                Detailed progress report
                @if($student->parent_id)
                    &mdash; Parent linked
                @else
                    &mdash; <span style="color:#b45309; font-weight:500;">No parent linked</span>
                @endif
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;" class="no-print">
            <a href="{{ route('teacher.reports.export', $student->id) }}"
               style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; background:#16a34a; color:#fff; border-radius:0.5rem; font-size:0.875rem; font-weight:500; text-decoration:none; border:none;"
               onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <i data-lucide="download" style="width:16px; height:16px;"></i>
                Export CSV
            </a>
            <button onclick="window.print()"
                    style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; background:#4b5563; color:#fff; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; cursor:pointer;"
                    onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#4b5563'">
                <i data-lucide="printer" style="width:16px; height:16px;"></i>
                Print / Save as PDF
            </button>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- Section 1: Mastery Breakdown                                           --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:hidden;">
        <div style="padding:1rem 1.25rem; border-bottom:1px solid #f3f4f6;">
            <h3 style="font-size:1rem; font-weight:600; color:#111827; margin:0 0 0.125rem 0;">Mastery Breakdown</h3>
            <p style="font-size:0.75rem; color:#9ca3af; margin:0;">Per-word proficiency levels based on cumulative scores</p>
        </div>

        @if($masteryScores->isEmpty())
            <div style="padding:3rem 1.25rem; text-align:center;">
                <i data-lucide="book-open" style="width:32px; height:32px; color:#d1d5db; margin:0 auto 0.5rem; display:block;"></i>
                <p style="font-size:0.875rem; color:#9ca3af; margin:0;">No mastery scores available for this student yet.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                            <th style="padding:0.75rem 1.25rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Filipino</th>
                            <th style="padding:0.75rem 1.25rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">English</th>
                            <th style="padding:0.75rem 1.25rem; text-align:center; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Total Score</th>
                            <th style="padding:0.75rem 1.25rem; text-align:left; font-weight:500; color:#6b7280; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Proficiency</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($masteryScores as $score)
                            @php
                                $profColor = match($score->proficiency_level) {
                                    'Mastered'   => ['bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4'],
                                    'Developing' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                                    'Beginning'  => ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a'],
                                    default      => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#e5e7eb'],
                                };
                            @endphp
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:0.875rem 1.25rem; font-weight:500; color:#111827;">
                                    {{ optional($score->vocabulary)->filipino_label ?? '—' }}
                                </td>
                                <td style="padding:0.875rem 1.25rem; color:#6b7280;">
                                    {{ optional($score->vocabulary)->english_label ?? '—' }}
                                </td>
                                <td style="padding:0.875rem 1.25rem; text-align:center; font-weight:600; color:#111827;">
                                    {{ number_format($score->total_score, 1) }}
                                </td>
                                <td style="padding:0.875rem 1.25rem;">
                                    <span style="display:inline-block; padding:0.15rem 0.65rem; border-radius:9999px; background:{{ $profColor['bg'] }}; color:{{ $profColor['text'] }}; border:1px solid {{ $profColor['border'] }}; font-size:0.75rem; font-weight:600;">
                                        {{ $score->proficiency_level ?? 'Unrated' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- Section 2: Learning Mode Breakdown                                     --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:hidden;">
        <div style="padding:1rem 1.25rem; border-bottom:1px solid #f3f4f6;">
            <h3 style="font-size:1rem; font-weight:600; color:#111827; margin:0 0 0.125rem 0;">Learning Mode Breakdown</h3>
            <p style="font-size:0.75rem; color:#9ca3af; margin:0;">Activity per word across Identification, Matching, and Spelling modes</p>
        </div>

        @if(empty($progressByWord))
            <div style="padding:3rem 1.25rem; text-align:center;">
                <i data-lucide="activity" style="width:32px; height:32px; color:#d1d5db; margin:0 auto 0.5rem; display:block;"></i>
                <p style="font-size:0.875rem; color:#9ca3af; margin:0;">No learning activity recorded for this student yet.</p>
            </div>
        @else
            <div>
                @foreach($progressByWord as $vocabId => $wordData)
                    @php $vocab = $wordData['vocab']; $modes = $wordData['modes']; @endphp
                    <div style="padding:1rem 1.25rem; border-bottom:1px solid #f3f4f6;">

                        {{-- Word label --}}
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                            <span style="font-weight:600; color:#111827; font-size:0.875rem;">
                                {{ optional($vocab)->filipino_label ?? '—' }}
                            </span>
                            @if(optional($vocab)->english_label)
                                <span style="font-size:0.75rem; color:#d1d5db;">/</span>
                                <span style="font-size:0.75rem; color:#6b7280;">{{ $vocab->english_label }}</span>
                            @endif
                        </div>

                        {{-- Mode cards --}}
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:0.75rem;">
                            @foreach(['Identification', 'Matching', 'Spelling'] as $modeName)
                                @php $modeRecord = $modes[$modeName] ?? null; @endphp
                                <div style="background:#f9fafb; border-radius:0.5rem; padding:0.75rem; border:1px solid #f3f4f6;">
                                    <p style="font-size:0.6875rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem 0;">
                                        {{ $modeName }}
                                    </p>
                                    @if($modeRecord)
                                        <div style="display:flex; flex-direction:column; gap:0.25rem;">
                                            <div style="display:flex; justify-content:space-between; font-size:0.75rem;">
                                                <span style="color:#6b7280;">Attempts</span>
                                                <span style="font-weight:600; color:#111827;">{{ $modeRecord->attempts }}</span>
                                            </div>
                                            <div style="display:flex; justify-content:space-between; font-size:0.75rem;">
                                                <span style="color:#6b7280;">Score</span>
                                                <span style="font-weight:600; color:#111827;">{{ number_format($modeRecord->score, 1) }}</span>
                                            </div>
                                            <div style="display:flex; justify-content:space-between; font-size:0.75rem;">
                                                <span style="color:#6b7280;">Weight</span>
                                                <span style="font-weight:600; color:#111827;">{{ $modeRecord->mastery_weight }}</span>
                                            </div>
                                            @php
                                                $errors = is_array($modeRecord->errors)
                                                    ? implode(', ', array_filter($modeRecord->errors))
                                                    : ($modeRecord->errors ?? null);
                                            @endphp
                                            @if($errors)
                                                <div style="margin-top:0.25rem; font-size:0.6875rem; color:#b45309; background:#fef3c7; border-radius:0.25rem; padding:0.2rem 0.4rem; word-break:break-word;">
                                                    Errors: {{ $errors }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <p style="font-size:0.75rem; color:#d1d5db; margin:0;">Not attempted</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- Section 3: Send Report                                                 --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,0.06); padding:1.25rem;">
        <h3 style="font-size:1rem; font-weight:600; color:#111827; margin:0 0 0.25rem 0;">Send Report to Parent</h3>
        <p style="font-size:0.875rem; color:#6b7280; margin:0 0 1rem 0;">
            Notify the parent via the portal. They will receive an in-app notification with a link to the progress review page.
        </p>

        @if($student->parent_id)

            {{-- Normal send form --}}
            <form method="POST" action="{{ route('teacher.reports.send', $student->id) }}">
                @csrf
                <button type="submit"
                        style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; background:#1e3a5f; color:#fff; border-radius:0.5rem; font-size:0.875rem; font-weight:600; border:none; cursor:pointer; transition:opacity 0.15s;"
                        onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                    <i data-lucide="send" style="width:1rem; height:1rem;"></i>
                    Send Report to Parent
                </button>
            </form>

            {{-- Force-through form: shown only after a no-activity warning for this student --}}
            @if(session('warn_force_student') == $student->id)
                <form method="POST" action="{{ route('teacher.reports.send', $student->id) }}" style="margin-top:0.75rem;">
                    @csrf
                    <input type="hidden" name="force" value="1">
                    <button type="submit"
                            style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 1rem; background:#b45309; color:#fff; border-radius:0.5rem; font-size:0.8125rem; font-weight:600; border:none; cursor:pointer;"
                            onclick="return confirm('Send report even though there is no learning activity recorded yet?')">
                        <i data-lucide="alert-triangle" style="width:0.875rem; height:0.875rem;"></i>
                        Send Anyway
                    </button>
                </form>
            @endif

        @else
            <div style="display:flex; align-items:flex-start; gap:0.625rem; padding:0.875rem 1rem; background:#fef3c7; border:1px solid #fde68a; border-radius:0.5rem;">
                <i data-lucide="alert-triangle" style="width:16px; height:16px; color:#b45309; flex-shrink:0; margin-top:0.125rem;"></i>
                <p style="font-size:0.875rem; color:#b45309; margin:0;">
                    This student does not have a linked parent account. Contact the admin to link a parent before sending a report.
                </p>
            </div>
        @endif
    </div>

</div>
@endsection
