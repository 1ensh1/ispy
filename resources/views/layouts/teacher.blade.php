<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Teacher Portal') — {{ config('app.name', 'iSpy World') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        @php
            $navItems = [
                ['label' => 'Dashboard',     'icon' => 'layout-dashboard', 'route' => 'teacher.dashboard'],
                ['label' => 'My Students',   'icon' => 'users',            'route' => 'teacher.students'],
                ['label' => 'Vocabulary',    'icon' => 'book-open',        'route' => 'teacher.vocabulary'],
                ['label' => 'Enrollment',    'icon' => 'clipboard-list',   'route' => 'teacher.enrollment'],
                ['label' => 'Classroom PIN', 'icon' => 'key-round',        'route' => 'teacher.pin'],
            ];
        @endphp

        <aside class="w-64 flex flex-col bg-gray-900 text-gray-300 border-r border-gray-800 shrink-0 min-h-screen">

            <div class="flex items-center gap-3 px-4 h-16 border-b border-gray-800">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                    iS
                </div>
                <div class="overflow-hidden whitespace-nowrap">
                    <h1 class="font-bold text-sm text-white leading-tight">iSpy World</h1>
                    <p class="text-[10px] text-gray-400">Teacher Portal</p>
                </div>
            </div>

            <nav class="flex-1 py-4 px-2 space-y-1 overflow-y-auto">
                @foreach($navItems as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ $isActive ? 'bg-indigo-600/10 text-indigo-400 font-medium' : 'hover:bg-gray-800 hover:text-white' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

        </aside>

        {{-- Main area --}}
        <div class="flex flex-1 flex-col min-w-0">

            {{-- Header --}}
            @php
                $teacherDisplayName = \App\Models\Teacher::where('user_id', auth()->id())->value('name')
                    ?? auth()->user()->name;
            @endphp
            <header class="flex items-center justify-between h-16 px-6 border-b bg-white shrink-0">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                        Teacher Portal
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <i data-lucide="user" class="w-4 h-4 text-gray-500"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ $teacherDisplayName }}</span>
                    </div>
                    <form method="POST" action="{{ route('teacher.logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Logout">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Flash banners --}}
            @if(session('success') || session('error'))
                <div class="px-6 pt-4 space-y-2">
                    @if(session('success'))
                        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <p class="text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <main class="flex-1 p-6 overflow-auto">
                @yield('content')
            </main>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
