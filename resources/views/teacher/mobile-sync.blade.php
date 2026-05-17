@extends('layouts.teacher')
@section('title', 'Mobile Sync')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page header --}}
    <div>
        <h2 style="font-size:1.5rem; font-weight:700; color:#111827; margin:0 0 0.25rem 0;">Mobile Sync</h2>
        <p style="font-size:0.875rem; color:#6b7280; margin:0;">Sync student progress data from the mobile app.</p>
    </div>

    {{-- Info card --}}
    <div style="background:#ffffff; border-radius:0.75rem; border:1px solid #f3f4f6;
                box-shadow:0 1px 3px rgba(0,0,0,0.06); padding:2rem; display:flex; align-items:flex-start; gap:1rem;">
        <div style="flex-shrink:0; width:40px; height:40px; border-radius:50%; background:#eff6ff;
                    display:flex; align-items:center; justify-content:center;">
            <i data-lucide="smartphone" style="width:20px; height:20px; color:#2563eb;"></i>
        </div>
        <div>
            <p style="font-size:0.9375rem; font-weight:600; color:#111827; margin:0 0 0.375rem 0;">Automatic Sync Enabled</p>
            <p style="font-size:0.875rem; color:#6b7280; margin:0; line-height:1.6;">
                Mobile sync is managed automatically when students use the iSpy World app.
                Data is pushed to the portal in real time.
            </p>
        </div>
    </div>

</div>
@endsection
