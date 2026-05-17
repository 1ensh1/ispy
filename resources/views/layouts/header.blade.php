@php
    $role = auth()->user()->role ?? 'parent';
    $roleLabels = ['Admin' => 'Administrator', 'Teacher' => 'Teacher', 'Parent' => 'Parent',
                   'admin' => 'Administrator', 'teacher' => 'Teacher', 'parent' => 'Parent'];
    $badgeClasses = [
        'Admin'   => 'bg-purple-100 text-purple-800 border border-purple-200',
        'Teacher' => 'bg-blue-100 text-blue-800 border border-blue-200',
        'Parent'  => 'bg-green-100 text-green-800 border border-green-200',
        'admin'   => 'bg-purple-100 text-purple-800 border border-purple-200',
        'teacher' => 'bg-blue-100 text-blue-800 border border-blue-200',
        'parent'  => 'bg-green-100 text-green-800 border border-green-200',
    ];

    $adminDisplayName = \App\Models\Administrator::where('user_id', auth()->id())->value('name')
        ?? auth()->user()->name;

    $adminUnreadCount = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('recipient_id', auth()->id())
        ->where('recipient_role', 'Admin')
        ->where('is_read', false)
        ->count();

    $adminNotifications = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('recipient_id', auth()->id())
        ->where('recipient_role', 'Admin')
        ->orderByDesc('created_at')
        ->limit(10)
        ->get();
@endphp

<header class="flex items-center justify-between h-16 px-6 border-b bg-white">
    <div>
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $badgeClasses[$role] ?? 'bg-gray-100 text-gray-800 border border-gray-200' }}">
            {{ $roleLabels[$role] ?? ucfirst(strtolower($role)) }}
        </span>
    </div>

    <div class="flex items-center gap-4">

        {{-- Notification Bell --}}
        <div class="relative" id="admin-bell-container">
            <button onclick="toggleAdminBell(event)"
                    class="relative text-gray-500 hover:text-gray-700 transition-colors p-1">
                <i data-lucide="bell" class="w-5 h-5"></i>
                @if($adminUnreadCount > 0)
                    <span id="admin-notif-badge"
                          data-count="{{ $adminUnreadCount }}"
                          class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 border-2 border-white text-white text-[9px] font-bold flex items-center justify-center leading-none">
                        {{ $adminUnreadCount > 9 ? '9+' : $adminUnreadCount }}
                    </span>
                @else
                    <span id="admin-notif-badge" data-count="0" class="hidden"></span>
                @endif
            </button>

            {{-- Dropdown --}}
            <div id="admin-bell-dropdown"
                 class="hidden absolute right-0 top-10 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">

                {{-- Dropdown header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <span class="text-sm font-semibold text-gray-900">Notifications</span>
                    <button onclick="adminMarkAllRead()"
                            class="text-xs text-[#2f5597] hover:underline font-medium">
                        Mark all as read
                    </button>
                </div>

                {{-- Items --}}
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($adminNotifications as $notif)
                        @if($notif->action_url)
                        <a href="{{ route('admin.notifications.redirect', $notif->id) }}"
                           class="block w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors admin-notif-item
                                  {{ !$notif->is_read ? 'bg-blue-50/60' : '' }}">
                            <p class="text-sm font-semibold text-gray-900 leading-tight truncate">{{ $notif->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 leading-snug line-clamp-2">
                                {{ \Illuminate\Support\Str::limit($notif->message, 80) }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                {{ $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->diffForHumans() : '' }}
                            </p>
                        </a>
                        @else
                        <button onclick="adminMarkRead({{ $notif->id }}, this)"
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors admin-notif-item
                                       {{ !$notif->is_read ? 'bg-blue-50/60 admin-notif-unread' : '' }}">
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
        <div class="relative pl-4 border-l border-gray-200" id="admin-profile-container">
            <button onclick="toggleAdminProfile(event)"
                    class="flex items-center gap-2.5 hover:opacity-80 transition-opacity cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                    <i data-lucide="user" class="w-4 h-4 text-gray-500"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ $adminDisplayName }}</span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 hidden sm:block"></i>
            </button>

            <div id="admin-profile-dropdown"
                 class="hidden absolute right-0 top-11 w-56 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $adminDisplayName }}</p>
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('admin.profile') }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                        My Profile
                    </a>
                    <a href="{{ route('admin.password') }}"
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

<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    window.toggleAdminProfile = function (e) {
        e.stopPropagation();
        document.getElementById('admin-profile-dropdown').classList.toggle('hidden');
        document.getElementById('admin-bell-dropdown').classList.add('hidden');
    };

    document.addEventListener('click', function (e) {
        const container = document.getElementById('admin-profile-container');
        const dropdown  = document.getElementById('admin-profile-dropdown');
        if (dropdown && container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    window.toggleAdminBell = function (e) {
        e.stopPropagation();
        document.getElementById('admin-bell-dropdown').classList.toggle('hidden');
    };

    document.addEventListener('click', function (e) {
        const container = document.getElementById('admin-bell-container');
        const dropdown  = document.getElementById('admin-bell-dropdown');
        if (dropdown && container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    window.adminMarkRead = function (id, el) {
        if (!el.classList.contains('admin-notif-unread')) return;
        fetch('{{ route('admin.notifications.read', ['id' => '__ID__']) }}'.replace('__ID__', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).then(function () {
            el.classList.remove('bg-blue-50/60', 'admin-notif-unread');
            _adminDecrBadge();
        });
    };

    window.adminMarkAllRead = function () {
        fetch('{{ route('admin.notifications.read-all') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).then(function () {
            document.querySelectorAll('.admin-notif-unread').forEach(function (el) {
                el.classList.remove('bg-blue-50/60', 'admin-notif-unread');
            });
            const badge = document.getElementById('admin-notif-badge');
            if (badge) { badge.dataset.count = '0'; badge.classList.add('hidden'); }
        });
    };

    function _adminDecrBadge() {
        const badge = document.getElementById('admin-notif-badge');
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
