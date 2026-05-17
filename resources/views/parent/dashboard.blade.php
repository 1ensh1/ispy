@extends('layouts.parent')
@section('title', 'Parent Dashboard')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Page heading --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ explode(' ', $parent->name)[0] }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $student ? $student->name."'s learning progress overview" : 'No student linked yet.' }}
        </p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Words Mastered --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Words Mastered</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $wordsMastered }}</p>
                    <p class="text-sm text-green-600 font-medium mt-1 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        {{ $wordsThisWeek }} this week
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-5 h-5 text-teal-600"></i>
                </div>
            </div>
        </div>

        {{-- Pronunciation Score --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Pronunciation Score</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $pronunciationScore }}%</p>
                    <p class="text-sm text-green-600 font-medium mt-1 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        Based on spelling attempts
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i data-lucide="mic" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
        </div>

        {{-- Next Consultation --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Next Consultation</p>
                    @if($nextConsultation)
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ date('M d', strtotime($nextConsultation->scheduled_date)) }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ date('g:i A', strtotime($nextConsultation->time_start)) }}
                            with {{ $nextConsultation->teacher_name }}
                        </p>
                    @else
                        <p class="text-xl font-semibold text-gray-400 mt-1">None scheduled</p>
                    @endif
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-amber-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile App Parent Password card --}}
    @if($student)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6" id="password-section">
        <div class="flex items-center gap-2 mb-1">
            <i data-lucide="key" class="w-5 h-5 text-gray-500"></i>
            <h2 class="font-semibold text-gray-900">Mobile App Parent Password</h2>
        </div>
        <p class="text-sm text-gray-500 mb-5">
            This is the password your child uses to access iSpy World on the mobile app at home.
            This is <strong class="text-gray-700">NOT</strong> your web portal login password.
            To change your web portal login password, go to My Profile.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Linked Student</p>
                <p class="font-semibold text-gray-800">{{ $student->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Parent Password</p>
                <div class="flex items-center gap-2">
                    <span id="pw-display" class="font-mono text-gray-800">••••••••</span>
                    @if($student->parent_password)
                        <span id="pw-actual" class="hidden font-mono text-gray-800">{{ $student->parent_password }}</span>
                    @endif
                    <button onclick="togglePassword()" class="text-gray-400 hover:text-gray-700 transition-colors">
                        <i data-lucide="eye" class="w-4 h-4" id="pw-eye"></i>
                    </button>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Last Updated</p>
                <p class="text-gray-800">{{ $student->updated_at ? \Carbon\Carbon::parse($student->updated_at)->format('M d, Y') : '—' }}</p>
            </div>
        </div>

        {{-- Success flash --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        <button onclick="togglePasswordForm()"
                class="text-sm font-medium text-[#1a2332] hover:underline flex items-center gap-1.5"
                id="change-pw-btn">
            <i data-lucide="edit-2" class="w-4 h-4"></i>
            Change Parent Password
        </button>

        <form method="POST" action="{{ route('parent.password.change') }}"
              id="pw-form" class="{{ $errors->any() ? '' : 'hidden' }} mt-4 space-y-3 max-w-sm">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Mobile App Password</label>
                <input type="password" name="current_password" required
                       class="w-full px-3 py-2 border {{ $errors->has('current_password') ? 'border-red-400' : 'border-gray-200' }} rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Mobile App Password</label>
                <input type="password" name="new_password" required
                       class="w-full px-3 py-2 border {{ $errors->has('new_password') ? 'border-red-400' : 'border-gray-200' }} rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                @error('new_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="togglePasswordForm()"
                        class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background:#1a2332;">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Bottom row: chart + recent words --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Activity chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Learning Activity (This Week)</h2>
            <canvas id="activityChart" height="120"></canvas>
        </div>

        {{-- Recent words --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Recent Words</h2>
            @forelse($recentWords as $word)
                <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ $word->filipino_label }}</p>
                        <p class="text-xs text-gray-400">{{ $word->english_label }}</p>
                    </div>
                    @if($word->proficiency_level === 'Mastered')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">Mastered</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">Practicing</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">No words yet.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const activity = @json($activityData);
    const ctx = document.getElementById('activityChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: activity.labels,
            datasets: [{
                data: activity.data,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.08)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointRadius: 4,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f3f4f6' } },
                x: { grid: { display: false } }
            }
        }
    });
})();

function togglePassword() {
    const display = document.getElementById('pw-display');
    const actual  = document.getElementById('pw-actual');
    const eye     = document.getElementById('pw-eye');
    if (!actual) return;
    if (display.textContent === '••••••••') {
        display.textContent = actual.textContent;
        eye.setAttribute('data-lucide', 'eye-off');
    } else {
        display.textContent = '••••••••';
        eye.setAttribute('data-lucide', 'eye');
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function togglePasswordForm() {
    const form = document.getElementById('pw-form');
    form.classList.toggle('hidden');
}
</script>
@endpush
