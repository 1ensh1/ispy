<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Flash: generic success --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Flash: new student created — show generated parent password --}}
        @if(session('new_student_password'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="key" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">
                            Parent Password for {{ session('new_student_name') }}
                        </p>
                        <p class="mt-1 font-mono text-xl font-bold text-amber-900 tracking-widest select-all">
                            {{ session('new_student_password') }}
                        </p>
                        <p class="mt-1 text-xs text-amber-600">
                            Copy this now — it will not be shown again.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <ul class="text-sm font-medium space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">User Management</h1>
                <p class="text-sm text-gray-500">Manage faculty, parent, and student accounts</p>
            </div>
            <button onclick="openCreateModal()"
                    class="bg-[#2f5597] hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Add Student
            </button>
        </div>

        {{-- Tab navigation --}}
        <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit mb-6">
            <a href="{{ route('admin.teachers.index') }}"
               class="text-gray-500 hover:text-gray-700 px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Teachers
            </a>
            <a href="{{ route('admin.users', ['tab' => 'parent']) }}"
               class="text-gray-500 hover:text-gray-700 px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Parents
            </a>
            <span class="bg-white shadow-sm text-gray-900 px-4 py-1.5 rounded-md text-sm font-medium">
                Student Mapping
            </span>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Student</th>
                            <th class="px-6 py-4 font-medium">Parent</th>
                            <th class="px-6 py-4 font-medium">Class &amp; Teacher</th>
                            <th class="px-6 py-4 font-medium">Parent Password</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $student)
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Student column --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @php
                                        $iconEmoji = [
                                            'cat'     => '🐱', 'dog'     => '🐶',
                                            'bear'    => '🐻', 'rabbit'  => '🐰',
                                            'fox'     => '🦊', 'frog'    => '🐸',
                                            'penguin' => '🐧', 'lion'    => '🦁',
                                        ];
                                    @endphp
                                    @if($student->profile_icon && isset($iconEmoji[$student->profile_icon]))
                                        <div class="w-8 h-8 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0 text-lg leading-none">
                                            {{ $iconEmoji[$student->profile_icon] }}
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0">
                                            <span class="text-[#2f5597] text-xs font-bold">
                                                {{ strtoupper(substr($student->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                    <span class="font-medium text-gray-900">{{ $student->name }}</span>
                                </div>
                            </td>

                            {{-- Parent column --}}
                            <td class="px-6 py-4">
                                @if($student->parentUser)
                                    <span class="text-gray-700">{{ $student->parentUser->name }}</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            {{-- Class & Teacher column --}}
                            <td class="px-6 py-4">
                                @if($student->classList)
                                    <div class="text-gray-700">{{ $student->classList->class_name }}</div>
                                    @if($student->classList->teacher)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $student->classList->teacher->name }}</div>
                                    @endif
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            {{-- Parent Password column --}}
                            <td class="px-6 py-4">
                                @if($student->parent_password)
                                    <span class="font-mono text-sm text-gray-800 tracking-widest select-all">
                                        {{ $student->parent_password }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Status column --}}
                            <td class="px-6 py-4">
                                @if($student->parentUser && $student->classList)
                                    <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-teal-500 text-white tracking-wide">
                                        Complete
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-red-100 text-red-600 border border-red-200 tracking-wide">
                                        Incomplete
                                    </span>
                                @endif
                            </td>

                            {{-- Actions column --}}
                            <td class="px-6 py-4 text-right space-x-2">
                                <button
                                    onclick="openAssignModal(
                                        {{ $student->id }},
                                        @js($student->name),
                                        {{ $student->parent_id ?? 'null' }},
                                        {{ $student->class_list_id ?? 'null' }}
                                    )"
                                    class="text-sm px-3 py-1.5 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors inline-flex items-center gap-1.5">
                                    <i data-lucide="{{ $student->parentUser && $student->classList ? 'pencil' : 'link' }}" class="w-3.5 h-3.5"></i>
                                    {{ $student->parentUser && $student->classList ? 'Edit' : 'Assign' }}
                                </button>
                                <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}"
                                      class="inline-block"
                                      onsubmit="return confirm('Delete student \'{{ addslashes($student->name) }}\'? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                <p class="text-sm">No students yet. Click "Add Student" to create one.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $students->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ===================== CREATE STUDENT MODAL ===================== --}}
    <div id="create-modal"
         style="display:none;"
         class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">

        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Add Student</h3>
                    <p class="text-xs text-gray-500 mt-0.5">A parent password will be auto-generated.</p>
                </div>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="create-form" method="POST" action="{{ route('admin.students.store') }}" class="p-6 space-y-4">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Student full name"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                </div>

                {{-- Profile Icon Picker --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Icon <span class="text-red-500">*</span></label>
                    <input type="hidden" id="create-profile-icon" name="profile_icon" value="{{ old('profile_icon') }}">
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;">
                        @foreach([
                            'cat'     => '🐱',
                            'dog'     => '🐶',
                            'bear'    => '🐻',
                            'rabbit'  => '🐰',
                            'fox'     => '🦊',
                            'frog'    => '🐸',
                            'penguin' => '🐧',
                            'lion'    => '🦁',
                        ] as $iconName => $emoji)
                            <button type="button"
                                    onclick="selectIcon('{{ $iconName }}')"
                                    id="icon-btn-{{ $iconName }}"
                                    class="icon-btn flex flex-col items-center gap-1 p-3 rounded-lg border-2 border-gray-200 hover:border-[#2f5597] transition-colors cursor-pointer"
                                    data-icon="{{ $iconName }}">
                                <span class="text-2xl leading-none">{{ $emoji }}</span>
                                <span class="text-[10px] text-gray-500 capitalize">{{ $iconName }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Parent dropdown --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
                    <select name="parent_id"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                        <option value="">— Assign Later —</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Class dropdown --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select name="class_list_id"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                        <option value="">— Assign Later —</option>
                        @foreach($classLists as $cl)
                            <option value="{{ $cl->id }}" {{ old('class_list_id') == $cl->id ? 'selected' : '' }}>
                                {{ $cl->class_name }}{{ $cl->teacher ? ' — ' . $cl->teacher->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex gap-3 justify-end">
                    <button type="button" onclick="closeCreateModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                        Create Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== ASSIGN MODAL ===================== --}}
    <div id="assign-modal"
         style="display:none;"
         class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">

        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Assign Student</h3>
                    <p class="text-xs text-gray-500 mt-0.5" id="assign-student-name"></p>
                </div>
                <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="assign-form" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PATCH">

                {{-- Parent dropdown --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
                    <select id="assign-parent" name="parent_id"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                        <option value="">— None —</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Class dropdown --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select id="assign-class" name="class_list_id"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                        <option value="">— None —</option>
                        @foreach($classLists as $cl)
                            <option value="{{ $cl->id }}">
                                {{ $cl->class_name }}{{ $cl->teacher ? ' — ' . $cl->teacher->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex gap-3 justify-end">
                    <button type="button" onclick="closeAssignModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                        Save Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ---- Create modal ----
        function openCreateModal() {
            document.getElementById('create-modal').style.display = 'flex';
            // Restore selected icon if returning from validation error
            var saved = document.getElementById('create-profile-icon').value;
            if (saved) selectIcon(saved);
        }

        function closeCreateModal() {
            document.getElementById('create-modal').style.display = 'none';
            // Reset name input
            document.querySelector('#create-form input[name="name"]').value = '';
            // Clear icon selection
            document.getElementById('create-profile-icon').value = '';
            document.querySelectorAll('.icon-btn').forEach(function (btn) {
                btn.classList.remove('border-[#2f5597]', 'bg-[#2f5597]/5');
                btn.classList.add('border-gray-200');
            });
            // Reset dropdowns to first option
            document.querySelector('#create-form select[name="parent_id"]').selectedIndex = 0;
            document.querySelector('#create-form select[name="class_list_id"]').selectedIndex = 0;
        }

        function selectIcon(name) {
            document.getElementById('create-profile-icon').value = name;
            document.querySelectorAll('.icon-btn').forEach(function (btn) {
                if (btn.dataset.icon === name) {
                    btn.classList.add('border-[#2f5597]', 'bg-[#2f5597]/5');
                    btn.classList.remove('border-gray-200');
                } else {
                    btn.classList.remove('border-[#2f5597]', 'bg-[#2f5597]/5');
                    btn.classList.add('border-gray-200');
                }
            });
        }

        // Auto-open create modal if there were validation errors
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function () { openCreateModal(); });
        @endif

        // ---- Assign modal ----
        function openAssignModal(studentId, studentName, parentId, classListId) {
            document.getElementById('assign-student-name').textContent = studentName;
            document.getElementById('assign-form').action = '/admin/students/' + studentId;
            document.getElementById('assign-parent').value = parentId  ?? '';
            document.getElementById('assign-class').value  = classListId ?? '';
            document.getElementById('assign-modal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assign-modal').style.display = 'none';
        }

        document.getElementById('assign-modal').addEventListener('click', function (e) {
            if (e.target === this) closeAssignModal();
        });

        document.getElementById('create-modal').addEventListener('click', function (e) {
            if (e.target === this) closeCreateModal();
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
