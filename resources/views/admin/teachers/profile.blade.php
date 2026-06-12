<x-app-layout>
    <div class="p-6 max-w-5xl mx-auto">

        {{-- Back button --}}
        <div class="mb-6">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('new_class_pin'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="key" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">New PIN for {{ session('new_class_name') }}</p>
                        <p class="mt-1 font-mono text-xl font-bold text-amber-900 tracking-widest select-all">{{ session('new_class_pin') }}</p>
                        <p class="mt-1 text-xs text-amber-600">Share this PIN with the teacher to connect their class on the Android app.</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->has('unassign'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ $errors->first('unassign') }}</p>
            </div>
        @endif

        @if($errors->has('delete_class'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ $errors->first('delete_class') }}</p>
            </div>
        @endif

        @if($errors->has('class_list_id'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ $errors->first('class_list_id') }}</p>
            </div>
        @endif

        {{-- ===================== PROFILE CARD ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-[#2f5597] flex items-center justify-center shrink-0">
                    @if($teacher->profile_picture)
                        <img src="{{ $teacher->profile_picture }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <span class="text-white text-2xl font-bold select-none">
                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                        </span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $teacher->name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $teacher->user->email ?? '—' }}</p>
                </div>

                <div class="shrink-0">
                    <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-teal-500 text-white tracking-wide">
                        Active
                    </span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-4 pt-5 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Subject</p>
                    <p class="text-sm text-gray-700">
                        {{ $classes->flatMap(fn($c) => $c->teacherSubjects->pluck('subject'))->filter()->unique()->sort()->implode(', ') ?: '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Classes</p>
                    <p class="text-sm text-gray-700">{{ $classes->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Active Students</p>
                    <p class="text-sm text-gray-700">{{ $classes->sum(fn($c) => $c->activeStudents->count()) }}</p>
                </div>
            </div>
        </div>

        {{-- ===================== ASSIGNED CLASSES — Part A: Current classes ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="layout-list" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Assigned Classes</h2>
            </div>

            @if($classes->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400">
                    <i data-lucide="inbox" class="w-7 h-7 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">No classes assigned to this teacher.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Class Name</th>
                                <th class="px-6 py-3 font-medium">Subject</th>
                                <th class="px-6 py-3 font-medium">PIN</th>
                                <th class="px-6 py-3 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($classes as $class)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $class->class_name }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($class->teacherSubjects as $cs)
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $cs->subject }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-gray-600 tracking-widest">
                                            {{ $class->unified_classroom_pin ?? '—' }}
                                        </span>
                                        <form method="POST" action="{{ route('admin.classes.generatePin', $class->id) }}">
                                            @csrf
                                            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                            @if($class->unified_classroom_pin)
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 border border-gray-200 text-gray-600 hover:bg-gray-200 transition-colors">
                                                    Regenerate
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium rounded-md bg-[#2f5597] text-white hover:bg-blue-800 transition-colors">
                                                    Generate PIN
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST"
                                              action="{{ route('admin.classes.unassign', $class->id) }}"
                                              onsubmit="return confirm('Unassign this class from the teacher?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-md bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 transition-colors">
                                                Unassign
                                            </button>
                                        </form>
                                        <form method="POST"
                                              action="{{ route('admin.classes.archive', $class->id) }}"
                                              onsubmit="return confirm('Archive this class? It will be hidden but can be restored later.')">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors">
                                                Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ===================== ARCHIVED CLASSES ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <details>
                <summary class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2 cursor-pointer list-none select-none">
                    <i data-lucide="archive" class="w-4 h-4 text-gray-400"></i>
                    <h2 class="text-sm font-semibold text-gray-500">Archived Classes
                        @if($archivedClasses->isNotEmpty())
                            <span class="ml-1.5 inline-block px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500 font-medium">{{ $archivedClasses->count() }}</span>
                        @endif
                    </h2>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 ml-auto"></i>
                </summary>
                @if($archivedClasses->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-400">
                        <i data-lucide="archive" class="w-7 h-7 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">No archived classes for this teacher.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 font-medium">Class Name</th>
                                    <th class="px-6 py-3 font-medium">Subject</th>
                                    <th class="px-6 py-3 font-medium whitespace-nowrap">Archived On</th>
                                    <th class="px-6 py-3 font-medium text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($archivedClasses as $archived)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3 font-medium text-gray-700">{{ $archived->class_name }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($archived->teacherSubjects as $cs)
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                                    {{ $cs->subject }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-400 text-xs whitespace-nowrap">
                                        {{ $archived->subjects_archived_at ? \Carbon\Carbon::parse($archived->subjects_archived_at)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST"
                                              action="{{ route('admin.classes.restore', $archived->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-md bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 transition-colors">
                                                Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </details>
        </div>

        {{-- ===================== ASSIGNED CLASSES — Part B: Assign existing class ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="link" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Assign Existing Class</h2>
            </div>

            <div class="p-6">
                @if($unassignedClasses->isEmpty())
                    <p class="text-sm text-gray-400">No unassigned classes available.</p>
                @else
                    <form method="POST" action="{{ route('admin.classes.assign') }}" class="flex flex-wrap items-end gap-4">
                        @csrf
                        <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                        <div class="flex-1 min-w-48">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unassigned Class</label>
                            <select name="class_list_id" required
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                                <option value="">— Select a class —</option>
                                @foreach($unassignedClasses as $uc)
                                    <option value="{{ $uc->id }}" {{ old('class_list_id') == $uc->id ? 'selected' : '' }}>
                                        {{ $uc->class_name }} (available: {{ implode(', ', $uc->available_subjects) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject(s)</label>
                            <div class="flex items-center gap-4 h-[38px]">
                                <label class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                    <input type="checkbox" name="subjects[]" value="English"
                                           {{ is_array(old('subjects')) && in_array('English', old('subjects')) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-[#2f5597] focus:ring-[#2f5597]">
                                    English
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                    <input type="checkbox" name="subjects[]" value="Filipino"
                                           {{ is_array(old('subjects')) && in_array('Filipino', old('subjects')) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-[#2f5597] focus:ring-[#2f5597]">
                                    Filipino
                                </label>
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                    class="px-5 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                                Assign to Teacher
                            </button>
                        </div>
                    </form>
                    @error('subjects')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                @endif
            </div>
        </div>

        {{-- ===================== ASSIGNED CLASSES — Part C: Create and assign ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Create &amp; Assign New Class</h2>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.classes.create-assign') }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class Name</label>
                        <input type="text" name="class_name" required maxlength="255"
                               value="{{ old('class_name') }}"
                               placeholder="e.g. Sunflower 1"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>

                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <select name="subject" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="">— Select —</option>
                            <option value="English"  {{ old('subject') === 'English'  ? 'selected' : '' }}>English</option>
                            <option value="Filipino" {{ old('subject') === 'Filipino' ? 'selected' : '' }}>Filipino</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors">
                            Create &amp; Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== STUDENTS PER CLASS ===================== --}}
        @foreach($classes as $class)
            @if($class->activeStudents->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-gray-500"></i>
                    <h2 class="text-sm font-semibold text-gray-700">
                        Students — {{ $class->class_name }}
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Student</th>
                                <th class="px-6 py-3 font-medium">Parent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($class->activeStudents as $student)
                            @php
                                $iconEmoji = [
                                    'cat'     => '🐱', 'dog'     => '🐶',
                                    'bear'    => '🐻', 'rabbit'  => '🐰',
                                    'fox'     => '🦊', 'frog'    => '🐸',
                                    'penguin' => '🐧', 'lion'    => '🦁',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($student->profile_icon && isset($iconEmoji[$student->profile_icon]))
                                            <div class="w-7 h-7 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0 text-base leading-none">
                                                {{ $iconEmoji[$student->profile_icon] }}
                                            </div>
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0">
                                                <span class="text-[#2f5597] text-xs font-bold">
                                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        @endif
                                        <span class="font-medium text-gray-900">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-gray-500">
                                    {{ $student->parentUser->name ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach

        {{-- ===================== SUBSTITUTE ASSIGNMENTS ===================== --}}

        {{-- Flash for sub actions --}}
        @if($errors->has('substitute_teacher_id'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ $errors->first('substitute_teacher_id') }}</p>
            </div>
        @endif

        {{-- Part A: Active substitutes on this teacher's classes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Active Substitute Assignments</h2>
            </div>

            @if($currentSubs->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400">
                    <i data-lucide="users" class="w-7 h-7 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">No active substitute assignments for this teacher's classes.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">Class</th>
                                <th class="px-6 py-3 font-medium">Substitute Teacher</th>
                                <th class="px-6 py-3 font-medium">Start Date</th>
                                <th class="px-6 py-3 font-medium">End Date</th>
                                <th class="px-6 py-3 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($currentSubs as $sub)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-900">
                                    {{ $sub->classList->class_name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">
                                    {{ $sub->substituteTeacher->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-500">
                                    {{ \Carbon\Carbon::parse($sub->start_date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-3 text-gray-500">
                                    {{ $sub->end_date ? \Carbon\Carbon::parse($sub->end_date)->format('M d, Y') : 'Open-ended' }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form method="POST"
                                          action="{{ route('admin.substitutes.remove', $sub->id) }}"
                                          onsubmit="return confirm('Remove this substitute assignment?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium rounded-md bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 transition-colors">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Part B: Assign new substitute form --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Assign Substitute Teacher</h2>
            </div>

            <div class="p-6">
                @if($classes->isEmpty())
                    <p class="text-sm text-gray-400">This teacher has no classes to assign a substitute to.</p>
                @else
                    <form method="POST" action="{{ route('admin.substitutes.assign') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                                <select id="sub-class-select" name="class_list_id" required
                                        onchange="loadSubPreview(this.value)"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                                    <option value="">— Select a class —</option>
                                    @foreach($classes as $cls)
                                        <option value="{{ $cls->id }}" {{ old('class_list_id') == $cls->id ? 'selected' : '' }}>
                                            {{ $cls->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Substitute Teacher</label>
                                <select name="substitute_teacher_id" required
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                                    <option value="">— Select a teacher —</option>
                                    @foreach($otherTeachers as $ot)
                                        <option value="{{ $ot->id }}" {{ old('substitute_teacher_id') == $ot->id ? 'selected' : '' }}>
                                            {{ $ot->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="start_date" required
                                       value="{{ old('start_date') }}"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    End Date <span class="text-gray-400 font-normal">(optional — leave blank for open-ended)</span>
                                </label>
                                <input type="date" name="end_date"
                                       value="{{ old('end_date') }}"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit"
                                    class="px-5 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                                Assign Substitute
                            </button>
                        </div>
                    </form>

                    {{-- Live preview of existing subs for selected class --}}
                    <div id="active-subs-preview" class="mt-5 hidden">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                            Currently active substitutes for this class
                        </p>
                        <div class="rounded-lg border border-amber-200 bg-amber-50 overflow-hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-amber-100 text-amber-700">
                                    <tr>
                                        <th class="px-4 py-2 font-medium">Substitute Teacher</th>
                                        <th class="px-4 py-2 font-medium">Start Date</th>
                                        <th class="px-4 py-2 font-medium">End Date</th>
                                    </tr>
                                </thead>
                                <tbody id="active-subs-preview-body" class="divide-y divide-amber-100">
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===================== RECENT ACTIVITY ===================== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-gray-500"></i>
                <h2 class="text-sm font-semibold text-gray-700">Recent Activity</h2>
            </div>
            @if($recentActivity->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400">
                    <i data-lucide="clock" class="w-7 h-7 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">No activity recorded for this teacher.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium whitespace-nowrap">Date / Time</th>
                                <th class="px-6 py-3 font-medium">Action</th>
                                <th class="px-6 py-3 font-medium">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentActivity as $log)
                            @php
                                $badgeClass = match($log->action) {
                                    'login'   => 'bg-blue-100 text-blue-700',
                                    'create'  => 'bg-green-100 text-green-700',
                                    'update'  => 'bg-yellow-100 text-yellow-700',
                                    'delete'  => 'bg-red-100 text-red-700',
                                    'archive' => 'bg-orange-100 text-orange-700',
                                    'restore' => 'bg-teal-100 text-teal-700',
                                    'approve' => 'bg-emerald-100 text-emerald-700',
                                    'reject'  => 'bg-red-100 text-red-700',
                                    default   => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $log->created_at ? $log->created_at->format('M d, Y h:i A') : '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $log->description }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // Pre-load preview if class was pre-selected (e.g. after validation error)
            var preSelected = document.getElementById('sub-class-select');
            if (preSelected && preSelected.value) {
                loadSubPreview(preSelected.value);
            }
        });

        function loadSubPreview(classId) {
            var preview = document.getElementById('active-subs-preview');
            var tbody   = document.getElementById('active-subs-preview-body');
            if (!preview || !tbody) return;

            if (!classId) {
                preview.classList.add('hidden');
                return;
            }

            var url = '{{ route('admin.substitutes.list') }}?class_list_id=' + encodeURIComponent(classId);

            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                tbody.innerHTML = '';
                if (!data.length) {
                    preview.classList.add('hidden');
                    return;
                }
                data.forEach(function (row) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td class="px-4 py-2 text-amber-900 font-medium">' + escHtml(row.teacher_name) + '</td>' +
                        '<td class="px-4 py-2 text-amber-800">' + escHtml(row.start_date) + '</td>' +
                        '<td class="px-4 py-2 text-amber-800">' + escHtml(row.end_date) + '</td>';
                    tbody.appendChild(tr);
                });
                preview.classList.remove('hidden');
            })
            .catch(function () { preview.classList.add('hidden'); });
        }

        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    </script>
</x-app-layout>
