<x-app-layout>
    <div x-data="{
            isModalOpen: {{ $errors->any() ? 'true' : 'false' }},
            editOpen: false,
            editUser: { id: null, name: '', email: '', class_name: '' },
            openEdit(user) {
                this.editUser = { ...user };
                this.editOpen = true;
            }
         }" class="p-6 max-w-7xl mx-auto relative">

        {{-- Success notice --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Error notice --}}
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

        {{-- Classroom PIN generated --}}
        @if(session('new_class_pin'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="key" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">
                            Classroom PIN for {{ session('new_class_name') }}
                        </p>
                        <p class="mt-1 font-mono text-xl font-bold text-amber-900 tracking-widest select-all">
                            {{ session('new_class_pin') }}
                        </p>
                        <p class="mt-1 text-xs text-amber-600">
                            Share this PIN with the teacher to connect their class on the Android app.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- One-time temporary password notice --}}
        @if(session('new_teacher_password'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="key" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">
                            Temporary Password for {{ session('new_teacher_name') }}
                        </p>
                        <p class="mt-1 font-mono text-xl font-bold text-amber-900 tracking-widest select-all">
                            {{ session('new_teacher_password') }}
                        </p>
                        <p class="mt-1 text-xs text-amber-600">
                            Copy this now — it will not be shown again.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">User Management</h1>
                <p class="text-sm text-gray-500">Manage faculty, parent, and student accounts</p>
            </div>
            <button @click="isModalOpen = true"
                    class="bg-[#2f5597] hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                @if($activeTab === 'teacher') Add Teacher @else Add User @endif
            </button>
        </div>

        {{-- ===================== TEACHER: CREATE MODAL ===================== --}}
        @if($activeTab === 'teacher')
        <div x-show="isModalOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="isModalOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Add New Teacher</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
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
                        <p class="text-xs text-amber-700">
                            A random 8-character password will be auto-generated and shown once after creation.
                        </p>
                    </div>
                    <div class="pt-2 flex gap-3 justify-end">
                        <button type="button" @click="isModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                            Create Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== TEACHER: EDIT MODAL ===================== --}}
        <div x-show="editOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="editOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit Teacher</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="editUser.name"></p>
                    </div>
                    <button @click="editOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/admin/teachers') }}/` + editUser.id" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" x-model="editUser.name" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class Name</label>
                        <input type="text" name="class_name" x-model="editUser.class_name" required
                               placeholder="e.g. Kinder A, Grade 1 - Sampaguita"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editUser.email" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" @click="editOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @else
        {{-- ===================== GENERIC: CREATE USER MODAL ===================== --}}
        <div x-show="isModalOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="isModalOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Create New User</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="role" value="{{ $activeTab }}">
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
                    @if($activeTab === 'parent')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Number
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" name="contact_number"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none"
                               placeholder="e.g. 09171234567">
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Temporary Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" @click="isModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                            Create {{ $activeTab === 'parent' ? 'Parent' : 'Account' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== GENERIC: EDIT USER MODAL ===================== --}}
        <div x-show="editOpen"
             style="display:none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="editOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit User</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="editUser.name"></p>
                    </div>
                    <button @click="editOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/admin/users') }}/` + editUser.id" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" x-model="editUser.name" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editUser.email" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            New Password
                            <span class="text-gray-400 font-normal">(leave blank to keep current)</span>
                        </label>
                        <input type="password" name="password" placeholder="••••••"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                    </div>
                    <div class="pt-4 flex gap-3 justify-end">
                        <button type="button" @click="editOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Tab navigation --}}
        <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit mb-6">
            <a href="{{ route('admin.teachers.index') }}"
               class="{{ $activeTab === 'teacher' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }} px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Teachers
            </a>
            <a href="{{ route('admin.users', ['tab' => 'parent']) }}"
               class="{{ $activeTab === 'parent' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }} px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Parents
            </a>
            <a href="{{ route('admin.students') }}"
               class="text-gray-500 hover:text-gray-700 px-4 py-1.5 rounded-md text-sm font-medium transition-all">
                Student Mapping
            </a>
        </div>

        {{-- Search bar --}}
        <div class="mb-4 relative max-w-md">
            @if($activeTab === 'teacher')
            <form method="GET" action="{{ route('admin.teachers.index') }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search teachers by name or email..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] focus:border-transparent outline-none">
            </form>
            @else
            <form method="GET" action="{{ route('admin.users') }}">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search by name or email..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] focus:border-transparent outline-none">
            </form>
            @endif
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium w-24">ID</th>
                            <th class="px-6 py-4 font-medium">Name</th>
                            @if($activeTab === 'teacher')
                                <th class="px-6 py-4 font-medium">Email</th>
                                <th class="px-6 py-4 font-medium">Students</th>
                                <th class="px-6 py-4 font-medium">Classroom PIN</th>
                            @else
                                <th class="px-6 py-4 font-medium">Web Login</th>
                                <th class="px-6 py-4 font-medium">Children</th>
                                <th class="px-6 py-4 font-medium">Parent Password</th>
                            @endif
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                {{ strtoupper(substr($user->role, 0, 1)) }}-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>

                            @if($activeTab === 'teacher')
                                <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $studentCountsByUser[$user->id] ?? 0 }}
                                </td>
                                <td class="px-6 py-4">
                                    @php $cl = $classListsByUser[$user->id] ?? null; @endphp
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($cl && $cl['unified_classroom_pin'])
                                            <span class="font-mono text-sm font-bold text-gray-800 tracking-widest select-all">
                                                {{ $cl['unified_classroom_pin'] }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                                No PIN
                                            </span>
                                        @endif
                                        @if($cl && $cl['id'])
                                            <form method="POST" action="{{ route('admin.classes.generatePin', $cl['id']) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs px-2.5 py-1 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors whitespace-nowrap">
                                                    {{ $cl['unified_classroom_pin'] ? 'Regenerate' : 'Generate PIN' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @else
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
                            @endif

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-[11px] font-medium bg-[#2f5597] text-white tracking-wide">
                                    active
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @php $clForEdit = $classListsByUser[$user->id] ?? null; @endphp
                                <button @click="openEdit({ id: {{ $user->id }}, name: @js($user->name), email: @js($user->email), class_name: @js($clForEdit['class_name'] ?? '') })"
                                        class="text-gray-400 hover:text-gray-700 transition-colors">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>

                                @if($activeTab === 'teacher')
                                <form method="POST" action="{{ route('admin.teachers.destroy', $user->id) }}"
                                      class="inline-block"
                                      onsubmit="return confirm('Delete teacher account for \'{{ addslashes($user->name) }}\'?\n\nNote: deletion will be blocked if this teacher has active students.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                      class="inline-block"
                                      onsubmit="return confirm('Delete account for \'{{ addslashes($user->name) }}\'? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'teacher' ? 7 : 7 }}"
                                class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                <p class="text-sm">
                                    No {{ $activeTab === 'teacher' ? 'teachers' : 'users' }} found
                                    @if($search) matching "<span class="font-medium">{{ $search }}</span>" @endif.
                                </p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</x-app-layout>
