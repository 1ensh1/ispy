<x-app-layout>
    @php
        $studentCap = 20;
    @endphp

    <div class="max-w-7xl mx-auto">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Manage Classes</h1>
                <p class="text-sm text-gray-500">{{ $totalActive }} active {{ \Illuminate\Support\Str::plural('class', $totalActive) }}. Classes are managed independently of teachers.</p>
            </div>
            <button onclick="openAddModal()"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i data-lucide="plus" style="width:16px;height:16px;"></i>
                Add Class
            </button>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 mb-5 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if($errors->any())
            <div class="flex items-start gap-3 p-4 mb-5 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <ul class="text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Toolbar: Show Archived toggle --}}
        <div class="flex items-center justify-between mb-4">
            <div></div>
            @if($showArchived)
                <a href="{{ route('admin.classes.index') }}"
                   class="flex items-center gap-2 px-3 py-2 text-sm font-medium border border-indigo-200 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                    <i data-lucide="eye-off" style="width:16px;height:16px;"></i>
                    Hide Archived
                </a>
            @else
                <a href="{{ route('admin.classes.index', ['show_archived' => 1]) }}"
                   class="flex items-center gap-2 px-3 py-2 text-sm font-medium border border-gray-200 bg-white text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                    <i data-lucide="archive" style="width:16px;height:16px;"></i>
                    Show Archived
                </a>
            @endif
        </div>

        {{-- Classes Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Class Name</th>
                            <th class="px-4 py-3 font-medium">PIN</th>
                            <th class="px-4 py-3 font-medium">Subjects</th>
                            <th class="px-4 py-3 font-medium">Teachers</th>
                            <th class="px-4 py-3 font-medium">Students</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($classes as $class)
                            @php
                                $isArchived = $class->archived_at !== null;
                                $subjects = $class->classSubjects->pluck('subject')->filter()->unique()->values();
                                $teachers = $class->classSubjects->pluck('teacher.name')->filter()->unique()->values();
                                $studentCount = $class->active_students_count ?? 0;
                            @endphp
                            <tr class="transition-colors {{ $isArchived ? 'bg-gray-50/80 text-gray-500' : 'hover:bg-gray-50' }}">
                                <td class="px-4 py-3 font-medium {{ $isArchived ? 'text-gray-500' : 'text-gray-900' }}">
                                    {{ $class->class_name }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $class->unified_classroom_pin }}</td>
                                <td class="px-4 py-3">
                                    @if($subjects->isNotEmpty())
                                        {{ $subjects->implode(', ') }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($teachers->isNotEmpty())
                                        {{ $teachers->implode(', ') }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="{{ $studentCount >= $studentCap ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                                        {{ $studentCount }} / {{ $studentCap }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($isArchived)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">Archived</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div style="display:inline-flex; align-items:center; gap:8px;">
                                        @if($isArchived)
                                            <form method="POST" action="{{ route('admin.classes.manage-restore', $class->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium text-green-700 border border-green-200 rounded-lg hover:bg-green-50 transition-colors">
                                                    Restore
                                                </button>
                                            </form>
                                        @else
                                            <button onclick="openEditModal(this)"
                                                    data-id="{{ $class->id }}"
                                                    data-name="{{ $class->class_name }}"
                                                    class="px-3 py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.classes.manage-archive', $class->id) }}"
                                                  onsubmit="return confirm('Archive this class? All subject assignments will also be archived.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                                    Archive
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-16 text-center">
                                    <i data-lucide="layout-list" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
                                    <p class="text-sm text-gray-400">No classes yet. Click “Add Class” to create one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ── Add Class Modal ── --}}
    <div id="add-modal"
         class="fixed inset-0 flex items-center justify-center z-50 hidden"
         style="background: rgba(0,0,0,0.5);">
        <div class="bg-white rounded-xl shadow-xl p-6" style="width: 460px; max-width: 95vw;">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">Add Class</h2>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class Name <span class="text-red-500">*</span></label>
                    <input type="text" name="class_name" maxlength="50" required autofocus
                           value="{{ old('_edit_id') ? '' : old('class_name') }}"
                           placeholder="e.g. TW33"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <p class="mt-1 text-xs text-gray-400">A 6-digit classroom PIN is generated automatically.</p>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:4px;">
                    <button type="button" onclick="closeAddModal()"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Create Class
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Edit Class Modal ── --}}
    <div id="edit-modal"
         class="fixed inset-0 flex items-center justify-center z-50 hidden"
         style="background: rgba(0,0,0,0.5);">
        <div class="bg-white rounded-xl shadow-xl p-6" style="width: 460px; max-width: 95vw;">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">Edit Class</h2>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="edit-form" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_edit_id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-class-name" name="class_name" maxlength="50" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:4px;">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const updateUrlTemplate = "{{ route('admin.classes.update', ['id' => '__ID__']) }}";

        function refreshIcons() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        window.openAddModal = function () {
            document.getElementById('add-modal').classList.remove('hidden');
            refreshIcons();
        };
        window.closeAddModal = function () {
            document.getElementById('add-modal').classList.add('hidden');
        };

        window.openEditModal = function (btn) {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const form = document.getElementById('edit-form');
            form.action = updateUrlTemplate.replace('__ID__', id);
            form.querySelector('input[name="_edit_id"]').value = id;
            document.getElementById('edit-class-name').value = name;
            document.getElementById('edit-modal').classList.remove('hidden');
            refreshIcons();
        };
        window.closeEditModal = function () {
            document.getElementById('edit-modal').classList.add('hidden');
        };

        document.getElementById('add-modal').addEventListener('click', function (e) {
            if (e.target === this) window.closeAddModal();
        });
        document.getElementById('edit-modal').addEventListener('click', function (e) {
            if (e.target === this) window.closeEditModal();
        });

        document.addEventListener('DOMContentLoaded', function () {
            refreshIcons();

            // Re-open the relevant modal after a validation error.
            @if($errors->any() && old('_edit_id'))
                const editBtn = document.querySelector('button[data-id="{{ old('_edit_id') }}"]');
                const form = document.getElementById('edit-form');
                form.action = updateUrlTemplate.replace('__ID__', '{{ old('_edit_id') }}');
                form.querySelector('input[name="_edit_id"]').value = '{{ old('_edit_id') }}';
                document.getElementById('edit-class-name').value = @json(old('class_name'));
                document.getElementById('edit-modal').classList.remove('hidden');
                refreshIcons();
            @elseif($errors->any())
                window.openAddModal();
            @endif
        });
    })();
    </script>

</x-app-layout>
