<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Parent Portal') — {{ config('app.name', 'iSpy World') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-sans antialiased text-gray-900" style="background:#f8f9fa;">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        @php
            $navItems = [
                ['label' => 'Dashboard',        'icon' => 'layout-dashboard', 'route' => 'parent.dashboard'],
                ['label' => 'Progress Review',  'icon' => 'bar-chart-3',      'route' => 'parent.progress'],
                ['label' => 'Proficiency',       'icon' => 'graduation-cap',   'route' => 'parent.proficiency'],
                ['label' => 'Messaging',         'icon' => 'message-square',   'route' => 'parent.messaging'],
                ['label' => 'Book Consultation', 'icon' => 'calendar-check',   'route' => 'parent.consultations'],
            ];
            $parentProfile       = \App\Models\ParentProfile::where('user_id', auth()->id())->first();
            $parentId            = $parentProfile?->id;
            $parentName          = $parentProfile?->name ?? auth()->user()->name;
            $parentNotifications = $parentId
                ? \Illuminate\Support\Facades\DB::table('notifications')
                    ->where('recipient_id', $parentId)
                    ->where('recipient_role', 'Parent')
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get()
                : collect();
            $parentUnreadCount = $parentNotifications->where('is_read', false)->count();
        @endphp

        <aside class="w-64 flex flex-col shrink-0 min-h-screen" style="background:#1a2332;">
            <div class="flex items-center gap-3 px-4 h-16 border-b border-white/10">
                <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center text-white font-bold text-sm shrink-0">iS</div>
                <div class="overflow-hidden whitespace-nowrap">
                    <h1 class="font-bold text-sm text-white leading-tight">iSpy World</h1>
                    <p class="text-[10px] text-gray-400">Parent Portal</p>
                </div>
            </div>

            <nav class="flex-1 py-4 px-2 space-y-1 overflow-y-auto">
                @foreach($navItems as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                              {{ $isActive ? 'text-white bg-white/10 font-medium' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="flex flex-1 flex-col min-w-0">

            {{-- Top bar --}}
            <header class="flex items-center justify-between h-16 px-6 border-b bg-white shrink-0">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                        Parent
                    </span>
                </div>
                <div class="flex items-center gap-4">

                    {{-- Notification Bell --}}
                    <div class="relative" id="parent-bell-container">
                        <button onclick="toggleParentBell(event)"
                                class="relative text-gray-500 hover:text-gray-700 transition-colors p-1">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            @if($parentUnreadCount > 0)
                                <span id="parent-notif-badge"
                                      data-count="{{ $parentUnreadCount }}"
                                      class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-amber-400 border-2 border-white text-gray-900 text-[9px] font-bold flex items-center justify-center leading-none">
                                    {{ $parentUnreadCount > 9 ? '9+' : $parentUnreadCount }}
                                </span>
                            @else
                                <span id="parent-notif-badge" data-count="0" class="hidden"></span>
                            @endif
                        </button>

                        {{-- Dropdown --}}
                        <div id="parent-bell-dropdown"
                             class="hidden absolute right-0 top-10 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">

                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                                <span class="text-sm font-semibold text-gray-900">Notifications</span>
                                <button onclick="parentMarkAllRead()"
                                        class="text-xs text-teal-600 hover:underline font-medium">
                                    Mark all as read
                                </button>
                            </div>

                            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                                @forelse($parentNotifications as $notif)
                                    @if($notif->action_url)
                                    <a href="{{ route('parent.notifications.redirect', $notif->id) }}"
                                       class="block w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors parent-notif-item
                                              {{ !$notif->is_read ? 'bg-amber-50/60' : '' }}">
                                        <p class="text-sm font-semibold text-gray-900 leading-tight truncate">{{ $notif->title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5 leading-snug line-clamp-2">
                                            {{ \Illuminate\Support\Str::limit($notif->message, 80) }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            {{ $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->diffForHumans() : '' }}
                                        </p>
                                    </a>
                                    @else
                                    <button onclick="parentMarkRead({{ $notif->id }}, this)"
                                            class="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors parent-notif-item
                                                   {{ !$notif->is_read ? 'bg-amber-50/60 parent-notif-unread' : '' }}">
                                        <p class="text-sm font-semibold text-gray-900 leading-tight truncate">{{ $notif->title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5 leading-snug line-clamp-2">
                                            {{ \Illuminate\Support\Str::limit($notif->message, 80) }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            {{ $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->diffForHumans() : '' }}
                                        </p>
                                    </button>
                                    @endif
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-gray-400">
                                        <i data-lucide="bell-off" class="w-6 h-6 mx-auto mb-1 opacity-40"></i>
                                        No notifications
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    {{-- Profile Dropdown --}}
                    <div class="relative pl-4 border-l border-gray-200" id="parent-profile-container">
                        <button onclick="toggleParentProfile(event)"
                                class="flex items-center gap-2.5 hover:opacity-80 transition-opacity cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center shrink-0">
                                <span class="text-teal-700 text-xs font-bold">{{ strtoupper(substr($parentName, 0, 1)) }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ $parentName }}</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 hidden sm:block"></i>
                        </button>

                        <div id="parent-profile-dropdown"
                             class="hidden absolute right-0 top-11 w-56 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $parentName }}</p>
                                <p class="text-xs text-gray-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('parent.profile') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    My Profile
                                </a>
                                <a href="{{ route('parent.portal.password') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i data-lucide="key-round" class="w-4 h-4 text-gray-400"></i>
                                    Change Password
                                </a>
                            </div>
                            <div class="border-t border-gray-100 py-1">
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
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

            <main class="flex-1 overflow-auto p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
    <script>
    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        window.toggleParentProfile = function (e) {
            e.stopPropagation();
            document.getElementById('parent-profile-dropdown').classList.toggle('hidden');
            document.getElementById('parent-bell-dropdown')?.classList.add('hidden');
        };

        document.addEventListener('click', function (e) {
            const container = document.getElementById('parent-profile-container');
            const dropdown  = document.getElementById('parent-profile-dropdown');
            if (dropdown && container && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        window.toggleParentBell = function (e) {
            e.stopPropagation();
            document.getElementById('parent-bell-dropdown').classList.toggle('hidden');
            document.getElementById('parent-profile-dropdown')?.classList.add('hidden');
        };

        document.addEventListener('click', function (e) {
            const container = document.getElementById('parent-bell-container');
            const dropdown  = document.getElementById('parent-bell-dropdown');
            if (dropdown && container && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        window.parentMarkRead = function (id, el) {
            if (!el.classList.contains('parent-notif-unread')) return;
            fetch('{{ route('parent.notifications.read', ['id' => '__ID__']) }}'.replace('__ID__', id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            }).then(function () {
                el.classList.remove('bg-amber-50/60', 'parent-notif-unread');
                _parentDecrBadge();
            });
        };

        window.parentMarkAllRead = function () {
            fetch('{{ route('parent.notifications.read-all') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            }).then(function () {
                document.querySelectorAll('.parent-notif-unread').forEach(function (el) {
                    el.classList.remove('bg-amber-50/60', 'parent-notif-unread');
                });
                const badge = document.getElementById('parent-notif-badge');
                if (badge) { badge.dataset.count = '0'; badge.classList.add('hidden'); }
            });
        };

        function _parentDecrBadge() {
            const badge = document.getElementById('parent-notif-badge');
            if (!badge) return;
            const n = Math.max(0, parseInt(badge.dataset.count || '0') - 1);
            badge.dataset.count = n;
            if (n <= 0) {
                badge.classList.add('hidden');
            } else {
                badge.classList.remove('hidden');
                badge.textContent = n > 9 ? '9+' : n;
            }
        }
    })();
    </script>
    @stack('scripts')
</body>
</html>
