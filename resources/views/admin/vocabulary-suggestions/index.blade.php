<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Vocabulary Suggestions</h1>
            <p class="text-sm text-gray-500">Review and manage teacher-submitted vocabulary suggestions</p>
        </div>

        {{-- Filter Bar --}}
        <div class="flex items-center gap-2 mb-5">
            @foreach(['' => 'All', 'Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected'] as $value => $label)
                <a href="{{ route('admin.vocabulary-suggestions.index', $value ? ['status' => $value] : []) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors border
                          {{ $status === ($value ?: null) || ($value === '' && !$status)
                             ? 'bg-[#2f5597] text-white border-[#2f5597]'
                             : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach

            {{-- Per-page selector (keeps the active status filter, resets to page 1) --}}
            <select onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.assign(u.toString());})(this.value)"
                    class="ml-auto px-3 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white text-gray-700">
                <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10 / page</option>
                <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20 / page</option>
                <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50 / page</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Teacher</th>
                            <th class="px-6 py-4 font-medium">English Label</th>
                            <th class="px-6 py-4 font-medium">Filipino Label</th>
                            <th class="px-6 py-4 font-medium">Category</th>
                            <th class="px-6 py-4 font-medium">Submitted At</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($suggestions as $suggestion)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $suggestion->teacher->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $suggestion->english_label }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $suggestion->filipino_label }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $suggestion->category }}</td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $suggestion->submitted_at ? $suggestion->submitted_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($suggestion->status === 'Pending')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Pending</span>
                                @elseif($suggestion->status === 'Approved')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">Approved</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600 border border-red-200">Rejected</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($suggestion->status === 'Pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST"
                                              action="{{ route('admin.vocabulary-suggestions.approve', $suggestion) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST"
                                              action="{{ route('admin.vocabulary-suggestions.reject', $suggestion) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center text-gray-400 text-sm">
                                <i data-lucide="lightbulb" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <p>No vocabulary suggestions at this time.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suggestions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $suggestions->links() }}
            </div>
            @endif
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
