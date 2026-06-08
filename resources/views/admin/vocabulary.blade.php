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
                audio_status: 'Missing',
                image_url: null,
                current_page: 1,
                current_search: '',
                current_audio: '',
                current_category: '',
                current_status: ''
            },
            openEdit(w) {
                this.word = w;
                this.editOpen = true;
                this.$nextTick(() => {
                    const cat = document.getElementById('edit_category');
                    if (cat) cat.value = w.category;
                });
            },
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

            <select name="category" onchange="this.form.submit()"
                    class="px-3 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white text-gray-700">
                <option value="">All Categories</option>
                <option value="CVC"           {{ ($categoryFilter ?? '') === 'CVC'           ? 'selected' : '' }}>CVC</option>
                <option value="Multi-Syllabic" {{ ($categoryFilter ?? '') === 'Multi-Syllabic' ? 'selected' : '' }}>Multi-Syllabic</option>
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

            @if($search || $audioFilter || $categoryFilter || $activeFilter)
                <a href="{{ route('admin.vocabulary') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 underline">Clear filters</a>
            @endif

            <span class="px-3 py-1 rounded-full border border-gray-200 bg-white text-sm text-gray-600 font-medium ml-auto">
                {{ $words->total() }} {{ Str::plural('entry', $words->total()) }}
            </span>
        </form>

        {{-- Bulk Actions --}}
        <div id="auto-fill-wrap" class="mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <button id="auto-fill-btn" type="button" onclick="autoFillMissingImages()"
                        style="background-color:#f59e0b; color:#fff; display:none;"
                        class="px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors hover:opacity-90">
                    <i data-lucide="image-plus" class="w-4 h-4"></i>
                    Auto-Fill Missing Images
                </button>
                <button id="gen-audio-btn" type="button" onclick="generateAllMissingAudio()"
                        style="background-color:#0d9488; color:#fff; display:none;"
                        class="px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors hover:opacity-90">
                    <i data-lucide="volume-2" class="w-4 h-4"></i>
                    Generate All Missing Audio
                </button>
            </div>
            <p id="auto-fill-progress" class="mt-2 text-sm text-gray-600" style="display:none;"></p>
            <p id="gen-audio-progress" class="mt-2 text-sm text-gray-600" style="display:none;"></p>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Filipino</th>
                            <th class="px-6 py-4 font-medium">English</th>
                            <th class="px-6 py-4 font-medium">Image</th>
                            <th class="px-6 py-4 font-medium">Difficulty</th>
                            <th class="px-6 py-4 font-medium">Category</th>
                            <th class="px-6 py-4 font-medium">Audio</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($words as $word)
                        @php $isNew = $highlightId && (int)$word->id === (int)$highlightId; @endphp
                        <tr class="hover:bg-gray-50 transition-colors vocab-row"
                            data-vocab-id="{{ $word->id }}"
                            data-has-image="{{ $word->image_url ? '1' : '0' }}"
                            data-audio-status="{{ $word->audio_status }}"
                            style="{{ $isNew ? 'border-left: 4px solid #22c55e; background-color: #f0fdf4;' : '' }}">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $word->filipino_label }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $word->english_label }}</td>
                            <td class="px-6 py-4">
                                @if($word->image_url)
                                    <img src="{{ $word->image_url }}" alt="{{ $word->english_label }}"
                                         style="width:48px; height:48px; object-fit:cover;"
                                         class="rounded">
                                @else
                                    <span class="text-xs text-gray-400">No image</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($word->complexity_level == 1)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#2f5597] text-white">CVC</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500 text-white">Multi-syllabic</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $word->category }}</td>
                            <td class="px-6 py-4 audio-cell">
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
                                    @if($word->audio_status !== 'Complete')
                                    <button type="button"
                                            onclick="generateAudioForRow(this)"
                                            title="Generate Audio"
                                            class="generate-audio-btn text-gray-400 hover:text-[#2f5597] transition-colors">
                                        <i data-lucide="volume-2" class="w-4 h-4"></i>
                                    </button>
                                    @endif

                                    <button @click="openEdit({
                                                id: {{ $word->id }},
                                                filipino_label: @js($word->filipino_label),
                                                english_label: @js($word->english_label),
                                                category: @js($word->category),
                                                complexity_level: @js($word->complexity_level),
                                                is_active: {{ $word->is_active ? 'true' : 'false' }},
                                                filipino_audio_url: $event.currentTarget.dataset.filipinoAudioUrl || null,
                                                english_audio_url: $event.currentTarget.dataset.englishAudioUrl || null,
                                                audio_status: @js($word->audio_status),
                                                image_url: @js($word->image_url),
                                                current_page: {{ $words->currentPage() }},
                                                current_search: @js($search ?? ''),
                                                current_audio: @js($audioFilter ?? ''),
                                                current_category: @js($categoryFilter ?? ''),
                                                current_status: @js($activeFilter ?? '')
                                            })"
                                            data-english-audio-url="{{ $word->english_audio_url ?? '' }}"
                                            data-filipino-audio-url="{{ $word->filipino_audio_url ?? '' }}"
                                            class="edit-word-btn text-gray-400 hover:text-gray-700 transition-colors">
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
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400 text-sm">
                                <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <p>No vocabulary entries found.</p>
                                @if($search || $audioFilter || $categoryFilter || $activeFilter)
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

                <form method="POST" action="{{ route('admin.vocabulary.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
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
                            <option value="CVC">CVC</option>
                            <option value="Multi-Syllabic">Multi-Syllabic</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                        <p class="mt-1 text-xs text-gray-400">If no image is uploaded, one will be fetched automatically from Pexels based on the English label.</p>
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
                      :action="`{{ url('/admin/vocabulary') }}/${word.id}`"
                      enctype="multipart/form-data"
                      class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="current_page"     :value="word.current_page">
                    <input type="hidden" name="current_search"   :value="word.current_search">
                    <input type="hidden" name="current_audio"    :value="word.current_audio">
                    <input type="hidden" name="current_category" :value="word.current_category">
                    <input type="hidden" name="current_status"   :value="word.current_status">
                    <input type="hidden" id="edit-word-id"       :value="word.id">

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
                        <select name="category" id="edit_category" required
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white">
                            <option value="CVC">CVC</option>
                            <option value="Multi-Syllabic">Multi-Syllabic</option>
                        </select>
                    </div>

                    {{-- Image Upload Section --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <template x-if="word.image_url">
                            <div class="mb-2">
                                <img :src="word.image_url" alt="Current image"
                                     style="max-width:200px; max-height:150px; object-fit:contain;"
                                     class="rounded border border-gray-200">
                            </div>
                        </template>
                        <template x-if="!word.image_url">
                            <p class="text-xs text-gray-400 mb-2">No image currently.</p>
                        </template>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                        <input type="hidden" name="existing_image_url" :value="word.image_url || ''">
                        <p class="mt-1 text-xs text-gray-500">Replace Image (optional)</p>
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

                    {{-- Audio Section --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Audio</p>
                        </div>
                        <div class="p-4 space-y-5">

                            {{-- English Audio --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">English Audio</label>
                                <div id="edit-en-audio-wrap" x-show="word.english_audio_url" class="mb-2">
                                    <audio id="edit-en-audio-player" :src="word.english_audio_url" controls
                                           class="w-full rounded"></audio>
                                </div>
                                <p id="edit-en-audio-placeholder" x-show="!word.english_audio_url" class="mb-2 text-xs text-gray-400">No audio yet.</p>
                            </div>

                            {{-- Filipino Audio --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Filipino Audio</label>
                                <div id="edit-fil-audio-wrap" x-show="word.filipino_audio_url" class="mb-2">
                                    <audio id="edit-fil-audio-player" :src="word.filipino_audio_url" controls
                                           class="w-full rounded"></audio>
                                </div>
                                <p id="edit-fil-audio-placeholder" x-show="!word.filipino_audio_url" class="mb-2 text-xs text-gray-400">No audio yet.</p>
                            </div>

                            {{-- Regenerate via TTS --}}
                            <div>
                                <button type="button" id="regen-audio-btn" onclick="regenAudio()"
                                        style="background-color:#6366f1; color:#fff;"
                                        class="px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors hover:opacity-90">
                                    <i data-lucide="mic" class="w-4 h-4"></i>
                                    Regenerate Audio via TTS
                                </button>
                                <p id="regen-audio-msg" class="mt-2 text-xs" style="display:none;"></p>
                            </div>

                            {{-- English Audio Upload --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Replace English Audio (optional)</label>
                                <input type="file" name="english_audio" accept=".mp3,.wav,.ogg,audio/mpeg,audio/wav,audio/ogg"
                                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                <p class="mt-1 text-xs text-gray-400">MP3, WAV or OGG — max 10 MB</p>
                            </div>

                            {{-- Filipino Audio Upload --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Replace Filipino Audio (optional)</label>
                                <input type="file" name="filipino_audio" accept=".mp3,.wav,.ogg,audio/mpeg,audio/wav,audio/ogg"
                                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                <p class="mt-1 text-xs text-gray-400">MP3, WAV or OGG — max 10 MB</p>
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
            updateAutoFillButtonVisibility();
            updateGenAudioButtonVisibility();
        });

        function getMissingImageRows() {
            return Array.from(document.querySelectorAll('tr.vocab-row[data-has-image="0"]'));
        }

        function updateAutoFillButtonVisibility() {
            var btn = document.getElementById('auto-fill-btn');
            if (!btn) return;
            btn.style.display = getMissingImageRows().length > 0 ? 'flex' : 'none';
        }

        async function autoFillMissingImages() {
            var btn      = document.getElementById('auto-fill-btn');
            var progress = document.getElementById('auto-fill-progress');
            var rows     = getMissingImageRows();
            var total    = rows.length;

            if (total === 0) return;

            btn.disabled     = true;
            btn.style.opacity = '0.6';
            btn.style.cursor  = 'not-allowed';
            progress.style.display = 'block';

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var fetchUrl  = '{{ route('admin.vocabulary.fetch-image') }}';
            var done      = 0;
            var failed    = [];

            for (var i = 0; i < rows.length; i++) {
                var row    = rows[i];
                var wordId = row.getAttribute('data-vocab-id');
                progress.textContent = 'Processing ' + (done + 1) + ' of ' + total + ' words...';

                try {
                    var response = await fetch(fetchUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ vocabulary_id: wordId }),
                    });

                    var data = await response.json();

                    if (data.success) {
                        var imgCell = row.querySelector('td:nth-child(3)');
                        if (imgCell) {
                            var img = document.createElement('img');
                            img.src = data.image_url;
                            img.alt = data.english_label;
                            img.style.cssText = 'width:48px; height:48px; object-fit:cover;';
                            img.className = 'rounded';
                            imgCell.innerHTML = '';
                            imgCell.appendChild(img);
                        }
                        row.setAttribute('data-has-image', '1');
                    } else {
                        failed.push(data.english_label || 'ID ' + wordId);
                    }
                } catch (e) {
                    failed.push('ID ' + wordId);
                }

                done++;
            }

            var summary = 'Done! ' + (done - failed.length) + ' image' + ((done - failed.length) !== 1 ? 's' : '') + ' generated.';
            if (failed.length > 0) {
                summary += ' Could not fetch images for: ' + failed.join(', ');
            }
            progress.textContent = summary;

            btn.disabled      = false;
            btn.style.opacity = '1';
            btn.style.cursor  = 'pointer';

            updateAutoFillButtonVisibility();
        }

        // ===================== AUDIO GENERATION =====================

        var genAudioUrl = '{{ route('admin.vocabulary.generate-audio') }}';

        function getMissingAudioRows() {
            return Array.from(document.querySelectorAll('tr.vocab-row[data-audio-status="Missing"]'));
        }

        function updateGenAudioButtonVisibility() {
            var btn = document.getElementById('gen-audio-btn');
            if (!btn) return;
            btn.style.display = getMissingAudioRows().length > 0 ? 'flex' : 'none';
        }

        function markAudioComplete(row, englishUrl, filipinoUrl) {
            row.setAttribute('data-audio-status', 'Complete');
            var cell = row.querySelector('.audio-cell');
            if (cell) {
                cell.innerHTML = '<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 border border-teal-200">Complete</span>';
            }
            var genBtn = row.querySelector('.generate-audio-btn');
            if (genBtn) genBtn.style.display = 'none';
            var editBtn = row.querySelector('.edit-word-btn');
            if (editBtn && englishUrl)  editBtn.dataset.englishAudioUrl  = englishUrl;
            if (editBtn && filipinoUrl) editBtn.dataset.filipinoAudioUrl = filipinoUrl;
        }

        async function postGenerateAudio(wordId) {
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var response  = await fetch(genAudioUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ vocabulary_id: wordId }),
            });
            return response.json();
        }

        async function generateAudioForRow(btn) {
            var row    = btn.closest('tr.vocab-row');
            if (!row) return;
            var wordId = row.getAttribute('data-vocab-id');

            var original     = btn.innerHTML;
            btn.disabled      = true;
            btn.style.opacity = '0.6';
            btn.style.cursor  = 'not-allowed';
            btn.innerHTML     = '<span style="font-size:11px; white-space:nowrap;">Generating...</span>';

            try {
                var data = await postGenerateAudio(wordId);

                if (data.success) {
                    markAudioComplete(row, data.english_audio_url, data.filipino_audio_url);
                    btn.innerHTML = original;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    btn.innerHTML = original;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    alert('Failed to generate audio: ' + (data.message || 'Unknown error'));
                }
            } catch (e) {
                btn.innerHTML = original;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                alert('Failed to generate audio. Please try again.');
            }

            btn.disabled      = false;
            btn.style.opacity = '1';
            btn.style.cursor  = 'pointer';

            updateGenAudioButtonVisibility();
        }

        async function regenAudio() {
            var btn     = document.getElementById('regen-audio-btn');
            var msg     = document.getElementById('regen-audio-msg');
            var wordId  = document.getElementById('edit-word-id').value;

            var original      = btn.innerHTML;
            btn.disabled      = true;
            btn.style.opacity = '0.6';
            btn.style.cursor  = 'not-allowed';
            btn.innerHTML     = '<span>Regenerating...</span>';
            msg.style.display = 'none';

            try {
                var data = await postGenerateAudio(wordId);

                if (data.success) {
                    var enPlayer  = document.getElementById('edit-en-audio-player');
                    var filPlayer = document.getElementById('edit-fil-audio-player');
                    var enWrap    = document.getElementById('edit-en-audio-wrap');
                    var filWrap   = document.getElementById('edit-fil-audio-wrap');
                    var enPH      = document.getElementById('edit-en-audio-placeholder');
                    var filPH     = document.getElementById('edit-fil-audio-placeholder');

                    if (enPlayer && data.english_audio_url) {
                        enPlayer.src         = data.english_audio_url;
                        enPlayer.load();
                        enWrap.style.display = 'block';
                        if (enPH) enPH.style.display = 'none';
                    }
                    if (filPlayer && data.filipino_audio_url) {
                        filPlayer.src         = data.filipino_audio_url;
                        filPlayer.load();
                        filWrap.style.display = 'block';
                        if (filPH) filPH.style.display = 'none';
                    }

                    // Update the edit button's data attributes on the table row so the
                    // modal reflects new URLs if closed and re-opened without a page reload.
                    var tableRow = document.querySelector('tr.vocab-row[data-vocab-id="' + wordId + '"]');
                    if (tableRow) {
                        markAudioComplete(tableRow, data.english_audio_url, data.filipino_audio_url);
                    }

                    msg.textContent   = 'Audio regenerated successfully.';
                    msg.style.color   = '#15803d';
                    msg.style.display = 'block';
                } else {
                    msg.textContent   = data.message || 'Failed to regenerate audio.';
                    msg.style.color   = '#dc2626';
                    msg.style.display = 'block';
                }
            } catch (e) {
                msg.textContent   = 'Request failed. Please try again.';
                msg.style.color   = '#dc2626';
                msg.style.display = 'block';
            }

            btn.innerHTML     = original;
            btn.disabled      = false;
            btn.style.opacity = '1';
            btn.style.cursor  = 'pointer';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        async function generateAllMissingAudio() {
            var btn      = document.getElementById('gen-audio-btn');
            var progress = document.getElementById('gen-audio-progress');
            var rows     = getMissingAudioRows();
            var total    = rows.length;

            if (total === 0) return;

            btn.disabled      = true;
            btn.style.opacity = '0.6';
            btn.style.cursor  = 'not-allowed';
            progress.style.display = 'block';

            var done   = 0;
            var failed = [];

            for (var i = 0; i < rows.length; i++) {
                var row    = rows[i];
                var wordId = row.getAttribute('data-vocab-id');
                progress.textContent = 'Processing ' + (done + 1) + ' of ' + total + ' words...';

                try {
                    var data = await postGenerateAudio(wordId);

                    if (data.success) {
                        markAudioComplete(row, data.english_audio_url, data.filipino_audio_url);
                    } else {
                        failed.push(data.english_label || 'ID ' + wordId);
                    }
                } catch (e) {
                    failed.push('ID ' + wordId);
                }

                done++;
            }

            var summary = 'Done! ' + (done - failed.length) + ' audio file' + ((done - failed.length) !== 1 ? 's' : '') + ' generated.';
            if (failed.length > 0) {
                summary += ' Failed: ' + failed.join(', ');
            }
            progress.textContent = summary;

            btn.disabled      = false;
            btn.style.opacity = '1';
            btn.style.cursor  = 'pointer';

            updateGenAudioButtonVisibility();
        }
    </script>
</x-app-layout>
