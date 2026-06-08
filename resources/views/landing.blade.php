<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>iSpy World — Future Minds Academy</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', system-ui, -apple-system, sans-serif; }
        .cms-cta { transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease; }
        .cms-cta:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,.2); }
    </style>
</head>
<body class="text-gray-800 bg-white">

@php
    $hero        = $cms->get('hero');
    $aboutSchool = $cms->get('about_school');
    $aboutApp    = $cms->get('about_app');
    $howTo       = $cms->get('how_to_download');
    $apk         = $cms->get('apk_download');

    $heroPub = $hero && $hero->is_published;
@endphp

{{-- ===================== SECTION 1 — NAVBAR ===================== --}}
<nav style="position: sticky; top: 0; z-index: 50; background-color: #1e3a5f; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="mx-auto px-6 flex items-center justify-between" style="max-width: 1200px; height: 72px;">
        <div class="flex items-center shrink-0">
            <img src="{{ asset('images/fma-logo.png') }}" alt="Future Minds Academy" style="height: 48px; width: auto;">
        </div>
        <div class="flex items-center gap-8">
            <a href="#about"         class="font-medium hover:opacity-80 transition-opacity" style="color: #e5e7eb; font-size: 0.95rem;">About</a>
            <a href="#download"      class="font-medium hover:opacity-80 transition-opacity" style="color: #e5e7eb; font-size: 0.95rem;">Download</a>
            <a href="#announcements" class="font-medium hover:opacity-80 transition-opacity" style="color: #e5e7eb; font-size: 0.95rem;">Announcements</a>
        </div>
        <div class="shrink-0">
            <a href="{{ route('login') }}"
               class="cms-cta inline-block font-semibold text-white rounded-lg"
               style="background-color: #f5a623; padding: 10px 22px;">
                Login
            </a>
        </div>
    </div>
</nav>

{{-- ===================== SECTION 2 — HERO / BANNER ===================== --}}
<section id="hero" class="relative flex items-center justify-center"
         style="min-height: 90vh;
                @if($heroPub && $hero->image_url)
                    background-image: url('{{ $hero->image_url }}'); background-size: cover; background-position: center; background-repeat: no-repeat;
                @else
                    background: linear-gradient(135deg, #1e3a5f 0%, #2f5597 55%, #1e3a5f 100%);
                @endif">
    <div style="position: absolute; inset: 0; background-color: rgba(15,28,52,0.66);"></div>

    <div class="relative text-center px-6" style="max-width: 820px; z-index: 1;">
        <h1 class="text-white font-extrabold leading-tight"
            style="font-size: clamp(2.2rem, 5vw, 3.6rem); text-shadow: 0 2px 18px rgba(0,0,0,0.35);">
            {{ $heroPub && $hero->title ? $hero->title : 'Welcome to iSpy World' }}
        </h1>
        <p class="mx-auto mt-5 text-gray-100" style="max-width: 640px; font-size: clamp(1rem, 2vw, 1.2rem); line-height: 1.7;">
            {{ $heroPub && $hero->body ? $hero->body : 'A bilingual, AI-powered learning adventure that helps young learners discover the world around them — one word at a time.' }}
        </p>
        <div class="mt-9">
            <a href="#download" class="cms-cta inline-block font-bold text-white rounded-full"
               style="background-color: #f5a623; padding: 15px 38px; font-size: 1.05rem;">
                Download the App
            </a>
        </div>
    </div>
</section>

{{-- ===================== SECTION 3 — ABOUT THE SCHOOL ===================== --}}
@if($aboutSchool && $aboutSchool->is_published)
<section id="about" style="background-color: #1e3a5f;" class="text-white">
    <div class="mx-auto px-6 flex items-center" style="max-width: 1200px; padding-top: 84px; padding-bottom: 84px; flex-wrap: wrap; gap: 52px;">
        <div style="flex: 1 1 380px;">
            <span class="inline-block rounded-full text-xs font-semibold uppercase tracking-widest"
                  style="background-color: rgba(245,166,35,0.18); color: #f5a623; padding: 6px 14px;">Our School</span>
            <h2 class="font-extrabold mt-4" style="font-size: clamp(1.8rem, 3.5vw, 2.6rem);">
                {{ $aboutSchool->title ?: 'About Future Minds Academy' }}
            </h2>
            <p class="mt-5 text-gray-200" style="font-size: 1.05rem; line-height: 1.85;">
                {{ $aboutSchool->body ?: 'Future Minds Academy is dedicated to nurturing curious, confident, and capable young learners through a warm, play-based, and bilingual learning environment.' }}
            </p>
        </div>
        <div style="flex: 1 1 380px;">
            @if($aboutSchool->image_url)
                <img src="{{ $aboutSchool->image_url }}" alt="Future Minds Academy"
                     style="width: 100%; max-height: 420px; object-fit: cover; border-radius: 18px; box-shadow: 0 18px 40px rgba(0,0,0,0.35);">
            @else
                <div class="flex items-center justify-center"
                     style="width: 100%; height: 340px; border-radius: 18px; background: linear-gradient(135deg, #2f5597, #244a82); box-shadow: 0 18px 40px rgba(0,0,0,0.35);">
                    <span style="font-size: 3.6rem; font-weight: 800; color: #f5a623; letter-spacing: 4px;">FMA</span>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ===================== SECTION 4 — ABOUT iSpy WORLD ===================== --}}
@if($aboutApp && $aboutApp->is_published)
<section style="background-color: #ffffff;" class="text-gray-800">
    <div class="mx-auto px-6 flex items-center" style="max-width: 1200px; padding-top: 84px; padding-bottom: 84px; flex-wrap: wrap; gap: 52px;">
        <div style="flex: 1 1 380px;">
            @if($aboutApp->image_url)
                <img src="{{ $aboutApp->image_url }}" alt="iSpy World app"
                     style="width: 100%; max-height: 420px; object-fit: cover; border-radius: 18px; box-shadow: 0 14px 36px rgba(30,58,95,0.18);">
            @else
                <div class="flex items-center justify-center"
                     style="width: 100%; height: 340px; border-radius: 18px; background: linear-gradient(135deg, #f5a623, #f6b850); box-shadow: 0 14px 36px rgba(245,166,35,0.28);">
                    <span style="font-size: 3rem; font-weight: 800; color: #ffffff; letter-spacing: 2px;">iSpy</span>
                </div>
            @endif
        </div>
        <div style="flex: 1 1 380px;">
            <span class="inline-block rounded-full text-xs font-semibold uppercase tracking-widest"
                  style="background-color: rgba(30,58,95,0.08); color: #1e3a5f; padding: 6px 14px;">The App</span>
            <h2 class="font-extrabold mt-4" style="font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #1e3a5f;">
                {{ $aboutApp->title ?: 'About iSpy World' }}
            </h2>
            <p class="mt-5 text-gray-600" style="font-size: 1.05rem; line-height: 1.85;">
                {{ $aboutApp->body ?: 'iSpy World turns everyday surroundings into a playground for language learning. Using on-device object detection, children point their camera at real objects and learn their names in both English and Filipino.' }}
            </p>
            <div class="flex mt-7" style="flex-wrap: wrap; gap: 12px;">
                @foreach(['Bilingual Learning', 'Object Detection', 'Progress Tracking'] as $feature)
                    <span class="inline-block rounded-full font-semibold"
                          style="background-color: rgba(245,166,35,0.16); color: #b9730a; padding: 9px 18px; font-size: 0.9rem;">
                        {{ $feature }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===================== SECTION 5 — HOW TO DOWNLOAD ===================== --}}
@if($howTo && $howTo->is_published)
<section style="background-color: #f3f4f6;" class="text-gray-800">
    <div class="mx-auto px-6 text-center" style="max-width: 1100px; padding-top: 84px; padding-bottom: 84px;">
        <h2 class="font-extrabold" style="font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #1e3a5f;">
            {{ $howTo->title ?: 'How to Download' }}
        </h2>
        <p class="mx-auto mt-4 text-gray-600" style="max-width: 680px; font-size: 1.05rem; line-height: 1.8;">
            {{ $howTo->body ?: 'Getting started with iSpy World takes just a few minutes. Follow these simple steps to install the app on your Android device.' }}
        </p>

        <div class="flex justify-center mt-12" style="flex-wrap: wrap; gap: 22px;">
            @php
                $steps = [
                    'Download the APK file below',
                    "Enable 'Install from Unknown Sources' on your Android device",
                    'Open the downloaded file and tap Install',
                    'Launch iSpy World and enter your credentials',
                ];
            @endphp
            @foreach($steps as $i => $step)
                <div class="bg-white text-left" style="flex: 1 1 220px; max-width: 250px; border-radius: 16px; padding: 26px 24px; box-shadow: 0 8px 24px rgba(30,58,95,0.08); border: 1px solid #e5e7eb;">
                    <div class="flex items-center justify-center font-extrabold text-white"
                         style="width: 44px; height: 44px; border-radius: 12px; background-color: #f5a623; font-size: 1.25rem;">
                        {{ $i + 1 }}
                    </div>
                    <p class="mt-4 font-medium text-gray-700" style="line-height: 1.6;">{{ $step }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ===================== SECTION 6 — APK DOWNLOAD ===================== --}}
@if($apk && $apk->is_published)
<section id="download" style="background: linear-gradient(135deg, #1e3a5f 0%, #244a82 100%);" class="text-white">
    <div class="mx-auto px-6 text-center" style="max-width: 760px; padding-top: 88px; padding-bottom: 88px;">
        <h2 class="font-extrabold" style="font-size: clamp(1.9rem, 4vw, 2.8rem);">
            {{ $apk->title ?: 'Download iSpy World' }}
        </h2>
        <p class="mx-auto mt-5 text-gray-200" style="max-width: 600px; font-size: 1.08rem; line-height: 1.8;">
            {{ $apk->body ?: 'Install the latest version of iSpy World on your Android device and begin the learning adventure today.' }}
        </p>

        <div class="mt-10">
            @if($apk->file_url)
                <a href="{{ $apk->file_url }}" download
                   class="cms-cta inline-flex items-center gap-2 font-bold text-white rounded-full"
                   style="background-color: #f5a623; padding: 17px 44px; font-size: 1.1rem;">
                    ⬇ Download APK
                </a>
            @else
                <span class="inline-flex items-center gap-2 font-bold rounded-full"
                      style="background-color: #6b7280; color: #d1d5db; padding: 17px 44px; font-size: 1.1rem; cursor: not-allowed;">
                    APK Coming Soon
                </span>
            @endif
        </div>

        @if($apk->image_url)
            <div class="mt-6">
                <a href="{{ $apk->image_url }}" download
                   class="cms-cta inline-block font-semibold rounded-full"
                   style="background-color: transparent; color: #f5a623; border: 2px solid #f5a623; padding: 12px 30px;">
                    📄 User Manual
                </a>
            </div>
        @endif
    </div>
</section>
@endif

{{-- ===================== SECTION 7 — ANNOUNCEMENTS ===================== --}}
<section id="announcements" style="background-color: #ffffff;" class="text-gray-800">
    <div class="mx-auto px-6" style="max-width: 1200px; padding-top: 84px; padding-bottom: 84px;">
        <div class="text-center">
            <h2 class="font-extrabold" style="font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #1e3a5f;">
                Latest Announcements
            </h2>
            <p class="mx-auto mt-3 text-gray-500" style="max-width: 560px;">News and updates from Future Minds Academy.</p>
        </div>

        @if($announcements->isEmpty())
            <div class="text-center mt-12">
                <p class="text-gray-400" style="font-size: 1.05rem;">No announcements at this time.</p>
            </div>
        @else
            <div class="flex justify-center mt-12" style="flex-wrap: wrap; gap: 26px;">
                @foreach($announcements as $a)
                    <article class="bg-white overflow-hidden"
                             style="flex: 1 1 320px; max-width: 360px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 8px 24px rgba(30,58,95,0.08);">
                        @if($a->image_url)
                            <img src="{{ $a->image_url }}" alt="{{ $a->title }}"
                                 style="width: 100%; height: 180px; object-fit: cover;">
                        @else
                            <div class="flex items-center justify-center" style="width: 100%; height: 180px; background-color: #1e3a5f;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15M18 13a3 3 0 1 0 0-6M5.436 13.683A4.001 4.001 0 0 1 7 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 0 1-1.564-.317z"/>
                                </svg>
                            </div>
                        @endif
                        <div style="padding: 22px 24px;">
                            @if($a->published_at)
                                <p class="text-xs font-semibold uppercase tracking-wider" style="color: #f5a623;">
                                    {{ $a->published_at->format('F d, Y') }}
                                </p>
                            @endif
                            <h3 class="font-bold mt-2" style="font-size: 1.2rem; color: #1e3a5f;">{{ $a->title }}</h3>
                            <p class="mt-3 text-gray-600"
                               style="font-size: 0.95rem; line-height: 1.65; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                {{ $a->body }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ===================== SECTION 8 — FOOTER ===================== --}}
<footer style="background-color: #142844;" class="text-gray-300">
    <div class="mx-auto px-6 flex" style="max-width: 1200px; padding-top: 56px; padding-bottom: 40px; flex-wrap: wrap; gap: 40px;">
        <div style="flex: 1 1 280px;">
            <div class="flex items-center" style="gap: 14px;">
                <img src="{{ asset('images/fma-logo.png') }}" alt="Future Minds Academy" style="height: 52px; width: auto;">
                <div>
                    <p class="font-bold text-white" style="font-size: 1.05rem;">Future Minds Academy</p>
                    <p class="text-sm text-gray-400">Mandaluyong City, Philippines</p>
                </div>
            </div>
        </div>

        <div style="flex: 1 1 200px;">
            <p class="font-semibold text-white mb-3">Quick Links</p>
            <ul class="space-y-2 text-sm">
                <li><a href="#about" class="hover:text-white" style="transition: color .15s;">About</a></li>
                <li><a href="#download" class="hover:text-white" style="transition: color .15s;">Download</a></li>
                <li><a href="#announcements" class="hover:text-white" style="transition: color .15s;">Announcements</a></li>
            </ul>
        </div>

        <div style="flex: 1 1 240px;" class="md:text-right">
            <p class="font-semibold text-white mb-3">Future Minds Academy</p>
            <p class="font-bold" style="color: #f5a623; font-size: 1.1rem;">Education Par Excellence!</p>
        </div>
    </div>

    <div style="border-top: 1px solid rgba(255,255,255,0.08);">
        <div class="mx-auto px-6 text-center text-sm text-gray-400" style="max-width: 1200px; padding-top: 20px; padding-bottom: 20px;">
            &copy; 2026 Future Minds Academy. All rights reserved. &nbsp;|&nbsp; Powered by iSpy World
        </div>
    </div>
</footer>

</body>
</html>
