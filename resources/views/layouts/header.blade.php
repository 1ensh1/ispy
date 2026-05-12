<header class="flex items-center justify-between h-16 px-6 border-b bg-white">
    <div>
        @php
            $role = auth()->user()->role ?? 'parent';
            $roleLabels = ['Admin' => 'Administrator', 'Teacher' => 'Teacher', 'Parent' => 'Parent',
                           'admin' => 'Administrator', 'teacher' => 'Teacher', 'parent' => 'Parent'];

            // Tailwind badge colors based on role
            $badgeClasses = [
                'Admin'   => 'bg-purple-100 text-purple-800 border border-purple-200',
                'Teacher' => 'bg-blue-100 text-blue-800 border border-blue-200',
                'Parent'  => 'bg-green-100 text-green-800 border border-green-200',
                'admin'   => 'bg-purple-100 text-purple-800 border border-purple-200',
                'teacher' => 'bg-blue-100 text-blue-800 border border-blue-200',
                'parent'  => 'bg-green-100 text-green-800 border border-green-200',
            ];
        @endphp
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $badgeClasses[$role] ?? 'bg-gray-100 text-gray-800 border border-gray-200' }}">
            {{ $roleLabels[$role] ?? ucfirst(strtolower($role)) }}
        </span>
    </div>
    
    <div class="flex items-center gap-4">
        <button class="text-gray-500 relative hover:text-gray-700 transition-colors">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-red-500 border-2 border-white"></span>
        </button>
        
        <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <i data-lucide="user" class="w-4 h-4 text-gray-500"></i>
            </div>
            <span class="text-sm font-medium text-gray-700 hidden sm:block">
                {{ auth()->user()->name }}
            </span>
        </div>
        
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</header>