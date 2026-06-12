@extends('layouts.teacher')
@section('title', 'Milestones')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Student Milestones</h2>
        <p class="text-sm text-gray-500 mt-1">Track CVC mastery and multi-syllable progression</p>
    </div>

    {{-- Class Leaderboard --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">

        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1.25rem;">
            <span style="font-size:1.25rem; line-height:1;">&#127942;</span>
            <h3 style="font-size:1.125rem; font-weight:700; color:#111827; margin:0;">Class Leaderboard</h3>
        </div>

        @if($leaderboard->isEmpty())
            <div style="text-align:center; padding:2.5rem 0;">
                <p style="font-size:0.875rem; color:#9ca3af;">No students enrolled yet.</p>
            </div>
        @else
            @php
                $iconEmoji = [
                    'cat' => '🐱', 'dog' => '🐶', 'bear' => '🐻', 'rabbit' => '🐰',
                    'fox' => '🦊', 'frog' => '🐸', 'penguin' => '🐧', 'lion' => '🦁',
                ];
            @endphp

            <div>
                @foreach($leaderboard as $loop_index => $entry)
                    @php
                        $rankColor = match($entry['rank']) {
                            1 => '#CA8A04',
                            2 => '#6B7280',
                            3 => '#D97706',
                            default => '#374151',
                        };
                        $total         = $entry['total_words'];
                        $masteredPct   = $total > 0 ? round($entry['mastered_count']   / $total * 100, 1) : 0;
                        $developingPct = $total > 0 ? round($entry['developing_count'] / $total * 100, 1) : 0;
                        $beginningPct  = $total > 0 ? round($entry['beginning_count']  / $total * 100, 1) : 0;
                        $isLast        = $loop_index === count($leaderboard) - 1;
                    @endphp
                    <div style="{{ $isLast ? '' : 'border-bottom:1px solid #f3f4f6;' }}">

                        {{-- Main row --}}
                        <div style="display:flex; align-items:center; gap:0.875rem; padding:0.75rem 0;">

                            {{-- Rank --}}
                            <div style="width:2rem; text-align:center; flex-shrink:0; font-size:0.875rem; font-weight:700; color:{{ $rankColor }};">
                                #{{ $entry['rank'] }}
                            </div>

                            {{-- Profile icon --}}
                            <div style="width:2.25rem; height:2.25rem; border-radius:9999px; background:#eff6ff; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; overflow:hidden;">
                                @if($entry['profile_icon'] && isset($iconEmoji[$entry['profile_icon']]))
                                    {{ $iconEmoji[$entry['profile_icon']] }}
                                @else
                                    <span style="color:#1e3a5f; font-weight:700; font-size:0.75rem;">{{ strtoupper(substr($entry['student_name'], 0, 1)) }}</span>
                                @endif
                            </div>

                            {{-- Name + proficiency bar --}}
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:0.875rem; font-weight:600; color:#111827; margin-bottom:0.3rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $entry['student_name'] }}
                                </div>
                                <div style="width:100%; height:6px; background:#f3f4f6; border-radius:9999px; overflow:hidden; display:flex;">
                                    @if($total > 0)
                                        <div style="width:{{ $masteredPct }}%; background:#22c55e; flex-shrink:0;"></div>
                                        <div style="width:{{ $developingPct }}%; background:#eab308; flex-shrink:0;"></div>
                                        <div style="width:{{ $beginningPct }}%; background:#ef4444; flex-shrink:0;"></div>
                                    @endif
                                </div>
                            </div>

                            {{-- Total score --}}
                            <div style="text-align:right; flex-shrink:0; min-width:3.5rem;">
                                <div style="font-size:0.9rem; font-weight:700; color:#1e3a5f;">{{ $entry['mastery_total'] }}</div>
                                <div style="font-size:0.65rem; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em;">pts</div>
                            </div>

                            {{-- Mastered badge --}}
                            <div style="flex-shrink:0;">
                                <span style="display:inline-flex; align-items:center; gap:0.2rem; padding:0.15rem 0.5rem; border-radius:9999px; font-size:0.75rem; font-weight:600; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;">
                                    &#10003; {{ $entry['mastered_count'] }}
                                </span>
                            </div>

                            {{-- See Words toggle --}}
                            <div style="flex-shrink:0;">
                                <button
                                    onclick="(function(btn){var el=document.getElementById('fwords-{{ $loop_index }}');var open=el.style.display==='none'||el.style.display==='';el.style.display=open?'block':'none';btn.textContent=open?'Hide':'See Words';})(this)"
                                    style="background-color:#0d9488; color:#ffffff; border:1px solid #0d9488; border-radius:6px; padding:4px 12px; font-size:0.8rem; cursor:pointer; font-weight:500; white-space:nowrap;">
                                    See Words
                                </button>
                            </div>

                        </div>

                        {{-- Frequent words detail panel --}}
                        <div id="fwords-{{ $loop_index }}" style="display:none; padding:0.5rem 0 0.75rem 5.5rem;">
                            @if(empty($entry['frequent_words']))
                                <p style="font-size:0.75rem; color:#9ca3af;">No activity recorded yet.</p>
                            @else
                                <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left; color:#6b7280; font-weight:500; padding:0 0.75rem 0.375rem 0;">English</th>
                                            <th style="text-align:left; color:#6b7280; font-weight:500; padding:0 0.75rem 0.375rem 0;">Filipino</th>
                                            <th style="text-align:left; color:#6b7280; font-weight:500; padding:0 0.75rem 0.375rem 0;">Category</th>
                                            <th style="text-align:left; color:#6b7280; font-weight:500; padding:0 0 0.375rem 0;">Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($entry['frequent_words'] as $word)
                                            @php
                                                $profStyle = match($word->proficiency_level ?? null) {
                                                    'Mastered'   => 'background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;',
                                                    'Developing' => 'background:#fef9c3; color:#854d0e; border:1px solid #fef08a;',
                                                    'Beginning'  => 'background:#fee2e2; color:#b91c1c; border:1px solid #fecaca;',
                                                    default      => 'background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb;',
                                                };
                                            @endphp
                                            <tr>
                                                <td style="padding:0.2rem 0.75rem 0.2rem 0; color:#111827; font-weight:500;">{{ $word->english_label }}</td>
                                                <td style="padding:0.2rem 0.75rem 0.2rem 0; color:#374151;">{{ $word->filipino_label }}</td>
                                                <td style="padding:0.2rem 0.75rem 0.2rem 0;">
                                                    <span style="padding:0.1rem 0.4rem; border-radius:9999px; font-size:0.65rem; font-weight:500; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;">{{ $word->category }}</span>
                                                </td>
                                                <td style="padding:0.2rem 0;">
                                                    <span style="padding:0.1rem 0.4rem; border-radius:9999px; font-size:0.65rem; font-weight:600; {{ $profStyle }}">{{ $word->proficiency_level ?? '—' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div style="display:flex; gap:1rem; margin-top:0.875rem; padding-top:0.75rem; border-top:1px solid #f3f4f6; font-size:0.7rem; color:#6b7280;">
                <span style="display:inline-flex; align-items:center; gap:0.3rem;">
                    <span style="width:0.7rem; height:0.5rem; background:#22c55e; border-radius:2px; display:inline-block;"></span> Mastered
                </span>
                <span style="display:inline-flex; align-items:center; gap:0.3rem;">
                    <span style="width:0.7rem; height:0.5rem; background:#eab308; border-radius:2px; display:inline-block;"></span> Developing
                </span>
                <span style="display:inline-flex; align-items:center; gap:0.3rem;">
                    <span style="width:0.7rem; height:0.5rem; background:#ef4444; border-radius:2px; display:inline-block;"></span> Beginning
                </span>
            </div>
        @endif

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
