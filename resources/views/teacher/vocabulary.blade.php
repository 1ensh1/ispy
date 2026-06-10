@extends('layouts.teacher')

@section('title', 'Vocabulary')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    {{-- Section 1: Vocabulary Library --}}
    <div>
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900">Vocabulary Library</h1>
            <p class="text-sm text-gray-500">Active words available in the iSpy World app (read-only)</p>
        </div>

        {{-- Search + Filters --}}
        <form method="GET" action="{{ route('teacher.vocabulary') }}" class="flex flex-wrap items-center gap-3 mb-4">
            <div class="relative" style="flex:1; min-width:192px; max-width:320px;">
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
                <option value="CVC"            {{ ($categoryFilter ?? '') === 'CVC'            ? 'selected' : '' }}>CVC</option>
                <option value="Multi-Syllabic"  {{ ($categoryFilter ?? '') === 'Multi-Syllabic' ? 'selected' : '' }}>Multi-Syllabic</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition-colors">
                Search
            </button>

            @if($search || $audioFilter || $categoryFilter)
                <a href="{{ route('teacher.vocabulary') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 underline">Clear filters</a>
            @endif

            <span class="px-3 py-1 rounded-full border border-gray-200 bg-white text-sm text-gray-600 font-medium ml-auto">
                {{ $words->total() }} {{ Str::plural('entry', $words->total()) }}
            </span>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">English</th>
                            <th class="px-6 py-4 font-medium">Filipino</th>
                            <th class="px-6 py-4 font-medium">Category</th>
                            <th class="px-6 py-4 font-medium">Difficulty</th>
                            <th class="px-6 py-4 font-medium">Audio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($words as $word)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $word->english_label }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $word->filipino_label }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $word->category }}</td>
                            <td class="px-6 py-4">
                                @if($word->complexity_level == 1)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#2f5597] text-white">CVC</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500 text-white">Multi-syllabic</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($word->audio_status === 'Complete')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 border border-teal-200">Complete</span>
                                @elseif($word->audio_status === 'Partial')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Partial</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600 border border-red-200">Missing</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <p>No active vocabulary entries yet.</p>
                                @if($search || $audioFilter || $categoryFilter)
                                    <a href="{{ route('teacher.vocabulary') }}" class="text-[#2f5597] hover:underline mt-1 inline-block">Clear filters</a>
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
    </div>

    {{-- Section 2: Propose + History --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Suggest Form --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Propose a New Word</h2>
            <p class="text-sm text-gray-500 mb-5">Submit a vocabulary word for admin review.</p>

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

            <form method="POST" action="{{ route('teacher.vocabulary.suggest') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        English Label <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="english_label" value="{{ old('english_label') }}" required
                           placeholder="e.g. Book"
                           class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none {{ $errors->has('english_label') ? 'border-red-400' : 'border-gray-200' }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Filipino Label <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="filipino_label" value="{{ old('filipino_label') }}" required
                           placeholder="e.g. Aklat"
                           class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none {{ $errors->has('filipino_label') ? 'border-red-400' : 'border-gray-200' }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select name="category" required
                            class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-[#2f5597] outline-none bg-white {{ $errors->has('category') ? 'border-red-400' : 'border-gray-200' }}">
                        <option value="">Select a category...</option>
                        <option value="CVC" {{ old('category') === 'CVC' ? 'selected' : '' }}>CVC</option>
                        <option value="Multi-Syllabic" {{ old('category') === 'Multi-Syllabic' ? 'selected' : '' }}>Multi-Syllabic</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="bg-[#2f5597] hover:bg-blue-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Submit Suggestion
                    </button>
                </div>
            </form>
        </div>

        {{-- Suggestion History --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-800">My Suggestions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">English</th>
                            <th class="px-5 py-3 font-medium">Filipino</th>
                            <th class="px-5 py-3 font-medium">Category</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($suggestions as $s)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $s->english_label }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $s->filipino_label }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $s->category }}</td>
                            <td class="px-5 py-3">
                                @if($s->status === 'Approved')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 border border-teal-200">Approved</span>
                                @elseif($s->status === 'Rejected')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600 border border-red-200">Rejected</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-200">Pending</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                {{ $s->submitted_at ? $s->submitted_at->format('M d, Y') : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400 text-sm">
                                <i data-lucide="send" class="w-6 h-6 mx-auto mb-2 opacity-30"></i>
                                <p>No suggestions submitted yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
