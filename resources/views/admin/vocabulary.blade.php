<x-app-layout>
    <div x-data="{
            addOpen: false,
            editOpen: false,
            word: {
                id: null,
                filipino_label: '',
                english_label: '',
                category: '',
                complexity_level: 'CVC',
                is_active: true,
                filipino_audio_url: null,
                english_audio_url: null,
                audio_status: 'Missing'
            },
            openEdit(w) { this.word = w; this.editOpen = true; },
            closeEdit() { this.editOpen = false; }
         }"
         class="p-6 max-w-7xl mx-auto relative">

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

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

        {{-- Page Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Vocabulary Library</h1>
                <p class="text-sm text-gray-500">Manage bilingual vocabulary entries and audio assets</p>
            </div>
            <button @click="addOpen = true"
                    class="bg-[#2f5597] hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Word
            </button>
        </div>

        {{-- Search + Filters + Entry Count --}}
        <form method="GET" action="{{ route('admin.vocabulary') }}" class="flex flex-wrap items-center gap-3 mb-4">
            <div class="relative flex-1 min-w-48 max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search vocabulary..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] focus:border-transparent outline-none">
            </div>

            <select name="audio_status" onchange="this.form.submit()"
                    class="px-3 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white text-gray-700">
                <option value="">All Audio</option>
                <option value="Complete" {{ ($audioFilter ?? '') === 'Complete' ? 'selected' : '' }}>Complete</option>
                <option value="Partial"  {{ ($audioFilter ?? '') === 'Partial'  ? 'selected' : '' }}>Partial</option>
                <option value="Missing"  {{ ($audioFilter ?? '') === 'Missing'  ? 'selected' : '' }}>Missing</option>
            </select>

            <select name="is_active" onchange="this.form.submit()"
                    class="px-3 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white text-gray-700">
                <option value="">All Status</option>
                <option value="1" {{ ($activeFilter ?? '') === '1' ? 'selected' : '' }}>Active Only</option>
                <option value="0" {{ ($activeFilter ?? '') === '0' ? 'selected' : '' }}>Inactive Only</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition-colors">
                Search
            </button>

            @if($search || $audioFilter || $activeFilter)
                <a href="{{ route('admin.vocabulary') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 underline">Clear filters</a>
            @endif

            <span class="px-3 py-1 rounded-full border border-gray-200 bg-white text-sm text-gray-600 font-medium ml-auto">
                {{ $words->total() }} {{ Str::plural('entry', $words->total()) }}
            </span>
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Filipino</th>
                            <th class="px-6 py-4 font-medium">English</th>
                            <th class="px-6 py-4 font-medium">Difficulty</th>
                            <th class="px-6 py-4 font-medium">Category</th>
                            <th class="px-6 py-4 font-medium">Audio</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($words as $word)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $word->filipino_label }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $word->english_label }}</td>
                            <td class="px-6 py-4">
                                @if($word->complexity_level == 1)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#2f5597] text-white">CVC</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500 text-white">Multi-syllabic</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $word->category }}</td>
                            <td class="px-6 py-4">
                                @if($word->audio_status === 'Complete')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 border border-teal-200">Complete</span>
                                @elseif($word->audio_status === 'Partial')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Partial</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600 border border-red-200">Missing</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($word->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#2f5597] text-white">Active</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit({
                                                id: {{ $word->id }},
                                                filipino_label: @js($word->filipino_label),
                                                english_label: @js($word->english_label),
                                                category: @js($word->category),
                                                complexity_level: @js($word->complexity_level),
                                                is_active: {{ $word->is_active ? 'true' : 'false' }},
                                                filipino_audio_url: @js($word->filipino_audio_url),
                                                english_audio_url: @js($word->english_audio_url),
                                                audio_status: @js($word->audio_status)
                                            })"
                                            class="text-gray-400 hover:text-gray-700 transition-colors">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>

                                    <form method="POST"
                                          action="{{ route('admin.vocabulary.destroy', $word->id) }}"
                                          onsubmit="return confirm('Delete \'{{ addslashes($word->filipino_label) }}\' ({{ addslashes($word->english_label) }})? This cannot be undone.')">
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
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">
                                <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <p>No vocabulary entries found.</p>
                                @if($search || $audioFilter || $activeFilter)
                                    <a href="{{ route('admin.vocabulary') }}" class="text-[#2f5597] hover:underline mt-1 inline-block">Clear filters</a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($words->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $words->links() }}
            </div>
            @endif
        </div>

        {{-- ===================== ADD WORD MODAL ===================== --}}
        <div x-show="addOpen"
             style="display: none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">

            <div @click.away="addOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Add New Word</h3>
                    <button @click="addOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.vocabulary.store') }}" class="p-6 space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filipino Label</label>
                            <input type="text" name="filipino_label" required
                                   placeholder="e.g. Aklat"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">English Label</label>
                            <input type="text" name="english_label" required
                                   placeholder="e.g. Book"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" required
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="">Select category...</option>
                            @foreach(['Classroom','Household','Food & Drinks','Animals','Body Parts','Nature','Clothing','Community'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty / Complexity</label>
                        <select name="complexity_level" required
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="1">CVC (Consonant-Vowel-Consonant)</option>
                            <option value="2">Multi-syllabic</option>
                        </select>
                    </div>

                    <p class="text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        Audio files can be uploaded after the word is created by clicking the edit (pencil) button.
                    </p>

                    <div class="pt-2 flex gap-3 justify-end">
                        <button type="button" @click="addOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors">
                            Add Word
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== EDIT WORD MODAL ===================== --}}
        <div x-show="editOpen"
             style="display: none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">

            <div @click.away="closeEdit()"
                 class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50 sticky top-0 z-10">
                    <h3 class="text-lg font-bold text-gray-900">Edit Word</h3>
                    <button @click="closeEdit()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST"
                      :action="`/admin/vocabulary/${word.id}`"
                      enctype="multipart/form-data"
                      class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filipino Label</label>
                            <input type="text" name="filipino_label" required
                                   x-model="word.filipino_label"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">English Label</label>
                            <input type="text" name="english_label" required
                                   x-model="word.english_label"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" required x-model="word.category"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            @foreach(['Classroom','Household','Food & Drinks','Animals','Body Parts','Nature','Clothing','Community'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty / Complexity</label>
                        <select name="complexity_level" required x-model="word.complexity_level"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="1">CVC (Consonant-Vowel-Consonant)</option>
                            <option value="2">Multi-syllabic</option>
                        </select>
                    </div>

                    {{-- is_active toggle --}}
                    <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                               :checked="word.is_active"
                               @change="word.is_active = $event.target.checked"
                               class="w-4 h-4 rounded border-gray-300 text-[#2f5597] focus:ring-[#2f5597]">
                        <div>
                            <label for="edit_is_active" class="text-sm font-medium text-gray-700">Active</label>
                            <p class="text-xs text-gray-500">Word is served to the mobile app</p>
                        </div>
                    </div>

                    {{-- Audio Upload Section --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Audio Files (MP3, max 10 MB each)</p>
                        </div>
                        <div class="p-4 space-y-4">

                            {{-- Filipino Audio --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Filipino Audio</label>
                                <div x-show="word.filipino_audio_url" class="mb-2">
                                    <audio :src="word.filipino_audio_url" controls
                                           class="w-full h-8 rounded"></audio>
                                </div>
                                <input type="file" name="filipino_audio" accept=".mp3,audio/mpeg"
                                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                <p x-show="!word.filipino_audio_url" class="mt-1 text-xs text-gray-400">No file uploaded yet.</p>
                            </div>

                            {{-- English Audio --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">English Audio</label>
                                <div x-show="word.english_audio_url" class="mb-2">
                                    <audio :src="word.english_audio_url" controls
                                           class="w-full h-8 rounded"></audio>
                                </div>
                                <input type="file" name="english_audio" accept=".mp3,audio/mpeg"
                                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                <p x-show="!word.english_audio_url" class="mt-1 text-xs text-gray-400">No file uploaded yet.</p>
                            </div>

                        </div>
                    </div>

                    <div class="pt-2 flex gap-3 justify-end">
                        <button type="button" @click="closeEdit()"
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

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</x-app-layout>
