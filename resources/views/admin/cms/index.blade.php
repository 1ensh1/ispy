<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Page header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Content Management System</h1>
            <p class="text-sm text-gray-500">Manage the public landing page content and announcements</p>
        </div>

        @php
            $sectionMeta = [
                'hero'            => ['label' => 'Hero / Banner',     'icon' => 'image'],
                'about_school'    => ['label' => 'About the School',  'icon' => 'school'],
                'about_app'       => ['label' => 'About iSpy World',  'icon' => 'smartphone'],
                'how_to_download' => ['label' => 'How to Download',    'icon' => 'list-ordered'],
                'apk_download'    => ['label' => 'APK Download',       'icon' => 'download'],
            ];
        @endphp

        <div class="flex gap-6" style="flex-wrap: wrap;">

            {{-- ===================== LEFT: VERTICAL TAB NAV ===================== --}}
            <div style="width: 240px; flex-shrink: 0;">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-2 space-y-1">
                    @foreach($sectionMeta as $key => $meta)
                        <button type="button" data-cms-tab="{{ $key }}" onclick="cmsShowTab('{{ $key }}')"
                                class="cms-tab-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors text-left">
                            <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4 shrink-0"></i>
                            <span class="truncate">{{ $meta['label'] }}</span>
                        </button>
                    @endforeach
                    <div class="my-1 border-t border-gray-100"></div>
                    <button type="button" data-cms-tab="announcements" onclick="cmsShowTab('announcements')"
                            class="cms-tab-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors text-left">
                        <i data-lucide="megaphone" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Announcements</span>
                    </button>
                </div>
            </div>

            {{-- ===================== RIGHT: PANELS ===================== --}}
            <div style="flex: 1 1 480px; min-width: 0;">

                {{-- ---------- SECTION EDIT PANELS ---------- --}}
                @foreach($sectionMeta as $key => $meta)
                    @php $section = $sections->get($key); @endphp
                    <div data-cms-panel="{{ $key }}" style="display: none;">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
                                <i data-lucide="{{ $meta['icon'] }}" class="w-5 h-5 text-[#2f5597]"></i>
                                <h2 class="text-lg font-bold text-gray-900">{{ $meta['label'] }}</h2>
                            </div>

                            <form onsubmit="event.preventDefault(); cmsSaveSection(this, '{{ $key }}');"
                                  enctype="multipart/form-data" class="p-6 space-y-5">

                                {{-- Inline flash --}}
                                <div data-cms-msg class="hidden rounded-lg px-4 py-3 text-sm font-medium"></div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text" name="title" value="{{ $section->title ?? '' }}"
                                           class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                                    <textarea name="body" rows="5"
                                              class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">{{ $section->body ?? '' }}</textarea>
                                </div>

                                {{-- Image --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $key === 'apk_download' ? 'User Manual / Banner Image' : 'Section Image' }}
                                    </label>
                                    <div class="flex items-center gap-3">
                                        @if($section && $section->image_url)
                                            <img src="{{ $section->image_url }}" alt="Current"
                                                 style="width: 50px; height: 50px; object-fit: cover;"
                                                 class="rounded border border-gray-200 shrink-0">
                                        @endif
                                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
                                               class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                    </div>
                                </div>

                                {{-- APK file (apk_download only) --}}
                                @if($key === 'apk_download')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">APK File (.apk)</label>
                                        @if($section && $section->file_url)
                                            <p class="mb-2 text-sm">
                                                <a href="{{ $section->file_url }}" target="_blank" rel="noopener"
                                                   class="text-[#2f5597] hover:underline inline-flex items-center gap-1">
                                                    <i data-lucide="file-down" class="w-4 h-4"></i> Current APK
                                                </a>
                                            </p>
                                        @endif
                                        <input type="file" name="file" accept=".apk,application/vnd.android.package-archive"
                                               class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                    </div>
                                @endif

                                {{-- Published toggle --}}
                                <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <input type="checkbox" name="is_published" value="1" id="pub_{{ $key }}"
                                           {{ ($section && $section->is_published) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-gray-300 text-[#2f5597] focus:ring-[#2f5597]">
                                    <label for="pub_{{ $key }}" class="text-sm font-medium text-gray-700">Published / Visible on landing page</label>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="bg-[#2f5597] hover:bg-blue-800 text-white px-5 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors">
                                        <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- ---------- ANNOUNCEMENTS PANEL ---------- --}}
                <div data-cms-panel="announcements" style="display: none;">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i data-lucide="megaphone" class="w-5 h-5 text-[#2f5597]"></i>
                                <h2 class="text-lg font-bold text-gray-900">Announcements</h2>
                            </div>
                            <button type="button" onclick="cmsToggleAddAnnouncement()"
                                    class="bg-[#2f5597] hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center gap-2 transition-colors">
                                <i data-lucide="plus" class="w-4 h-4"></i> Add Announcement
                            </button>
                        </div>

                        {{-- Add form (hidden by default) --}}
                        <div id="cms-add-announcement" style="display: none;" class="px-6 py-5 border-b border-gray-100 bg-blue-50/40">
                            <form onsubmit="event.preventDefault(); cmsStoreAnnouncement(this);" enctype="multipart/form-data" class="space-y-4">
                                <div data-cms-msg class="hidden rounded-lg px-4 py-3 text-sm font-medium"></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text" name="title" required
                                           class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                                    <textarea name="body" rows="4" required
                                              class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Image (optional)</label>
                                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
                                           class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg">
                                    <input type="checkbox" name="is_published" value="1" id="new_ann_pub"
                                           class="w-4 h-4 rounded border-gray-300 text-[#2f5597] focus:ring-[#2f5597]">
                                    <label for="new_ann_pub" class="text-sm font-medium text-gray-700">Publish immediately</label>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="cmsToggleAddAnnouncement()"
                                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                                    <button type="submit"
                                            class="bg-[#2f5597] hover:bg-blue-800 text-white px-5 py-2 rounded-md text-sm font-medium transition-colors">Save Announcement</button>
                                </div>
                            </form>
                        </div>

                        {{-- Announcements table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                                    <tr>
                                        <th class="px-6 py-3 font-medium">Title</th>
                                        <th class="px-6 py-3 font-medium">Status</th>
                                        <th class="px-6 py-3 font-medium">Published At</th>
                                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($announcements as $a)
                                        <tr data-ann-row="{{ $a->id }}" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $a->title }}</td>
                                            <td class="px-6 py-4">
                                                @if($a->is_published)
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">Published</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">Draft</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-gray-500">
                                                {{ $a->published_at ? $a->published_at->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" onclick="cmsToggleEditAnnouncement({{ $a->id }})"
                                                            class="text-gray-400 hover:text-gray-700 transition-colors" title="Edit">
                                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" onclick="cmsDeleteAnnouncement({{ $a->id }})"
                                                            class="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        {{-- Inline edit row --}}
                                        <tr data-ann-edit="{{ $a->id }}" style="display: none;">
                                            <td colspan="4" class="px-6 py-5 bg-blue-50/40 border-t border-blue-100">
                                                <form onsubmit="event.preventDefault(); cmsUpdateAnnouncement(this, {{ $a->id }});" enctype="multipart/form-data" class="space-y-4">
                                                    <div data-cms-msg class="hidden rounded-lg px-4 py-3 text-sm font-medium"></div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                                        <input type="text" name="title" value="{{ $a->title }}" required
                                                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                                                        <textarea name="body" rows="4" required
                                                                  class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none">{{ $a->body }}</textarea>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Replace Image (optional)</label>
                                                        <div class="flex items-center gap-3">
                                                            @if($a->image_url)
                                                                <img src="{{ $a->image_url }}" alt="Current"
                                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                                     class="rounded border border-gray-200 shrink-0">
                                                            @endif
                                                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
                                                                   class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-[#2f5597]/10 file:text-[#2f5597] hover:file:bg-[#2f5597]/20 transition-colors">
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg">
                                                        <input type="checkbox" name="is_published" value="1" id="edit_ann_pub_{{ $a->id }}"
                                                               {{ $a->is_published ? 'checked' : '' }}
                                                               class="w-4 h-4 rounded border-gray-300 text-[#2f5597] focus:ring-[#2f5597]">
                                                        <label for="edit_ann_pub_{{ $a->id }}" class="text-sm font-medium text-gray-700">Published</label>
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" onclick="cmsToggleEditAnnouncement({{ $a->id }})"
                                                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                                                        <button type="submit"
                                                                class="bg-[#2f5597] hover:bg-blue-800 text-white px-5 py-2 rounded-md text-sm font-medium transition-colors">Update</button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr data-ann-empty>
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm">
                                                <i data-lucide="megaphone" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                                <p>No announcements yet.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination + per-page selector --}}
                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-medium text-gray-500">Rows per page</label>
                                <select onchange="cmsChangePerPage(this.value)"
                                        class="px-2 py-1.5 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-[#2f5597] outline-none">
                                    <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>
                            @if($announcements->hasPages())
                                <div>{{ $announcements->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const CMS_CSRF = document.querySelector('meta[name="csrf-token"]').content;

        // ---------- Tab switching ----------
        function cmsShowTab(tab) {
            document.querySelectorAll('[data-cms-panel]').forEach(p => {
                p.style.display = (p.dataset.cmsPanel === tab) ? 'block' : 'none';
            });
            document.querySelectorAll('.cms-tab-btn').forEach(b => {
                const active = b.dataset.cmsTab === tab;
                b.classList.toggle('bg-[#2f5597]/10', active);
                b.classList.toggle('text-[#2f5597]', active);
                b.classList.toggle('text-gray-600', !active);
            });
        }

        // ---------- Inline message helper ----------
        function cmsShowMsg(form, ok, text) {
            const box = form.querySelector('[data-cms-msg]');
            if (!box) return;
            box.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border', 'border-green-200', 'bg-red-50', 'text-red-600', 'border-red-200');
            if (ok) {
                box.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
            } else {
                box.classList.add('bg-red-50', 'text-red-600', 'border', 'border-red-200');
            }
            box.textContent = text;
        }

        // ---------- Section save (PATCH via POST + _method) ----------
        async function cmsSaveSection(form, key) {
            const fd = new FormData(form);
            fd.append('_method', 'PATCH');
            fd.set('is_published', form.querySelector('[name="is_published"]').checked ? '1' : '0');

            const url = "{{ url('admin/cms/section') }}/" + key;
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CMS_CSRF, 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    cmsShowMsg(form, true, 'Section updated.');
                } else {
                    cmsShowMsg(form, false, data.message || 'Update failed. Please check your input.');
                }
            } catch (e) {
                cmsShowMsg(form, false, 'Network error. Please try again.');
            }
            btn.disabled = false;
        }

        // ---------- Announcements ----------
        function cmsToggleAddAnnouncement() {
            const el = document.getElementById('cms-add-announcement');
            el.style.display = (el.style.display === 'none') ? 'block' : 'none';
        }

        function cmsToggleEditAnnouncement(id) {
            const row = document.querySelector('[data-ann-edit="' + id + '"]');
            if (row) row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
        }

        async function cmsStoreAnnouncement(form) {
            const fd = new FormData(form);
            fd.set('is_published', form.querySelector('[name="is_published"]').checked ? '1' : '0');
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;

            try {
                const res = await fetch("{{ route('admin.cms.announcements.store') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CMS_CSRF, 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    cmsShowMsg(form, false, data.message || 'Could not save announcement.');
                    btn.disabled = false;
                }
            } catch (e) {
                cmsShowMsg(form, false, 'Network error. Please try again.');
                btn.disabled = false;
            }
        }

        async function cmsUpdateAnnouncement(form, id) {
            const fd = new FormData(form);
            fd.append('_method', 'PATCH');
            fd.set('is_published', form.querySelector('[name="is_published"]').checked ? '1' : '0');
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;

            try {
                const res = await fetch("{{ url('admin/cms/announcements') }}/" + id, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CMS_CSRF, 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    cmsShowMsg(form, false, data.message || 'Could not update announcement.');
                    btn.disabled = false;
                }
            } catch (e) {
                cmsShowMsg(form, false, 'Network error. Please try again.');
                btn.disabled = false;
            }
        }

        async function cmsDeleteAnnouncement(id) {
            if (!confirm('Delete this announcement permanently?')) return;

            try {
                const res = await fetch("{{ url('admin/cms/announcements') }}/" + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CMS_CSRF, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const row  = document.querySelector('[data-ann-row="' + id + '"]');
                    const edit = document.querySelector('[data-ann-edit="' + id + '"]');
                    if (row)  row.remove();
                    if (edit) edit.remove();
                } else {
                    alert('Could not delete announcement.');
                }
            } catch (e) {
                alert('Network error. Please try again.');
            }
        }

        // ---------- Per-page (announcements) ----------
        // Reloads with the chosen per_page, drops the page param (reset to page 1),
        // and keeps the announcements tab active via the URL hash.
        function cmsChangePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page');
            url.hash = 'announcements';
            window.location.assign(url.toString());
        }

        // ---------- Init ----------
        document.addEventListener('DOMContentLoaded', function () {
            cmsShowTab(window.location.hash === '#announcements' ? 'announcements' : 'hero');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</x-app-layout>
