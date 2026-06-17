<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meet the TamaTech Team — iSpy World</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="text-gray-800 bg-white" style="display: flex; flex-direction: column; min-height: 100vh;">

@php
    $intro   = $tamaTechContent->get('tamatech_intro');
    $members = collect(['tamatech_member_1', 'tamatech_member_2', 'tamatech_member_3', 'tamatech_member_4'])
        ->map(fn ($key) => $tamaTechContent->get($key))
        ->filter();
@endphp

{{-- ===================== NAVBAR ===================== --}}
<nav style="position: sticky; top: 0; z-index: 50; background-color: #1e3a5f; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="mx-auto px-6 flex items-center justify-between" style="max-width: 1200px; height: 72px;">
        <a href="{{ route('landing') }}" class="flex items-center shrink-0">
            <img src="{{ asset('images/fma-logo.png') }}" alt="Future Minds Academy" style="height: 48px; width: auto;">
        </a>
        <a href="{{ route('landing') }}"
           class="inline-flex items-center font-medium text-white rounded-lg"
           style="gap: 6px; background-color: #f5a623; padding: 10px 22px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
            Back to Home
        </a>
    </div>
</nav>

<main style="flex: 1;">
{{-- ===================== SECTION A — HERO / INTRO ===================== --}}
<section class="flex items-center justify-center text-center"
         style="background: linear-gradient(135deg, #1e3a5f 0%, #2f5597 55%, #1e3a5f 100%); padding-top: 64px; padding-bottom: 64px;">
    <div class="mx-auto px-6" style="max-width: 760px;">
        <img src="{{ asset('images/tamatech-logo.png') }}" alt="TamaTech"
             class="mx-auto rounded-full bg-white shadow-lg"
             style="width: 120px; height: 120px; object-fit: cover; padding: 8px;">
        <h1 class="text-white font-bold" style="font-size: 2.25rem; margin-top: 24px;">
            {{ $intro->title ?? 'Meet the Team' }}
        </h1>
        @if($intro && $intro->body)
            <p class="text-gray-200" style="margin-top: 16px; line-height: 1.7;">
                {{ $intro->body }}
            </p>
        @endif
    </div>
</section>

{{-- ===================== SECTION B — TEAM GRID ===================== --}}
<section style="padding-top: 64px; padding-bottom: 80px;">
    <div class="mx-auto px-6" style="max-width: 900px;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
            @foreach($members as $member)
                <div class="bg-gray-50 rounded-xl border border-gray-200 text-center flex flex-col items-center"
                     style="padding: 32px;">
                    <div class="flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-400 overflow-hidden"
                         style="width: 88px; height: 88px;">
                        @if($member->image_url)
                            <img src="{{ $member->image_url }}" alt="{{ $member->title }}"
                                 style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                        @else
                            <i data-lucide="user" style="width: 44px; height: 44px;"></i>
                        @endif
                    </div>
                    <h2 class="font-bold text-gray-900" style="margin-top: 20px; font-size: 1.15rem;">
                        {{ $member->title }}
                    </h2>
                    <p class="text-gray-500" style="margin-top: 6px;">
                        {{ $member->body }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>

</main>

{{-- ===================== FOOTER ===================== --}}
<footer style="background-color: #142844;" class="text-gray-300">
    <div class="mx-auto px-6 text-center text-sm text-gray-400"
         style="max-width: 1200px; padding-top: 20px; padding-bottom: 20px;">
        &copy; {{ date('Y') }} Future Minds Academy. All rights reserved. &nbsp;|&nbsp; Powered by iSpy World &nbsp;|&nbsp; Built by TamaTech<img src="{{ asset('images/tamatech-logo.png') }}" alt="TamaTech" style="display: inline; vertical-align: middle; width: 28px; height: 28px; border-radius: 50%; object-fit: cover; margin-left: 6px;">
    </div>
</footer>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
