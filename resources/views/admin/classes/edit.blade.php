<x-app-layout>
    <div class="max-w-xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.classes.index') }}"
               class="flex items-center justify-center w-9 h-9 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Class</h1>
                <p class="text-sm text-gray-500">{{ $class->class_name }}</p>
            </div>
        </div>

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

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.classes.update', $class->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class Name <span class="text-red-500">*</span></label>
                    <input type="text" name="class_name" maxlength="50" required
                           value="{{ old('class_name', $class->class_name) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Classroom PIN</label>
                    <input type="text" value="{{ $class->unified_classroom_pin }}" disabled
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-400 font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subjects</label>
                    <p class="text-sm text-gray-600">
                        @php $subjects = $class->classSubjects->pluck('subject')->filter()->unique()->values(); @endphp
                        {{ $subjects->isNotEmpty() ? $subjects->implode(', ') : '—' }}
                    </p>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:4px;">
                    <a href="{{ route('admin.classes.index') }}"
                       class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
