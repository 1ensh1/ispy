@php
    $allClasses    = $teacherAllClasses ?? collect();
    $activeClass   = $teacherActiveClass ?? null;
    $multipleClass = $allClasses->count() > 1;
@endphp

@if($multipleClass)
    {{-- Multi-class switcher --}}
    <div class="relative" id="class-switcher-container">
        <button
            onclick="toggleClassSwitcher(event)"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
        >
            <i data-lucide="layers" class="w-4 h-4 text-gray-400 shrink-0"></i>
            <span class="max-w-[140px] truncate">
                {{ $activeClass['class_name'] ?? 'Select Class' }}
            </span>
            @if($activeClass['is_sub'] ?? false)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 leading-none shrink-0">SUB</span>
            @endif
            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i>
        </button>

        <div id="class-switcher-dropdown"
             class="hidden absolute right-0 top-10 w-64 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
            <div class="px-3 py-2 border-b border-gray-100 bg-gray-50">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Switch Class</p>
            </div>
            <div class="py-1 max-h-72 overflow-y-auto">
                @foreach($allClasses as $cls)
                    @php $isActive = ($activeClass['id'] ?? null) === $cls['id']; @endphp
                    <form method="POST" action="{{ route('teacher.switch-class') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $cls['id'] }}">
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 text-left text-sm transition-colors
                                       {{ $isActive ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            {{-- Checkmark or spacer --}}
                            <span class="w-4 shrink-0 flex items-center justify-center">
                                @if($isActive)
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                @endif
                            </span>
                            <span class="flex-1 truncate font-medium">{{ $cls['class_name'] }}</span>
                            @if($cls['is_sub'])
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 leading-none shrink-0">SUB</span>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
@else
    {{-- Single class — plain text --}}
    @if($activeClass)
        <div class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600">
            <i data-lucide="layers" class="w-4 h-4 text-gray-400 shrink-0"></i>
            <span class="font-medium truncate max-w-[180px]">{{ $activeClass['class_name'] }}</span>
            @if($activeClass['is_sub'])
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 leading-none shrink-0">SUB</span>
            @endif
        </div>
    @endif
@endif
