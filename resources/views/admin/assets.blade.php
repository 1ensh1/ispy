<x-app-layout>
    <div x-data="{
            uploadOpen: false,
            upload: { id: null, label: '', language: '' },
            openUpload(id, label, language) {
                this.upload = { id, label, language };
                this.uploadOpen = true;
            }
         }"
         class="p-6 max-w-7xl mx-auto relative">

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Bilingual Assets</h1>
                <p class="text-sm text-gray-500">Manage native-speaker audio files for Filipino and English vocabulary</p>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center">
                        <i data-lucide="volume-2" class="w-5 h-5 text-teal-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalUploaded }}</p>
                        <p class="text-xs text-gray-500">Audio Files Uploaded</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i data-lucide="globe" class="w-5 h-5 text-[#2f5597]"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">2</p>
                        <p class="text-xs text-gray-500">Languages Supported</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalMissing }}</p>
                        <p class="text-xs text-gray-500">Missing Audio Files</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Word</th>
                            <th class="px-6 py-4 font-medium">Language</th>
                            <th class="px-6 py-4 font-medium">Type</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($words as $word)

                            {{-- Filipino row --}}
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $word->filipino_label }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border border-gray-200 text-gray-600">Filipino</span>
                                </td>
                                <td class="px-6 py-3 text-gray-500">Pronunciation</td>
                                <td class="px-6 py-3">
                                    @if($word->filipino_audio_url)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500 text-white">uploaded</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600 border border-red-200">missing</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <button @click="openUpload({{ $word->id }}, @js($word->filipino_label), 'filipino')"
                                            class="text-sm px-3 py-1 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors">
                                        {{ $word->filipino_audio_url ? 'Replace' : 'Upload' }}
                                    </button>
                                    @if($word->filipino_audio_url)
                                        <form method="POST" action="{{ route('admin.assets.destroy', [$word->id, 'filipino']) }}"
                                              class="inline-block ml-1"
                                              onsubmit="return confirm('Remove Filipino audio for \'{{ addslashes($word->filipino_label) }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm px-3 py-1 rounded-md border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            {{-- English row --}}
                            <tr class="hover:bg-gray-50 transition-colors bg-gray-50/50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $word->english_label }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border border-[#2f5597]/30 text-[#2f5597]">English</span>
                                </td>
                                <td class="px-6 py-3 text-gray-500">Pronunciation</td>
                                <td class="px-6 py-3">
                                    @if($word->english_audio_url)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500 text-white">uploaded</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600 border border-red-200">missing</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <button @click="openUpload({{ $word->id }}, @js($word->english_label), 'english')"
                                            class="text-sm px-3 py-1 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors">
                                        {{ $word->english_audio_url ? 'Replace' : 'Upload' }}
                                    </button>
                                    @if($word->english_audio_url)
                                        <form method="POST" action="{{ route('admin.assets.destroy', [$word->id, 'english']) }}"
                                              class="inline-block ml-1"
                                              onsubmit="return confirm('Remove English audio for \'{{ addslashes($word->english_label) }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm px-3 py-1 rounded-md border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                <i data-lucide="volume-x" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <p>No vocabulary entries yet. Add words in the Vocabulary Library first.</p>
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

        {{-- ===================== UPLOAD AUDIO MODAL ===================== --}}
        <div x-show="uploadOpen"
             style="display: none;"
             class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">

            <div @click.away="uploadOpen = false"
                 class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Upload Audio File</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-text="upload.language === 'filipino' ? 'Filipino' : 'English'"></span>
                            pronunciation for "<span x-text="upload.label"></span>"
                        </p>
                    </div>
                    <button @click="uploadOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST"
                      :action="`/admin/assets/${upload.id}/upload`"
                      enctype="multipart/form-data"
                      class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="language" :value="upload.language">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Audio File (MP3, WAV, OGG — max 10MB)</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-lg p-6 text-center hover:border-[#2f5597] transition-colors">
                            <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                            <input type="file" name="audio_file" accept=".mp3,.wav,.ogg,audio/*" required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[#2f5597] file:text-white hover:file:bg-blue-800 cursor-pointer">
                        </div>
                    </div>

                    <div class="pt-2 flex gap-3 justify-end">
                        <button type="button" @click="uploadOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#2f5597] hover:bg-blue-800 rounded-lg transition-colors flex items-center gap-2">
                            <i data-lucide="upload" class="w-4 h-4"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
