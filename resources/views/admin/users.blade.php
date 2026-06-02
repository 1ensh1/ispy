<x-app-layout>
    <div x-data="{
            activeTab: @js(request()->query('tab', 'teachers')),

            teacherCreateOpen: {{ ($errors->any() && request()->query('tab', 'teachers') === 'teachers') ? 'true' : 'false' }},
            teacherEditOpen: false,
            editTeacher: { id: null, name: '', email: '', class_name: '' },
            openEditTeacher(user) { this.editTeacher = { ...user }; this.teacherEditOpen = true; },

            parentCreateOpen: {{ ($errors->any() && request()->query('tab') === 'parents') ? 'true' : 'false' }},
            parentEditOpen: false,
            editParent: { id: null, name: '', email: '' },
            openEditParent(user) { this.editParent = { ...user }; this.parentEditOpen = true; },

            getAddLabel() {
                if (this.activeTab === 'teachers') return 'Add Teacher';
                if (this.activeTab === 'parents')  return 'Add Parent';
                return 'Add Student';
            },
            handleAdd() {
                if (this.activeTab === 'teachers') { this.teacherCreateOpen = true; return; }
                if (this.activeTab === 'parents')  { this.parentCreateOpen  = true; return; }
                document.getElementById('create-modal').style.display = 'flex';
            },
            switchTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                history.replaceState({}, '', url.toString());
            }
         }" class="p-6 max-w-7xl mx-auto relative">

        {{-- Flash: success --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Flash: error --}}
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('error') }}</p>
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

        {{-- Flash: classroom PIN --}}
        @if(session('new_class_pin'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="key" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Classroom PIN for {{ session('new_class_name') }}</p>
                        <p class="mt-1 font-mono text-xl font-bold text-amber-900 tracking-widest select-all">{{ session('new_class_pin') }}</p>
                        <p class="mt-1 text-xs text-amber-600">Share this PIN with the teacher to connect their class on the Android app.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Teacher temp password flash removed: credentials are now sent by email --}}

        {{-- Flash: new student parent password --}}
        @if(session('new_student_password'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="key" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Parent Password for {{ session('new_student_name') }}</p>
                        <p class="mt-1 font-mono text-xl font-bold text-amber-900 tracking-widest select-all">{{ session('new_student_password') }}</p>
                        <p class="mt-1 text-xs text-amber-600">Copy this now — it will not be shown again.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Page header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">User Management</h1>
                <p class="text-sm text-gray-500">Manage faculty, parent, and student accounts</p>
            </div>
            <button @click="handleAdd()"
                    class="bg-[#2f5597] hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="user-plus" class="w-4 h-4 shrink-0"></i>
                <span x-text="getAddLabel()"></span>
            </button>
        </div>

        {{-- ===== TEACHER CREATE MODAL ===== --}}
        <div x-show="teacherCreateOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="teacherCreateOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Add New Teacher</h3>
                    <button @click="teacherCreateOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.teachers.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class Name</label>
                        <input type="text" name="class_name" required
                               placeholder="e.g. Kinder A, Grade 1 - Sampaguita"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <p class="text-xs text-amber-700">A 10-character temporary password will be auto-generated and emailed to the teacher. The account will be set to <strong>Inactive</strong> until the teacher activates it.</p>
                    </div>
                    <div class="pt-2 flex gap-3 justify-end">
                        <button type="button" @click="teacherCreateOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">Create Teacher</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== TEACHER EDIT MODAL ===== --}}
        <div x-show="teacherEditOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="teacherEditOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit Teacher</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="editTeacher.name"></p>
                    </div>
                    <button @click="teacherEditOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/admin/teachers') }}/` + editTeacher.id" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" x-model="editTeacher.name" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class Name</label>
                        <input type="text" name="class_name" x-model="editTeacher.class_name" required
                               placeholder="e.g. Kinder A, Grade 1 - Sampaguita"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editTeacher.email" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" @click="teacherEditOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== PARENT CREATE MODAL ===== --}}
        <div x-show="parentCreateOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="parentCreateOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Create New Parent</h3>
                    <button @click="parentCreateOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="role" value="parent">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Number <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" name="contact_number" placeholder="e.g. 09171234567"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Temporary Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" @click="parentCreateOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">Create Parent</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== PARENT EDIT MODAL ===== --}}
        <div x-show="parentEditOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="parentEditOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit Parent</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="editParent.name"></p>
                    </div>
                    <button @click="parentEditOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/admin/users') }}/` + editParent.id" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" x-model="editParent.name" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editParent.email" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            New Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span>
                        </label>
                        <input type="password" name="password" placeholder="••••••"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" @click="parentEditOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== STUDENT CREATE MODAL (vanilla JS) ===== --}}
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="Student full name"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Icon <span class="text-red-500">*</span></label>
                        <input type="hidden" id="create-profile-icon" name="profile_icon" value="{{ old('profile_icon') }}">
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;">
                            @foreach(['cat'=>'🐱','dog'=>'🐶','bear'=>'🐻','rabbit'=>'🐰','fox'=>'🦊','frog'=>'🐸','penguin'=>'🐧','lion'=>'🦁'] as $iconName => $emoji)
                                <button type="button" onclick="selectIcon('{{ $iconName }}')"
                                        id="icon-btn-{{ $iconName }}"
                                        class="icon-btn flex flex-col items-center gap-1 p-3 rounded-lg border-2 border-gray-200 hover:border-[#2f5597] transition-colors cursor-pointer"
                                        data-icon="{{ $iconName }}">
                                    <span class="text-2xl leading-none">{{ $emoji }}</span>
                                    <span class="text-[10px] text-gray-500 capitalize">{{ $iconName }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
                        <select name="parent_id" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="">— Assign Later —</option>
                            @foreach($parentsList as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                        <select name="class_list_id" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
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
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">Create Student</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== STUDENT ASSIGN MODAL (vanilla JS) ===== --}}
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Icon</label>
                        <input type="hidden" id="assign-profile-icon" name="profile_icon" value="cat">
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;">
                            @foreach(['cat'=>'🐱','dog'=>'🐶','bear'=>'🐻','rabbit'=>'🐰','fox'=>'🦊','frog'=>'🐸','penguin'=>'🐧','lion'=>'🦁'] as $iconName => $emoji)
                                <button type="button" onclick="selectAssignIcon('{{ $iconName }}')"
                                        id="assign-icon-btn-{{ $iconName }}"
                                        class="assign-icon-btn flex flex-col items-center gap-1 p-3 rounded-lg border-2 border-gray-200 hover:border-[#2f5597] transition-colors cursor-pointer"
                                        data-icon="{{ $iconName }}">
                                    <span class="text-2xl leading-none">{{ $emoji }}</span>
                                    <span class="text-[10px] text-gray-500 capitalize">{{ $iconName }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
                        <select id="assign-parent" name="parent_id" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="">— None —</option>
                            @foreach($parentsList as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                        <select id="assign-class" name="class_list_id" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="">— None —</option>
                            @foreach($classLists as $cl)
                                <option value="{{ $cl->id }}">{{ $cl->class_name }}{{ $cl->teacher ? ' — ' . $cl->teacher->name : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Password</label>
                        <div class="flex gap-2">
                            <input type="text" id="assign-parent-password" name="parent_password"
                                   class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-[#2f5597] outline-none">
                            <button type="button" onclick="generateAssignPassword()"
                                    class="px-3 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors whitespace-nowrap">Generate</button>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">This password is used by the student's mobile app at home.</p>
                    </div>
                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" onclick="closeAssignModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">Save Assignments</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tab navigation --}}
        <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit mb-6">
            <button @click="switchTab('teachers')"
                    :class="activeTab === 'teachers' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:text-gray-800'"
                    class="px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Teachers
            </button>
            <button @click="switchTab('parents')"
                    :class="activeTab === 'parents' ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:text-gray-800'"
                    class="px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Parents
            </button>
            <button @click="switchTab('students')"
                    :class="activeTab === 'students' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:text-gray-800'"
                    class="px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Students
            </button>
        </div>

        {{-- ===================== TEACHERS TAB PANEL ===================== --}}
        <div x-show="activeTab === 'teachers'">

            {{-- Teacher search --}}
            <div class="mb-4 relative max-w-md">
                <form method="GET" action="{{ route('admin.teachers.index') }}" onsubmit="return false;">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                    </div>
                    <input type="text" id="search-teachers" name="search" value="{{ $search ?? '' }}"
                           placeholder="Search teachers by name or email..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] focus:border-transparent outline-none">
                </form>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium w-24">ID</th>
                                <th class="px-6 py-4 font-medium">Name</th>
                                <th class="px-6 py-4 font-medium">Email</th>
                                <th class="px-6 py-4 font-medium">Classes</th>
                                <th class="px-6 py-4 font-medium">Students</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-teachers" class="divide-y divide-gray-100">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors" data-name="{{ strtolower($user->name) }} {{ strtolower($user->email) }}">
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                    T-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    @php $cl = $classListsByUser[$user->id] ?? null; @endphp
                                    @if($cl && !empty($cl['all_classes']))
                                        <div class="space-y-1">
                                            @foreach($cl['all_classes'] as $cls)
                                                <div class="flex items-center gap-1.5 flex-wrap text-sm text-gray-700">
                                                    <span>{{ $cls['name'] }}{{ $cls['subject'] ? ' ('.$cls['subject'].')' : '' }}</span>
                                                    @if($cls['pin'])
                                                        <span class="text-gray-400">—</span>
                                                        <span class="font-mono text-xs font-bold text-gray-600 tracking-widest">{{ $cls['pin'] }}</span>
                                                    @else
                                                        <span class="text-gray-400 text-xs">— no PIN</span>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.classes.generatePin', $cls['id']) }}" class="inline">
                                                        @csrf
                                                        @if($cls['pin'])
                                                            <button type="submit"
                                                                    class="px-2.5 py-1 text-xs font-medium rounded-md border border-gray-200 bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                                                Regen
                                                            </button>
                                                        @else
                                                            <button type="submit"
                                                                    class="px-2.5 py-1 text-xs font-medium rounded-md bg-[#2f5597] text-white hover:bg-blue-800 transition-colors">
                                                                Generate
                                                            </button>
                                                        @endif
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $studentCountsByUser[$user->id] ?? 0 }}
                                </td>
                                <td class="px-6 py-4">
                                    @php $teacherStatus = $classListsByUser[$user->id]['status'] ?? 'Active'; @endphp
                                    @if($teacherStatus === 'Active')
                                        <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-[#2f5597] text-white tracking-wide">Active</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-gray-200 text-gray-600 tracking-wide">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @php $clForEdit = $classListsByUser[$user->id] ?? null; @endphp
                                    <div class="flex items-center justify-end gap-3">
                                    @if($clForEdit && !empty($clForEdit['teacher_id']))
                                        <a href="{{ route('admin.teachers.profile', $clForEdit['teacher_id']) }}"
                                           class="text-gray-400 hover:text-[#2f5597] transition-colors" title="View profile">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                    <button @click="openEditTeacher({ id: {{ $user->id }}, name: @js($user->name), email: @js($user->email), class_name: @js($clForEdit['class_name'] ?? '') })"
                                            class="text-gray-400 hover:text-gray-700 transition-colors">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.teachers.destroy', $user->id) }}"
                                          class="inline-block"
                                          onsubmit="return confirm('Delete teacher account for \'{{ addslashes($user->name) }}\'?\n\nNote: deletion will be blocked if this teacher has active students.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                    @if($search)
                                        <p class="text-sm">No teachers found matching "<span class="font-medium">{{ $search }}</span>".</p>
                                    @else
                                        <p class="text-sm">No teachers found.</p>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $users->links() }}
                </div>
            </div>
        </div>

        {{-- ===================== PARENTS TAB PANEL ===================== --}}
        <div x-show="activeTab === 'parents'" style="display:none;">

            {{-- Parent search --}}
            <div class="mb-4 relative max-w-md">
                <form method="GET" action="{{ route('admin.teachers.index') }}" onsubmit="return false;">
                    <input type="hidden" name="tab" value="parents">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                    </div>
                    <input type="text" id="search-parents" name="parent_search" value="{{ $parentSearch ?? '' }}"
                           placeholder="Search parents by name or email..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] focus:border-transparent outline-none">
                </form>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium w-24">ID</th>
                                <th class="px-6 py-4 font-medium">Name</th>
                                <th class="px-6 py-4 font-medium">Web Login</th>
                                <th class="px-6 py-4 font-medium">Children</th>
                                <th class="px-6 py-4 font-medium">Parent Password</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-parents" class="divide-y divide-gray-100">
                            @forelse($parentUsers as $user)
                            <tr class="hover:bg-gray-50 transition-colors" data-name="{{ strtolower($user->name) }} {{ strtolower($user->email) }}">
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                    P-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                                @php $children = $extraData[$user->id]['children'] ?? collect(); @endphp
                                <td class="px-6 py-4 text-gray-500">{{ explode('@', $user->email)[0] }}</td>
                                <td class="px-6 py-4 text-gray-500">
                                    @forelse($children as $child)
                                        <div class="text-sm">{{ $child->name }}</div>
                                    @empty
                                        <span class="text-gray-400 text-xs">No student linked</span>
                                    @endforelse
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    @forelse($children as $child)
                                        <div x-data="{ showPwd: false }" class="flex items-center gap-2 mb-1">
                                            <span class="tracking-widest font-mono font-bold text-sm"
                                                  x-text="showPwd ? '{{ $child->parent_password }}' : '••••••'"></span>
                                            <i data-lucide="eye"
                                               @click="showPwd = !showPwd"
                                               class="w-4 h-4 cursor-pointer hover:text-[#2f5597] transition-colors"
                                               :class="showPwd ? 'text-[#2f5597]' : 'text-gray-400'"></i>
                                        </div>
                                    @empty
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endforelse
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-[#2f5597] text-white tracking-wide">active</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.parents.profile', $user->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="View Profile">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <button @click="openEditParent({ id: {{ $user->id }}, name: @js($user->name), email: @js($user->email) })"
                                            class="text-gray-400 hover:text-gray-700 transition-colors">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                          class="inline-block"
                                          onsubmit="return confirm('Delete account for \'{{ addslashes($user->name) }}\'? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                    @if($parentSearch)
                                        <p class="text-sm">No parents found matching "<span class="font-medium">{{ $parentSearch }}</span>".</p>
                                    @else
                                        <p class="text-sm">No parents found.</p>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $parentUsers->links() }}
                </div>
            </div>
        </div>

        {{-- ===================== STUDENTS TAB PANEL ===================== --}}
        <div x-show="activeTab === 'students'" style="display:none;">

            {{-- Student search --}}
            <div class="mb-4 relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </div>
                <input type="text" id="search-students"
                       placeholder="Search students by name..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] focus:border-transparent outline-none">
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Student</th>
                                <th class="px-6 py-4 font-medium">Parent</th>
                                <th class="px-6 py-4 font-medium">Class &amp; Teacher</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-students" class="divide-y divide-gray-100">
                            @forelse($students as $student)
                            <tr class="hover:bg-gray-50 transition-colors" data-name="{{ strtolower($student->name) }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @php $iconEmoji = ['cat'=>'🐱','dog'=>'🐶','bear'=>'🐻','rabbit'=>'🐰','fox'=>'🦊','frog'=>'🐸','penguin'=>'🐧','lion'=>'🦁']; @endphp
                                        @if($student->profile_icon && isset($iconEmoji[$student->profile_icon]))
                                            <div class="w-8 h-8 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0 text-lg leading-none">
                                                {{ $iconEmoji[$student->profile_icon] }}
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-[#2f5597]/10 flex items-center justify-center shrink-0">
                                                <span class="text-[#2f5597] text-xs font-bold">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <span class="font-medium text-gray-900">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($student->parentUser)
                                        <span class="text-gray-700">{{ $student->parentUser->name }}</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($student->classList)
                                        <div class="text-gray-700">{{ $student->classList->class_name }}</div>
                                        @if($student->classList->teacher)
                                            <div class="text-xs text-gray-400 mt-0.5">{{ $student->classList->teacher->name }}</div>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($student->parentUser && $student->classList)
                                        <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-teal-500 text-white tracking-wide">Complete</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-red-100 text-red-600 border border-red-200 tracking-wide">Incomplete</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.students.profile', $student->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="View Profile">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <button onclick="openAssignModal(
                                                {{ $student->id }},
                                                @js($student->name),
                                                {{ $student->parent_id ?? 'null' }},
                                                {{ $student->class_list_id ?? 'null' }},
                                                @js($student->profile_icon ?? 'cat'),
                                                @js($student->parent_password ?? '')
                                            )"
                                            class="text-sm px-3 py-1.5 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors inline-flex items-center gap-1.5">
                                        <i data-lucide="{{ $student->parentUser && $student->classList ? 'pencil' : 'link' }}" class="w-3.5 h-3.5"></i>
                                        {{ $student->parentUser && $student->classList ? 'Edit' : 'Assign' }}
                                    </button>
                                    <form method="POST" action="{{ route('admin.students.archive', $student->id) }}"
                                          class="inline-block"
                                          onsubmit="return confirm('Archive student \'{{ addslashes($student->name) }}\'? They will be hidden from active lists.')">
                                        @csrf
                                        <button type="submit" class="text-gray-400 hover:text-yellow-600 transition-colors" title="Archive student">
                                            <i data-lucide="archive" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
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

    </div>

    <script>
        // ---- Student create modal ----
        function openCreateModal() {
            document.getElementById('create-modal').style.display = 'flex';
            var saved = document.getElementById('create-profile-icon').value;
            if (saved) selectIcon(saved);
        }
        function closeCreateModal() {
            document.getElementById('create-modal').style.display = 'none';
            document.querySelector('#create-form input[name="name"]').value = '';
            document.getElementById('create-profile-icon').value = '';
            document.querySelectorAll('.icon-btn').forEach(function (btn) {
                btn.classList.remove('border-[#2f5597]', 'bg-[#2f5597]/5');
                btn.classList.add('border-gray-200');
            });
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

        // ---- Student assign modal ----
        function selectAssignIcon(name) {
            document.getElementById('assign-profile-icon').value = name;
            document.querySelectorAll('.assign-icon-btn').forEach(function (btn) {
                if (btn.dataset.icon === name) {
                    btn.classList.add('border-[#2f5597]', 'bg-[#2f5597]/5');
                    btn.classList.remove('border-gray-200');
                } else {
                    btn.classList.remove('border-[#2f5597]', 'bg-[#2f5597]/5');
                    btn.classList.add('border-gray-200');
                }
            });
        }
        function openAssignModal(studentId, studentName, parentId, classListId, profileIcon, parentPassword) {
            document.getElementById('assign-student-name').textContent = studentName;
            document.getElementById('assign-form').action = '{{ url("/admin/students") }}/' + studentId;
            document.getElementById('assign-parent').value = parentId  ?? '';
            document.getElementById('assign-class').value  = classListId ?? '';
            document.getElementById('assign-parent-password').value = parentPassword ?? '';
            selectAssignIcon(profileIcon || 'cat');
            document.getElementById('assign-modal').style.display = 'flex';
        }
        function closeAssignModal() {
            document.getElementById('assign-modal').style.display = 'none';
        }
        function generateAssignPassword() {
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
            var pw = '';
            for (var i = 0; i < 8; i++) pw += chars.charAt(Math.floor(Math.random() * chars.length));
            document.getElementById('assign-parent-password').value = pw;
        }

        document.getElementById('assign-modal').addEventListener('click', function (e) {
            if (e.target === this) closeAssignModal();
        });
        document.getElementById('create-modal').addEventListener('click', function (e) {
            if (e.target === this) closeCreateModal();
        });

        @if($errors->any() && request()->query('tab') === 'students')
            document.addEventListener('DOMContentLoaded', function () { openCreateModal(); });
        @endif

        function setupInstantSearch(inputId, tbodyId) {
            const input = document.getElementById(inputId);
            const tbody = document.getElementById(tbodyId);
            if (!input || !tbody) return;
            input.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                const rows = tbody.querySelectorAll('tr:not([data-empty])');
                let visibleCount = 0;
                rows.forEach(function (row) {
                    const name = (row.dataset.name || '').toLowerCase();
                    if (query === '' || name.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                let emptyRow = tbody.querySelector('[data-empty]');
                if (!emptyRow) {
                    emptyRow = document.createElement('tr');
                    emptyRow.setAttribute('data-empty', '1');
                    emptyRow.innerHTML = '<td colspan="10" class="text-center py-8 text-gray-400 text-sm">No results found.</td>';
                    tbody.appendChild(emptyRow);
                }
                emptyRow.style.display = visibleCount === 0 ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
            setupInstantSearch('search-teachers', 'tbody-teachers');
            setupInstantSearch('search-parents', 'tbody-parents');
            setupInstantSearch('search-students', 'tbody-students');
        });
    </script>
</x-app-layout>
