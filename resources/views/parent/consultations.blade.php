@extends('layouts.parent')
@section('title', 'Consultations')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Face-to-Face Consultation Scheduling</h1>
        <p class="text-sm text-gray-500 mt-0.5">Schedule and manage consultation appointments with your child's teacher</p>
    </div>

    {{-- Info banner --}}
    <div class="flex items-start gap-3 p-4 rounded-xl text-sm text-blue-800 border border-blue-200" style="background:#eff6ff;">
        <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5 text-blue-500"></i>
        <p>Consultations are conducted face-to-face at the school premises. Please arrive on time for your scheduled appointment. To change your appointment, cancel and rebook a new slot.</p>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2 border-b border-gray-200">
        <button onclick="switchTab('book')" id="tab-book"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px
                       border-teal-600 text-teal-700">
            Book Consultation
        </button>
        <button onclick="switchTab('appointments')" id="tab-appointments"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px
                       border-transparent text-gray-500 hover:text-gray-700">
            My Appointments
        </button>
    </div>

    {{-- TAB 1: Book Consultation --}}
    <div id="panel-book" class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Left: teacher select + calendar --}}
        <div class="lg:col-span-3 space-y-4">

            @if($teachers->isNotEmpty())
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Select Teacher</label>
                <select id="teacher-select"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white"
                        {{ $teachers->count() === 1 ? 'disabled' : '' }}>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Select Date</label>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <button onclick="prevMonth()" class="p-1 rounded hover:bg-gray-100 transition-colors">
                            <i data-lucide="chevron-left" class="w-5 h-5 text-gray-600"></i>
                        </button>
                        <span id="cal-month-label" class="font-semibold text-gray-800 text-sm"></span>
                        <button onclick="nextMonth()" class="p-1 rounded hover:bg-gray-100 transition-colors">
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-600"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-0 text-center mb-1">
                        @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                            <div class="text-[11px] font-medium text-gray-400 py-1">{{ $d }}</div>
                        @endforeach
                    </div>
                    <div id="cal-grid" class="grid grid-cols-7 gap-0 text-center"></div>
                </div>
            </div>
        </div>

        {{-- Right: available slots --}}
        <div class="lg:col-span-2">
            <h2 class="text-sm font-semibold text-gray-700 mb-2">Available Time Slots</h2>
            <div id="slots-panel" class="space-y-2">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center text-gray-400 text-sm">
                    <i data-lucide="calendar" class="w-7 h-7 mx-auto mb-2 opacity-30"></i>
                    Select a date to see available slots.
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: My Appointments --}}
    <div id="panel-appointments" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Teacher</th>
                            <th class="px-6 py-4 font-medium">Date</th>
                            <th class="px-6 py-4 font-medium">Time</th>
                            <th class="px-6 py-4 font-medium">Purpose</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $booking->teacher_name }}</td>
                            <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                {{ $booking->scheduled_date ? date('M d, Y', strtotime($booking->scheduled_date)) : 'Slot Removed' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                @if($booking->time_start && $booking->time_end)
                                    {{ date('g:i A', strtotime($booking->time_start)) }}
                                    – {{ date('g:i A', strtotime($booking->time_end)) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $booking->purpose_of_meeting }}</td>
                            <td class="px-6 py-4">
                                @if($booking->status === 'Confirmed')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">Confirmed</span>
                                @elseif($booking->status === 'Pending')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
                                @elseif($booking->status === 'Cancelled')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700 border border-rose-200">Cancelled</span>
                                @elseif($booking->status === 'Rejected')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">Rejected</span>
                                @elseif($booking->status === 'Completed')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">Completed</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">{{ $booking->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(in_array($booking->status, ['Pending', 'Confirmed']))
                                    <form method="POST"
                                          action="{{ route('parent.consultations.cancel', $booking->id) }}"
                                          onsubmit="return confirm('Cancel this booking? The slot will be released for others to book.')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 text-xs font-medium text-rose-600 border border-rose-200 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors">
                                            Cancel
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                <i data-lucide="calendar" class="w-7 h-7 mx-auto mb-2 opacity-30"></i>
                                No appointments booked yet.
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

@push('scripts')
<script>
const availableDates = new Set(@json($availableDates));
const slotsUrl      = "{{ route('parent.consultations.slots') }}";
const bookUrl       = "{{ route('parent.consultations.store') }}";
const csrfToken     = document.querySelector('meta[name="csrf-token"]').content;

let currentYear  = new Date().getFullYear();
let currentMonth = new Date().getMonth();
let selectedDate = null;

const monthNames = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];

function renderCalendar() {
    const label = document.getElementById('cal-month-label');
    const grid  = document.getElementById('cal-grid');
    label.textContent = monthNames[currentMonth] + ' ' + currentYear;
    grid.innerHTML = '';

    const today     = new Date(); today.setHours(0,0,0,0);
    const firstDay  = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMon = new Date(currentYear, currentMonth + 1, 0).getDate();

    for (let i = 0; i < firstDay; i++) {
        grid.insertAdjacentHTML('beforeend', '<div></div>');
    }

    for (let d = 1; d <= daysInMon; d++) {
        const dateObj  = new Date(currentYear, currentMonth, d);
        const yyyy     = dateObj.getFullYear();
        const mm       = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd       = String(dateObj.getDate()).padStart(2, '0');
        const dateStr  = `${yyyy}-${mm}-${dd}`;
        const isPast   = dateObj < today;
        const hasSlot  = availableDates.has(dateStr);
        const isSelect = dateStr === selectedDate;

        let cls = 'relative flex items-center justify-center w-8 h-8 mx-auto text-sm rounded-full ';
        if (isSelect)     cls += 'bg-teal-600 text-white font-semibold';
        else if (isPast)  cls += 'text-gray-300 cursor-default';
        else              cls += 'text-gray-700 hover:bg-teal-50 cursor-pointer';

        const dot = hasSlot && !isPast
            ? '<span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-green-500"></span>'
            : '';

        grid.insertAdjacentHTML('beforeend',
            `<div class="py-0.5">
                <div class="${cls}" ${!isPast ? `onclick="selectDate('${dateStr}')"` : ''}>
                    ${d}${dot}
                </div>
             </div>`
        );
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function selectDate(dateStr) {
    selectedDate = dateStr;
    renderCalendar();

    const teacherId = document.getElementById('teacher-select')?.value ?? '';
    if (!teacherId) return;

    const panel = document.getElementById('slots-panel');
    panel.innerHTML = '<div class="text-center text-gray-400 text-sm py-6">Loading slots…</div>';

    fetch(`${slotsUrl}?teacher_id=${teacherId}&date=${dateStr}`)
        .then(r => r.json())
        .then(slots => {
            if (!slots.length) {
                panel.innerHTML = '<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-center text-gray-400 text-sm">No available slots on this date.</div>';
                return;
            }
            panel.innerHTML = slots.map(slot => `
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4" id="slot-${slot.id}">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-800">
                            ${fmtTime(slot.time_start)} – ${fmtTime(slot.time_end)}
                        </span>
                        <button onclick="openBookForm(${slot.id})"
                                class="px-3 py-1.5 text-xs font-medium text-white rounded-lg transition-colors hover:opacity-80"
                                style="background:#14b8a6;">
                            Book
                        </button>
                    </div>
                    <div id="book-form-${slot.id}" class="hidden mt-3 space-y-2">
                        <form method="POST" action="${bookUrl}">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="slot_id" value="${slot.id}">
                            <textarea name="purpose_of_meeting" required maxlength="300" rows="3"
                                      placeholder="Purpose of meeting…"
                                      oninput="document.getElementById('counter-${slot.id}').textContent = this.value.length + ' / 300 characters'"
                                      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none resize-none"></textarea>
                            <p id="counter-${slot.id}" style="font-size:11px;color:#9ca3af;text-align:right;margin-top:2px;">0 / 300 characters</p>
                            <div class="flex gap-2 pt-1">
                                <button type="button" onclick="closeBookForm(${slot.id})"
                                        class="px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium text-white rounded-lg transition-colors"
                                        style="background:#1a2332;">
                                    Confirm Booking
                                </button>
                            </div>
                        </form>
                    </div>
                </div>`).join('');
        })
        .catch(() => {
            panel.innerHTML = '<div class="text-center text-red-500 text-sm py-4">Failed to load slots.</div>';
        });
}

function fmtTime(t) {
    const [h, m] = t.split(':');
    const hh = parseInt(h);
    const ampm = hh >= 12 ? 'PM' : 'AM';
    return `${(hh % 12) || 12}:${m} ${ampm}`;
}

function openBookForm(slotId) {
    document.getElementById(`book-form-${slotId}`).classList.remove('hidden');
}
function closeBookForm(slotId) {
    document.getElementById(`book-form-${slotId}`).classList.add('hidden');
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

function switchTab(tab) {
    const panels = { book: 'panel-book', appointments: 'panel-appointments' };
    const tabs   = { book: 'tab-book',   appointments: 'tab-appointments' };
    Object.keys(panels).forEach(key => {
        document.getElementById(panels[key]).classList.toggle('hidden', key !== tab);
        const btn = document.getElementById(tabs[key]);
        if (key === tab) {
            btn.classList.replace('border-transparent', 'border-teal-600');
            btn.classList.replace('text-gray-500', 'text-teal-700');
        } else {
            btn.classList.replace('border-teal-600', 'border-transparent');
            btn.classList.replace('text-teal-700', 'text-gray-500');
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    renderCalendar();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
