@extends('layouts.teacher')
@section('title', 'Consultation Availability')

@section('content')

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
            <form method="POST" action="{{ route('teacher.consultation.maxAppointments') }}" id="max-appt-form">
                @csrf
                <select name="max_per_day"
                        onchange="document.getElementById('max-appt-form').submit()"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm
                               focus:ring-2 focus:ring-indigo-500 outline-none bg-white text-gray-700">
                    @foreach([2, 4, 6, 8] as $opt)
                        <option value="{{ $opt }}" {{ $maxPerDay == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </form>
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

            {{-- Week navigation (client-side) --}}
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <button type="button" id="btn-prev-week"
                        style="padding:6px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;white-space:nowrap;">
                    ← Previous Week
                </button>
                <span id="week-label" style="font-weight:600;font-size:14px;color:#111827;text-align:center;"></span>
                <button type="button" id="btn-next-week"
                        style="padding:6px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;white-space:nowrap;">
                    Next Week →
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 w-28">Time</th>
                                <th id="week-th-0" class="px-4 py-3 text-center font-medium text-gray-500"></th>
                                <th id="week-th-1" class="px-4 py-3 text-center font-medium text-gray-500"></th>
                                <th id="week-th-2" class="px-4 py-3 text-center font-medium text-gray-500"></th>
                                <th id="week-th-3" class="px-4 py-3 text-center font-medium text-gray-500"></th>
                                <th id="week-th-4" class="px-4 py-3 text-center font-medium text-gray-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach([8,9,10,11,12,13,14,15,16] as $hour)
                                @php
                                    if ($hour < 12)       $timeLabel = $hour . ':00 AM';
                                    elseif ($hour === 12) $timeLabel = '12:00 PM';
                                    else                  $timeLabel = ($hour - 12) . ':00 PM';
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-medium whitespace-nowrap text-xs">{{ $timeLabel }}</td>
                                    <td id="cell-{{ $hour }}-0" class="px-4 py-3 text-center"><span class="text-gray-200 text-base leading-none">—</span></td>
                                    <td id="cell-{{ $hour }}-1" class="px-4 py-3 text-center"><span class="text-gray-200 text-base leading-none">—</span></td>
                                    <td id="cell-{{ $hour }}-2" class="px-4 py-3 text-center"><span class="text-gray-200 text-base leading-none">—</span></td>
                                    <td id="cell-{{ $hour }}-3" class="px-4 py-3 text-center"><span class="text-gray-200 text-base leading-none">—</span></td>
                                    <td id="cell-{{ $hour }}-4" class="px-4 py-3 text-center"><span class="text-gray-200 text-base leading-none">—</span></td>
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
                               min="08:00" max="17:00"
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
                               min="08:00" max="17:00"
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
            <div class="flex gap-5 items-start">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 shrink-0" style="width:300px;">
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
                        Has scheduled slots
                    </div>
                </div>
                <div class="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-5" style="min-height:200px;">
                    <div id="cal-detail-panel" class="flex items-center justify-center text-gray-400 text-sm" style="min-height:160px;">
                        Select a date to view schedule details.
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tab: Appointments ── --}}
        <div id="panel-appointments" class="mt-4 hidden">
            <div class="flex justify-end mb-3">
                <select onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');u.searchParams.set('tab','appointments');window.location.assign(u.toString());})(this.value)"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400/30">
                    <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20 / page</option>
                    <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50 / page</option>
                </select>
            </div>
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
                                    @elseif($booking->status === 'Rejected')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                     bg-red-100 text-red-700 border border-red-200">
                                            Rejected
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
                                        <div class="flex items-center gap-1 flex-wrap">
                                            <form method="POST" action="{{ route('teacher.consultation.updateStatus') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                <input type="hidden" name="status" value="Confirmed">
                                                <button type="submit"
                                                        style="background:#16a34a;color:white;padding:3px 10px;font-size:12px;border-radius:6px;border:none;cursor:pointer;">
                                                    Confirm
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('teacher.consultation.booking.reject', $booking->id) }}" class="inline"
                                                  onsubmit="return confirm('Reject this booking?')">
                                                @csrf
                                                <button type="submit"
                                                        style="background:#dc2626;color:white;padding:3px 10px;font-size:12px;border-radius:6px;border:none;cursor:pointer;">
                                                    Reject
                                                </button>
                                            </form>
                                            @if($booking->slot_id)
                                            <form method="POST" action="{{ route('teacher.consultation.destroy', $booking->slot_id) }}" class="inline"
                                                  onsubmit="return confirm('Delete this slot? The booking will be cancelled.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        style="background:#dc2626;color:white;padding:3px 10px;font-size:12px;border-radius:6px;border:none;cursor:pointer;">
                                                    Cancel
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    @elseif($booking->status === 'Confirmed')
                                        @php
                                            $slotEnd = $booking->scheduled_date
                                                ? \Carbon\Carbon::parse($booking->scheduled_date . ' ' . $booking->time_end)
                                                : null;
                                        @endphp
                                        @if($slotEnd && $slotEnd->isPast())
                                            <form method="POST" action="{{ route('teacher.consultation.updateStatus') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                <input type="hidden" name="status" value="Completed">
                                                <button type="submit"
                                                        style="background:#f97316;color:white;padding:3px 10px;font-size:12px;border-radius:6px;border:none;cursor:pointer;">
                                                    Complete
                                                </button>
                                            </form>
                                        @else
                                            <div class="flex items-center gap-1 flex-wrap">
                                                <span style="background-color:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:9999px;font-size:12px;font-weight:500;">Upcoming</span>
                                                @if($booking->slot_id)
                                                <form method="POST" action="{{ route('teacher.consultation.destroy', $booking->slot_id) }}" class="inline"
                                                      onsubmit="return confirm('Delete this slot? The booking will be cancelled.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            style="background:#dc2626;color:white;padding:3px 10px;font-size:12px;border-radius:6px;border:none;cursor:pointer;">
                                                        Cancel
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        @endif
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
                @if($bookings->hasPages())
                    <div class="px-4 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Data from PHP ─────────────────────────────────────────────────────────────
const ALL_SLOTS       = @json($allSlots);
const TODAY_MONDAY    = '{{ $todayMonday->format('Y-m-d') }}';
const CSRF_TOKEN      = '{{ csrf_token() }}';
const DESTROY_URL_TPL  = '{{ route('teacher.consultation.destroy',          ['slot'    => 'SLOT_ID'])    }}';
const CONFIRM_URL_TPL  = '{{ route('teacher.consultation.booking.confirm',  ['booking' => 'BOOKING_ID']) }}';
const REJECT_URL_TPL   = '{{ route('teacher.consultation.booking.reject',   ['booking' => 'BOOKING_ID']) }}';
const SAVE_URL         = '{{ route('teacher.consultation.save') }}';
const TODAY           = isoDate(new Date()); // "YYYY-MM-DD" — used for past-slot detection

// ── Week grid constants ───────────────────────────────────────────────────────
const HOURS       = [8, 9, 10, 11, 12, 13, 14, 15, 16];
const DAY_ABBRS   = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
const MONTH_NAMES = ['January','February','March','April','May','June',
                     'July','August','September','October','November','December'];
const MONTH_ABBRS = ['Jan','Feb','Mar','Apr','May','Jun',
                     'Jul','Aug','Sep','Oct','Nov','Dec'];

let currentMonday = parseDate(TODAY_MONDAY);

// ── Helpers ───────────────────────────────────────────────────────────────────
function parseDate(str) {
    const [y, m, d] = str.split('-').map(Number);
    return new Date(y, m - 1, d);
}

function isoDate(d) {
    return d.getFullYear()
        + '-' + String(d.getMonth() + 1).padStart(2, '0')
        + '-' + String(d.getDate()).padStart(2, '0');
}

function fmtTime(timeStr) {
    // accepts "HH:mm" or "HH:mm:ss"
    const parts = timeStr.split(':');
    const h     = parseInt(parts[0], 10);
    const m     = parts[1];
    const sfx   = h >= 12 ? 'PM' : 'AM';
    const h12   = h % 12 || 12;
    return h12 + (m === '00' ? '' : ':' + m) + ' ' + sfx;
}

const BTN = 'border:none;cursor:pointer;border-radius:5px;padding:2px 8px;font-size:11px;margin-left:2px;';

function renderCancelForm(slot) {
    const url = DESTROY_URL_TPL.replace('SLOT_ID', slot.id);
    return '<form method="POST" action="' + url + '"'
        + ' onsubmit="return confirm(\'Delete this slot?\')" class="inline">'
        + '<input type="hidden" name="_token" value="' + CSRF_TOKEN + '">'
        + '<input type="hidden" name="_method" value="DELETE">'
        + '<button type="submit" style="background-color:#dc2626;color:white;' + BTN + '">Cancel</button>'
        + '</form>';
}

function renderConfirmForm(slot) {
    const url = CONFIRM_URL_TPL.replace('BOOKING_ID', slot.booking_id);
    return '<form method="POST" action="' + url + '" class="inline">'
        + '<input type="hidden" name="_token" value="' + CSRF_TOKEN + '">'
        + '<button type="submit" style="background-color:#16a34a;color:white;' + BTN + '">Confirm</button>'
        + '</form>';
}

function renderRejectForm(slot) {
    const url = REJECT_URL_TPL.replace('BOOKING_ID', slot.booking_id);
    return '<form method="POST" action="' + url + '" onsubmit="return confirm(\'Reject this booking?\')" class="inline">'
        + '<input type="hidden" name="_token" value="' + CSRF_TOKEN + '">'
        + '<button type="submit" style="background-color:#dc2626;color:white;' + BTN + '">Reject</button>'
        + '</form>';
}

// ── Week grid renderer ────────────────────────────────────────────────────────
function renderWeek(monday) {
    currentMonday = monday;

    // Five weekday Date objects
    const weekDates = [];
    for (let i = 0; i < 5; i++) {
        const d = new Date(monday);
        d.setDate(d.getDate() + i);
        weekDates.push(d);
    }
    const friday = weekDates[4];

    // Week label: "June 2026 — Week 2 (Jun 8 – Jun 14)"
    const weekNum = Math.ceil(monday.getDate() / 7);
    document.getElementById('week-label').textContent =
        MONTH_NAMES[monday.getMonth()] + ' ' + monday.getFullYear()
        + ' — Week ' + weekNum
        + ' (' + MONTH_ABBRS[monday.getMonth()] + ' ' + monday.getDate()
        + ' – ' + MONTH_ABBRS[friday.getMonth()] + ' ' + friday.getDate() + ')';

    // Column headers: "Mon / Jun 8"
    weekDates.forEach(function (d, i) {
        const th = document.getElementById('week-th-' + i);
        if (!th) return;
        th.innerHTML = '<div>' + DAY_ABBRS[i] + '</div>'
            + '<div style="font-size:11px;color:#9ca3af;font-weight:400;margin-top:2px;">'
            + MONTH_ABBRS[d.getMonth()] + ' ' + d.getDate() + '</div>';
    });

    // Build grid lookup: dateStr -> hour -> slots[]
    const mondayStr = isoDate(monday);
    const fridayStr = isoDate(friday);
    const weekSlots = ALL_SLOTS.filter(function (s) {
        return s.scheduled_date >= mondayStr && s.scheduled_date <= fridayStr;
    });

    const grid = {};
    weekSlots.forEach(function (slot) {
        const hour = parseInt(slot.time_start.split(':')[0], 10);
        if (!grid[slot.scheduled_date])       grid[slot.scheduled_date]       = {};
        if (!grid[slot.scheduled_date][hour]) grid[slot.scheduled_date][hour] = [];
        grid[slot.scheduled_date][hour].push(slot);
    });

    // Re-render every cell
    HOURS.forEach(function (hour) {
        weekDates.forEach(function (d, colIdx) {
            const cell = document.getElementById('cell-' + hour + '-' + colIdx);
            if (!cell) return;
            const dateStr   = isoDate(d);
            const cellSlots = (grid[dateStr] && grid[dateStr][hour]) ? grid[dateStr][hour] : [];

            if (cellSlots.length === 0) {
                cell.innerHTML = '<span class="text-gray-200 text-base leading-none">—</span>';
                return;
            }

            let html = '<div class="flex flex-col items-center gap-1">';
            cellSlots.forEach(function (slot) {
                const timeRange = fmtTime(slot.time_start) + '–' + fmtTime(slot.time_end);
                const isPast    = slot.scheduled_date < TODAY;
                const bs        = slot.booking_status;

                html += '<div class="flex flex-col items-center gap-0.5">';

                // Parent name · time range on one compact line
                const headerTxt   = (slot.parent_name && bs)
                    ? slot.parent_name + ' · ' + timeRange
                    : timeRange;
                const headerColor = (slot.parent_name && bs) ? '#6b7280' : '#9ca3af';
                html += '<span style="font-size:11px;color:' + headerColor + ';">' + headerTxt + '</span>';

                const BADGE = 'border-radius:9999px;padding:2px 10px;font-size:11px;font-weight:500;color:white;';

                if (bs === 'Completed') {
                    html += '<span style="background-color:#16a34a;' + BADGE + '">Completed</span>';

                } else if (bs === 'Cancelled') {
                    html += '<span style="background-color:#6b7280;' + BADGE + '">Cancelled</span>';

                } else if (bs === 'Rejected') {
                    html += '<span style="background-color:#dc2626;' + BADGE + '">Rejected</span>';

                } else if (bs === 'Confirmed') {
                    html += '<div class="flex items-center gap-1">'
                        + '<span style="background-color:#16a34a;' + BADGE + '">Confirmed</span>'
                        + renderCancelForm(slot)
                        + '</div>';

                } else if (bs === 'Pending') {
                    html += '<div class="flex items-center gap-1">'
                        + '<span style="background-color:#d97706;' + BADGE + '">Pending</span>'
                        + renderConfirmForm(slot)
                        + renderRejectForm(slot)
                        + renderCancelForm(slot)
                        + '</div>';

                } else if (isPast) {
                    html += '<span style="background-color:#9ca3af;' + BADGE + '">No Booking</span>';

                } else {
                    const availCls = slot.is_available
                        ? 'bg-green-100 text-green-700 border-green-200'
                        : 'bg-gray-100 text-gray-500 border-gray-200';
                    const availTxt = slot.is_available ? 'Available' : 'Unavailable';
                    const availVal = slot.is_available ? '1' : '0';
                    html += '<div class="flex items-center gap-1">'
                        + '<button type="button"'
                        +   ' data-slot-id="' + slot.id + '"'
                        +   ' data-available="' + availVal + '"'
                        +   ' onclick="toggleSlot(this)"'
                        +   ' class="slot-toggle px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer border ' + availCls + '">'
                        + availTxt
                        + '</button>'
                        + renderCancelForm(slot)
                        + '</div>';
                }

                html += '</div>';
            });
            html += '</div>';
            cell.innerHTML = html;
        });
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ── Slot availability toggle ──────────────────────────────────────────────────
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
    // Mirror change into ALL_SLOTS so re-renders reflect the toggle
    const s = ALL_SLOTS.find(function (s) { return s.id == id; });
    if (s) s.is_available = next;
}

function saveSchedule() {
    if (Object.keys(slotChanges).length === 0) {
        alert('No availability changes to save.');
        return;
    }
    const form  = document.createElement('form');
    form.method = 'POST';
    form.action = SAVE_URL;
    const csrf  = document.createElement('input');
    csrf.type   = 'hidden'; csrf.name = '_token'; csrf.value = CSRF_TOKEN;
    form.appendChild(csrf);
    for (const [id, val] of Object.entries(slotChanges)) {
        const inp = document.createElement('input');
        inp.type  = 'hidden'; inp.name = 'slots[' + id + ']'; inp.value = val;
        form.appendChild(inp);
    }
    document.body.appendChild(form);
    form.submit();
}

// ── Calendar view ─────────────────────────────────────────────────────────────
const slotDates     = new Set(@json($slotDates));
const CAL_SLOTS     = @json($calendarSlots);
let currentYear     = new Date().getFullYear();
let currentMonth    = new Date().getMonth();
let selectedCalDate = null;

function renderCalendar() {
    const label = document.getElementById('cal-month-label');
    const grid  = document.getElementById('cal-grid');
    if (!label || !grid) return;

    label.textContent = MONTH_NAMES[currentMonth] + ' ' + currentYear;
    grid.innerHTML    = '';

    const today     = new Date(); today.setHours(0, 0, 0, 0);
    const firstDay  = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMon = new Date(currentYear, currentMonth + 1, 0).getDate();

    for (let i = 0; i < firstDay; i++) {
        grid.insertAdjacentHTML('beforeend', '<div></div>');
    }
    for (let d = 1; d <= daysInMon; d++) {
        const dateObj    = new Date(currentYear, currentMonth, d);
        const dateStr    = isoDate(dateObj);
        const hasSlot    = slotDates.has(dateStr);
        const isPast     = dateObj < today;
        const isSelected = dateStr === selectedCalDate;
        const isClickable = !isPast || hasSlot;

        let cls = 'relative flex items-center justify-center w-8 h-8 mx-auto text-sm rounded-full ';
        if (isSelected)        cls += 'bg-indigo-600 text-white font-semibold';
        else if (!isClickable) cls += 'text-gray-300 cursor-default';
        else if (isPast)       cls += 'text-gray-400 hover:bg-gray-100 cursor-pointer';
        else                   cls += 'text-gray-700 hover:bg-gray-100 cursor-pointer';

        const dot = hasSlot
            ? '<span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-green-500"></span>'
            : '';

        const clickAttr = isClickable ? `onclick="selectCalendarDate('${dateStr}')"` : '';

        grid.insertAdjacentHTML('beforeend',
            `<div class="py-0.5"><div class="${cls}" ${clickAttr}>${d}${dot}</div></div>`
        );
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function selectCalendarDate(dateStr) {
    selectedCalDate = dateStr;
    renderCalendar();
    renderDayDetail(dateStr);
}

function renderDayDetail(dateStr) {
    const panel = document.getElementById('cal-detail-panel');
    if (!panel) return;

    const dateObj  = parseDate(dateStr);
    const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const header   = dayNames[dateObj.getDay()] + ', '
        + MONTH_NAMES[dateObj.getMonth()] + ' ' + dateObj.getDate() + ', ' + dateObj.getFullYear();

    const daySlots = CAL_SLOTS.filter(function (s) { return s.scheduled_date === dateStr; });
    daySlots.sort(function (a, b) { return a.time_start.localeCompare(b.time_start); });

    let html = '<div style="width:100%;">';
    html += '<p style="font-weight:600;font-size:15px;color:#111827;margin-bottom:16px;">' + header + '</p>';

    if (daySlots.length === 0) {
        html += '<div style="text-align:center;color:#9ca3af;padding:40px 0;font-size:14px;">No schedules for this date.</div>';
    } else {
        html += '<div style="display:flex;flex-direction:column;gap:10px;">';
        daySlots.forEach(function (slot) {
            const timeRange = fmtTime(slot.time_start) + ' – ' + fmtTime(slot.time_end);
            const bs = slot.booking_status;

            var bgColor = '#f3f4f6', textColor = '#6b7280', badgeText = 'Unknown';
            if (!bs) {
                bgColor = '#dcfce7'; textColor = '#15803d'; badgeText = 'Available';
            } else if (bs === 'Pending') {
                bgColor = '#fef3c7'; textColor = '#92400e'; badgeText = 'Pending';
            } else if (bs === 'Confirmed') {
                bgColor = '#d1fae5'; textColor = '#065f46'; badgeText = 'Confirmed';
            } else if (bs === 'Completed') {
                bgColor = '#f3f4f6'; textColor = '#6b7280'; badgeText = 'Completed';
            } else if (bs === 'Cancelled') {
                bgColor = '#ffe4e6'; textColor = '#be123c'; badgeText = 'Cancelled';
            } else if (bs === 'Rejected') {
                bgColor = '#fee2e2'; textColor = '#b91c1c'; badgeText = 'Rejected';
            }

            html += '<div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;">';
            html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">';
            html += '<span style="font-weight:600;font-size:13px;color:#374151;">' + timeRange + '</span>';
            html += '<span style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:500;background:' + bgColor + ';color:' + textColor + ';">' + badgeText + '</span>';
            html += '</div>';

            if (bs && slot.parent_name) {
                html += '<div style="font-size:12px;color:#374151;margin-bottom:3px;"><strong>Parent:</strong> ' + slot.parent_name + '</div>';
                if (slot.student_name) {
                    html += '<div style="font-size:12px;color:#374151;margin-bottom:3px;"><strong>Student:</strong> ' + slot.student_name + '</div>';
                }
                if (slot.purpose) {
                    html += '<div style="font-size:12px;color:#6b7280;margin-top:6px;font-style:italic;">' + slot.purpose + '</div>';
                }
            } else if (!bs) {
                html += '<div style="font-size:12px;color:#9ca3af;">No booking yet</div>';
            }

            html += '</div>';
        });
        html += '</div>';
    }
    html += '</div>';

    panel.innerHTML = html;
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

// ── Tab switching ─────────────────────────────────────────────────────────────
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

// ── Add Slot form helpers ─────────────────────────────────────────────────────
function calcExpectedEnd(startVal, durationMins) {
    const [h, m] = startVal.split(':').map(Number);
    const total  = h * 60 + m + durationMins;
    const eh     = Math.floor(total / 60) % 24;
    const em     = total % 60;
    return String(eh).padStart(2, '0') + ':' + String(em).padStart(2, '0');
}

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    // Week navigation buttons
    document.getElementById('btn-prev-week').addEventListener('click', function () {
        const prev = new Date(currentMonday);
        prev.setDate(prev.getDate() - 7);
        renderWeek(prev);
    });
    document.getElementById('btn-next-week').addEventListener('click', function () {
        const next = new Date(currentMonday);
        next.setDate(next.getDate() + 7);
        renderWeek(next);
    });

    // Initial grid render
    renderWeek(parseDate(TODAY_MONDAY));

    // Add Slot date validation
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

    if (startInput)  startInput.addEventListener('change', autoFillEnd);
    if (durationSel) durationSel.addEventListener('change', function () {
        if (startInput && startInput.value) autoFillEnd();
    });
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
    @else
        switchTab('{{ $activeTab }}');
    @endif
});
</script>
@endpush
