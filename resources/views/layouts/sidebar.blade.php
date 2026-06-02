@php
    $role = auth()->user()->role ?? 'parent';
    $roleLabels = ['Admin' => 'Administrator', 'Teacher' => 'Teacher', 'Parent' => 'Parent',
                   'admin' => 'Administrator', 'teacher' => 'Teacher', 'parent' => 'Parent'];

    $navMap = [
        'admin' => [
            ['label' => 'Dashboard',          'icon' => 'layout-dashboard', 'path' => 'admin/dashboard'],
            ['label' => 'User Accounts',      'icon' => 'users',            'path' => 'admin/users'],
            ['label' => 'Vocabulary Library',    'icon' => 'book-open',    'path' => 'admin/vocabulary'],
            ['label' => 'Vocab Suggestions',     'icon' => 'lightbulb',    'path' => 'admin/vocabulary-suggestions'],
            ['label' => 'Bilingual Assets',   'icon' => 'file-audio',       'path' => 'admin/assets'],
            ['label' => 'Data Sync',          'icon' => 'refresh-cw',       'path' => 'admin/sync'],
            ['label' => 'System Snapshots',   'icon' => 'camera',           'path' => 'admin/snapshots'],
            ['label' => 'Access Control',     'icon' => 'shield-check',     'path' => 'admin/access'],
            ['label' => 'Activity Logs',      'icon' => 'clipboard-list',   'path' => 'admin/activity-logs'],
            ['label' => 'Consultations',      'icon' => 'calendar-check',   'path' => 'admin/consultations'],
            ['label' => 'Reports',            'icon' => 'bar-chart-2',      'path' => 'admin/reports'],
        ],
        'teacher' => [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'path' => 'teacher/dashboard'],
            ['label' => 'Student Progress', 'icon' => 'bar-chart-3', 'path' => 'teacher/progress'],
            ['label' => 'Consultation Availability', 'icon' => 'calendar-check', 'path' => 'teacher/availability'],
            ['label' => 'Word Sets', 'icon' => 'book-open', 'path' => 'teacher/wordsets'],
        ],
        'parent' => [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'path' => 'parent/dashboard'],
            ['label' => 'Progress Review', 'icon' => 'bar-chart-3', 'path' => 'parent/progress'],
            ['label' => 'Book Consultation', 'icon' => 'calendar-check', 'path' => 'parent/schedule'],
        ]
    ];

    $items = $navMap[$role] ?? $navMap[strtolower($role)] ?? $navMap['parent'];
@endphp

<aside x-data="{ collapsed: false }" 
       :class="collapsed ? 'w-16' : 'w-64'" 
       class="flex flex-col bg-gray-900 text-gray-300 border-r border-gray-800 transition-all duration-300 shrink-0 min-h-screen relative">
    
    <div class="flex items-center gap-3 px-4 h-16 border-b border-gray-800">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
            iS
        </div>
        <div x-show="!collapsed" class="overflow-hidden whitespace-nowrap">
            <h1 class="font-bold text-sm text-white leading-tight">iSpy World</h1>
            <p class="text-[10px] text-gray-400">{{ $roleLabels[$role] ?? ucfirst(strtolower($role)) }} Portal</p>
        </div>
    </div>

    <nav class="flex-1 py-4 px-2 space-y-1 overflow-y-auto">
        @foreach($items as $item)
            @php
                // Checks if the current URL matches the path to highlight it
                $isActive = request()->is($item['path'] . '*');
            @endphp
            <a href="{{ url($item['path']) }}" 
               class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ $isActive ? 'bg-indigo-600/10 text-indigo-400 font-medium' : 'hover:bg-gray-800 hover:text-white' }}"
               :title="collapsed ? '{{ $item['label'] }}' : ''">
                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
                <span x-show="!collapsed" class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <button @click="collapsed = !collapsed" 
            class="absolute -right-3 top-20 flex items-center justify-center w-6 h-6 bg-gray-800 border border-gray-700 rounded-full text-gray-400 hover:text-white transition-colors">
        <i :data-lucide="collapsed ? 'chevron-right' : 'chevron-left'" class="w-4 h-4"></i>
    </button>
</aside>