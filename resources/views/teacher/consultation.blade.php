@extends('layouts.teacher')
@section('title', 'Consultation Availability')

@section('content')
@php
    use Carbon\Carbon;

    $hours = [8, 9, 10, 11, 12, 13, 14, 15, 16];
    $days  = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri'];

    $grid = [];
    foreach ($slots as $slot) {
        $dow  = (int) Carbon::parse($slot->scheduled_date)->dayOfWeek;
        $hour = (int) substr($slot->time_start, 0, 2);
        if ($dow >= 1 && $dow <= 5 && $hour >= 8 && $hour <= 16) {
            $grid[$dow][$hour][] = $slot;
        }
    }
@endphp

<div class="space-y-6">

    {{-- Top bar --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Consultation Availability</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your consultation slots and upcoming appointments</p>
        </div>
        <button type="button"
                onclick="saveSchedule()"
                class="shrink-0 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors hover:opacity-80"
                style="background:#1e3a5f;">
            Save Schedule
        </button>
    </div>

    {{-- Stat / config cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-check" class="w-6 h-6 text-indigo-600"></i>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ $upcomingCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Upcoming Appointments</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <label class="block text-xs font-medium text-gray-500 mb-2">Max Appointments / Day</label>
            <select class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                           focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                <option>2</option>
                <option>4</option>
                <option selected>6</option>
                <option>8</option>
            </select>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <label class="block text-xs font-medium text-gray-500 mb-2">Consultation Duration</label>
            <select id="duration-select"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                           focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                <option value="30">30 minutes</option>
                <option value="45" selected>45 minutes</option>
                <option value="60">60 minutes</option>
            </select>
        </div>

    </div>

    {{-- Tabs --}}
    <div>
        <div class="flex gap-1 border-b border-gray-200">
            <button onclick="switchTab('weekly')" id="tab-weekly"
                    class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px
                           border-indigo-600 text-indigo-700">
                Weekly Availability
            </button>
            <button onclick="switchTab('calendar')" id="tab-calendar"
                    class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px
                           border-transparent text-gray-500 hover:text-gray-700">
                Calendar View
            </button>
            <button onclick="switchTab('appointments')" id="tab-appointments"
                    class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px
                           border-transparent text-gray-500 hover:text-gray-700">
                Appointments
            </button>
        </div>

        {{-- ── Tab: Weekly Availability ── --}}
        <div id="panel-weekly" class="mt-4 space-y-4">

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 w-28">Time</th>
                                @foreach($days as $dow => $dayName)
                                    <th class="px-4 py-3 text-center font-medium text-gray-500">{{ $dayName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($hours as $hour)
                                @php
                                    if ($hour < 12)      $timeLabel = $hour . ':00 AM';
                                    elseif ($hour === 12) $timeLabel = '12:00 PM';
                                    else                 $timeLabel = ($hour - 12) . ':00 PM';
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-medium whitespace-nowrap text-xs">
                                        {{ $timeLabel }}
                                    </td>
                                    @foreach([1,2,3,4,5] as $dow)
                                        <td class="px-4 py-3 text-center">
                                            @if(!empty($grid[$dow][$hour]))
                                                <div class="flex flex-col items-center gap-1.5">
                                                    @foreach($grid[$dow][$hour] as $slot)
                                                        <div class="flex items-center gap-1">
                                                            <button type="button"
                                                                    data-slot-id="{{ $slot->id }}"
                                                                    data-available="{{ $slot->is_available ? '1' : '0' }}"
                                                                    onclick="toggleSlot(this)"
                                                                    class="slot-toggle px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer border
                                                                           {{ $slot->is_available ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                                                {{ $slot->is_available ? 'Available' : 'Unavailable' }}
                                                            </button>
                                                            <form method="POST"
                                                                  action="{{ route('teacher.consultation.destroy', $slot->id) }}"
                                                                  onsubmit="return confirm('Delete this slot?')"
                                                                  class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="text-gray-300 hover:text-red-500
                                                                               transition-colors ml-0.5">
                                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-200 text-base leading-none">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add New Slot form --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-indigo-500"></i>
                    Add New Slot
                </h3>
                <form method="POST" action="{{ route('teacher.consultation.store') }}"
                      class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                        <input type="date" name="scheduled_date" id="slot-date" required
                               min="{{ today()->format('Y-m-d') }}"
                               value="{{ old('scheduled_date') }}"
                               class="px-3 py-2 border border-gray-200 rounded-lg text-sm
                                      focus:ring-2 focus:ring-indigo-500 outline-none bg-white
                                      @error('scheduled_date') border-red-400 @enderror">
                        @error('scheduled_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Start Time</label>
                        <input type="time" name="time_start" id="slot-time-start" required
                               value="{{ old('time_start') }}"
                               class="px-3 py-2 border border-gray-200 rounded-lg text-sm
                                      focus:ring-2 focus:ring-indigo-500 outline-none bg-white
                                      @error('time_start') border-red-400 @enderror">
                        @error('time_start')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">End Time</label>
                        <input type="time" name="time_end" id="slot-time-end" required
                               value="{{ old('time_end') }}"
                               class="px-3 py-2 border border-gray-200 rounded-lg text-sm
                                      focus:ring-2 focus:ring-indigo-500 outline-none bg-white
                                      @error('time_end') border-red-400 @enderror">
                        @error('time_end')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white rounded-lg
                                   transition-colors hover:opacity-80"
                            style="background:#1e3a5f;">
                        Add Slot
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Tab: Calendar View ── --}}
        <div id="panel-calendar" class="mt-4 hidden">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 max-w-sm">
                <div class="flex items-center justify-between mb-4">
                    <button onclick="prevMonth()"
                            class="p-1 rounded hover:bg-gray-100 transition-colors">
                        <i data-lucide="chevron-left" class="w-5 h-5 text-gray-600"></i>
                    </button>
                    <span id="cal-month-label" class="font-semibold text-gray-800 text-sm"></span>
                    <button onclick="nextMonth()"
                            class="p-1 rounded hover:bg-gray-100 transition-colors">
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-600"></i>
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-0 text-center mb-1">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                        <div class="text-[11px] font-medium text-gray-400 py-1">{{ $d }}</div>
                    @endforeach
                </div>
                <div id="cal-grid" class="grid grid-cols-7 gap-0 text-center"></div>
                <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                    Has available slots
                </div>
            </div>
        </div>

        {{-- ── Tab: Appointments ── --}}
        <div id="panel-appointments" class="mt-4 hidden">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                            <tr>
                                <th class="px-5 py-4 font-medium">Parent Name</th>
                                <th class="px-5 py-4 font-medium">Student Name</th>
                                <th class="px-5 py-4 font-medium">Date</th>
                                <th class="px-5 py-4 font-medium">Time</th>
                                <th class="px-5 py-4 font-medium">Purpose</th>
                                <th class="px-5 py-4 font-medium">Status</th>
                                <th class="px-5 py-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($bookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $booking->parent_name ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $booking->student_name ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-gray-600 whitespace-nowrap">
                                    {{ date('M d, Y', strtotime($booking->scheduled_date)) }}
                                </td>
                                <td class="px-5 py-4 text-gray-600 whitespace-nowrap">
                                    {{ date('g:i A', strtotime($booking->time_start)) }}
                                    –&nbsp;{{ date('g:i A', strtotime($booking->time_end)) }}
                                </td>
                                <td class="px-5 py-4 text-gray-500 max-w-xs truncate">
                                    {{ $booking->purpose_of_meeting }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($booking->status === 'Confirmed')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                     bg-green-100 text-green-700 border border-green-200">
                                            Confirmed
                                        </span>
                                    @elseif($booking->status === 'Pending')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                     bg-amber-100 text-amber-700 border border-amber-200">
                                            Pending
                                        </span>
                                    @elseif($booking->status === 'Completed')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                     bg-gray-100 text-gray-500 border border-gray-200">
                                            Completed
                                        </span>
                                    @elseif($booking->status === 'Cancelled')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                     bg-rose-100 text-rose-700 border border-rose-200">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                     bg-gray-100 text-gray-500 border border-gray-200">
                                            {{ $booking->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($booking->status === 'Pending')
                                        <form method="POST" action="{{ route('teacher.consultation.updateStatus') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                            <input type="hidden" name="status" value="Confirmed">
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                                Confirm
                                            </button>
                                        </form>
                                    @elseif($booking->status === 'Confirmed')
                                        <form method="POST" action="{{ route('teacher.consultation.updateStatus') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                            <input type="hidden" name="status" value="Completed">
                                            <button type="submit"
                                                    class="px-3 py-1 text-xs font-medium text-white rounded-lg hover:opacity-80 transition-colors"
                                                    style="background:#1e3a5f;">
                                                Complete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                                    <i data-lucide="calendar" class="w-7 h-7 mx-auto mb-2 opacity-30"></i>
                                    No appointments yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
const slotChanges = {};

function toggleSlot(btn) {
    const id      = btn.dataset.slotId;
    const current = btn.dataset.available === '1';
    const next    = !current;
    btn.dataset.available = next ? '1' : '0';
    if (next) {
        btn.textContent = 'Available';
        btn.className   = 'slot-toggle px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer border bg-green-100 text-green-700 border-green-200';
    } else {
        btn.textContent = 'Unavailable';
        btn.className   = 'slot-toggle px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer border bg-gray-100 text-gray-500 border-gray-200';
    }
    slotChanges[id] = next ? 1 : 0;
}

function saveSchedule() {
    if (Object.keys(slotChanges).length === 0) {
        alert('No availability changes to save.');
        return;
    }
    const form  = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("teacher.consultation.save") }}';
    const csrf  = document.createElement('input');
    csrf.type   = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    for (const [id, val] of Object.entries(slotChanges)) {
        const inp = document.createElement('input');
        inp.type  = 'hidden'; inp.name = 'slots[' + id + ']'; inp.value = val;
        form.appendChild(inp);
    }
    document.body.appendChild(form);
    form.submit();
}

const slotDates  = new Set(@json($slotDates));
let currentYear  = new Date().getFullYear();
let currentMonth = new Date().getMonth();
const monthNames = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];

function renderCalendar() {
    const label = document.getElementById('cal-month-label');
    const grid  = document.getElementById('cal-grid');
    if (!label || !grid) return;

    label.textContent = monthNames[currentMonth] + ' ' + currentYear;
    grid.innerHTML    = '';

    const today     = new Date(); today.setHours(0, 0, 0, 0);
    const firstDay  = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMon = new Date(currentYear, currentMonth + 1, 0).getDate();

    for (let i = 0; i < firstDay; i++) {
        grid.insertAdjacentHTML('beforeend', '<div></div>');
    }

    for (let d = 1; d <= daysInMon; d++) {
        const dateObj = new Date(currentYear, currentMonth, d);
        const dateStr = dateObj.toISOString().slice(0, 10);
        const hasSlot = slotDates.has(dateStr);
        const isPast  = dateObj < today;

        let cls = 'relative flex items-center justify-center w-8 h-8 mx-auto text-sm rounded-full ';
        cls += isPast ? 'text-gray-300 cursor-default' : 'text-gray-700 hover:bg-gray-100 cursor-pointer';

        const dot = hasSlot
            ? '<span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-green-500"></span>'
            : '';

        grid.insertAdjacentHTML('beforeend',
            `<div class="py-0.5"><div class="${cls}">${d}${dot}</div></div>`
        );
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function prevMonth() {
    if (currentMonth === 0) { currentMonth = 11; currentYear--; }
    else currentMonth--;
    renderCalendar();
}
function nextMonth() {
    if (currentMonth === 11) { currentMonth = 0; currentYear++; }
    else currentMonth++;
    renderCalendar();
}

const TABS = ['weekly', 'calendar', 'appointments'];

function switchTab(tab) {
    TABS.forEach(function (t) {
        const panel = document.getElementById('panel-' + t);
        const btn   = document.getElementById('tab-' + t);
        if (!panel || !btn) return;
        const active = t === tab;
        panel.classList.toggle('hidden', !active);
        btn.classList.toggle('border-indigo-600', active);
        btn.classList.toggle('text-indigo-700',   active);
        btn.classList.toggle('border-transparent', !active);
        btn.classList.toggle('text-gray-500',      !active);
    });
    if (tab === 'calendar') renderCalendar();
}

function calcExpectedEnd(startVal, durationMins) {
    const [h, m] = startVal.split(':').map(Number);
    const total  = h * 60 + m + durationMins;
    const eh     = Math.floor(total / 60) % 24;
    const em     = total % 60;
    return String(eh).padStart(2, '0') + ':' + String(em).padStart(2, '0');
}

document.addEventListener('DOMContentLoaded', function () {
    const slotDateInput = document.getElementById('slot-date');
    if (slotDateInput) {
        slotDateInput.addEventListener('change', function () {
            const d   = new Date(this.value + 'T00:00:00');
            const dow = d.getDay();
            if (dow === 0 || dow === 6) {
                alert('Please select a weekday (Monday–Friday). Weekends cannot be used for consultation slots.');
                this.value = '';
            }
        });
    }

    const startInput  = document.getElementById('slot-time-start');
    const endInput    = document.getElementById('slot-time-end');
    const durationSel = document.getElementById('duration-select');

    function autoFillEnd() {
        if (startInput && startInput.value && durationSel) {
            endInput.value = calcExpectedEnd(startInput.value, parseInt(durationSel.value));
        }
    }

    if (startInput) startInput.addEventListener('change', autoFillEnd);

    if (durationSel) {
        durationSel.addEventListener('change', function () {
            if (startInput && startInput.value) autoFillEnd();
        });
    }

    if (endInput) {
        endInput.addEventListener('change', function () {
            if (!startInput || !startInput.value || !durationSel) return;
            const expected = calcExpectedEnd(startInput.value, parseInt(durationSel.value));
            if (this.value && this.value !== expected) {
                alert('End time must be exactly ' + durationSel.value + ' minutes after the start time based on your selected duration.');
                this.value = '';
            }
        });
    }

    renderCalendar();
    if (typeof lucide !== 'undefined') lucide.createIcons();

    @if($errors->any())
        switchTab('weekly');
    @endif
});
</script>
@endpush
