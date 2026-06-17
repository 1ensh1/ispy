@extends('layouts.parent')
@section('title', 'Messaging')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col"
     style="height: calc(100vh - 172px); min-height: 480px;">

    @if($teacher && $engagement)

        {{-- Chat header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200 shrink-0">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-white shrink-0"
                 style="background:#1e3a5f;">
                {{ strtoupper(substr($teacher->name, 0, 1)) }}
            </div>
            <div>
                <div class="font-semibold text-gray-900 text-sm">{{ $teacher->name }}</div>
                <div class="text-xs text-gray-500">Teacher · {{ $student?->classList?->class_name ?? '' }}</div>
            </div>
        </div>

        {{-- Chat messages --}}
        <div class="flex flex-col flex-1 overflow-y-auto px-5 py-4 space-y-3" id="chat-area">
            @forelse($messages as $msg)
                @php $isParent = $msg->sender_role === 'Parent'; @endphp
                <div class="flex flex-col w-full {{ $isParent ? 'items-end' : 'items-start' }}"
                     data-message-id="{{ $msg->id }}">
                    <div class="max-w-xs lg:max-w-md">
                        <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed
                                    {{ $isParent
                                        ? 'text-white rounded-br-sm'
                                        : 'text-gray-800 rounded-bl-sm' }}"
                             style="{{ $isParent ? 'background:#1e3a5f;' : 'background:#f1f3f4;' }}">
                            {{ $msg->message_body }}
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 {{ $isParent ? 'text-right' : '' }}">
                            {{ \Carbon\Carbon::parse($msg->sent_at)->format('h:i A') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-400 text-sm py-8">
                    <i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                    No messages yet. Start the conversation!
                </div>
            @endforelse
        </div>

        {{-- Input bar --}}
        <div class="border-t border-gray-200 px-4 py-3 shrink-0">
            <form method="POST" action="{{ route('parent.messaging.store') }}"
                  id="msg-form" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="engagement_id" value="{{ $engagement->id }}">
                <input type="text" name="message_body" id="msg-input" maxlength="2000"
                       placeholder="Type a message..."
                       class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                <button type="submit" id="msg-send"
                        class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0 transition-colors hover:opacity-80 opacity-50 cursor-not-allowed"
                        style="background:#1e3a5f;" disabled>
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

    @else
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400 select-none">
            <i data-lucide="message-square" class="w-12 h-12 mb-3 opacity-20"></i>
            <p class="text-sm">No teacher assigned to your child's class yet.</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatArea = document.getElementById('chat-area');
    if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;

    // --- Realtime messaging (AJAX send + polling) ---
    const ENGAGEMENT_ID = {{ $engagement?->id ?? 0 }};
    const POLL_URL  = "{{ route('parent.messaging.poll') }}";
    const STORE_URL = "{{ route('parent.messaging.ajax.store') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Guard: nothing to do without a valid, active engagement.
    if (!chatArea || !Number.isInteger(ENGAGEMENT_ID) || ENGAGEMENT_ID <= 0) return;

    // Initialize lastMessageId from the highest data-message-id already rendered.
    let lastMessageId = 0;
    chatArea.querySelectorAll('[data-message-id]').forEach(function (el) {
        const id = parseInt(el.getAttribute('data-message-id'), 10);
        if (Number.isInteger(id) && id > lastMessageId) lastMessageId = id;
    });

    const renderedIds = new Set();
    chatArea.querySelectorAll('[data-message-id]').forEach(function (el) {
        const id = parseInt(el.getAttribute('data-message-id'), 10);
        if (!isNaN(id)) renderedIds.add(id);
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    // Match Blade's Carbon 'h:i A' formatting. The API returns UTC ISO whose
    // wall-clock numbers equal the stored app-timezone time, so read UTC parts
    // directly to avoid a browser-timezone shift vs. server-rendered bubbles.
    function formatTime(iso) {
        const d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        let h = d.getUTCHours();
        const m = d.getUTCMinutes();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12; if (h === 0) h = 12;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ' ' + ampm;
    }

    function appendMessage(msg) {
        const msgId = parseInt(msg.id, 10);
        if (renderedIds.has(msgId)) return;
        const isParent = msg.sender_role === 'Parent';
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col w-full ' + (isParent ? 'items-end' : 'items-start');
        wrapper.setAttribute('data-message-id', msgId);
        wrapper.innerHTML =
            '<div class="max-w-xs lg:max-w-md">' +
                '<div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed ' +
                     (isParent ? 'text-white rounded-br-sm' : 'text-gray-800 rounded-bl-sm') + '" ' +
                     'style="' + (isParent ? 'background:#1e3a5f;' : 'background:#f1f3f4;') + '">' +
                    escapeHtml(msg.message_body) +
                '</div>' +
                '<p class="text-[10px] text-gray-400 mt-1 ' + (isParent ? 'text-right' : '') + '">' +
                    formatTime(msg.sent_at) +
                '</p>' +
            '</div>';
        chatArea.appendChild(wrapper);
        renderedIds.add(msgId);
    }

    function scrollToBottom() {
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    // --- AJAX send ---
    const form  = document.getElementById('msg-form');
    const input = document.getElementById('msg-input');
    if (form && input) {
        const submitBtn = form.querySelector('button[type="submit"]');

        // UX layer: gray out + disable the send button while the input is empty.
        function syncSendButton() {
            if (!submitBtn) return;
            const empty = input.value.trim() === '';
            submitBtn.disabled = empty;
            submitBtn.classList.toggle('opacity-50', empty);
            submitBtn.classList.toggle('cursor-not-allowed', empty);
        }

        input.addEventListener('input', syncSendButton);
        syncSendButton();

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const body = input.value.trim();
            if (body === '') return;

            if (submitBtn) submitBtn.disabled = true;
            fetch(STORE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ engagement_id: ENGAGEMENT_ID, message_body: body }),
            })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!data || data.success !== true || !data.message) {
                    throw new Error('Unexpected response');
                }
                input.value = '';
                appendMessage(data.message);
                lastMessageId = Math.max(lastMessageId, parseInt(data.message.id, 10));
                scrollToBottom();
                if (window.lucide) lucide.createIcons();
            })
            .catch(function () {
                alert('Failed to send message. Please try again.');
            })
            .finally(function () {
                // Re-evaluate from the current input value: stays disabled after a
                // successful send (input cleared), re-enables if text remains.
                syncSendButton();
                input.focus();
            });
        });
    }

    // --- Polling ---
    let pollTimer = null;

    function poll() {
        fetch(POLL_URL + '?engagement_id=' + ENGAGEMENT_ID + '&last_id=' + lastMessageId, {
            headers: { 'Accept': 'application/json' },
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            const messages = (data && data.messages) || [];
            if (messages.length === 0) return;
            messages.forEach(function (msg) {
                appendMessage(msg);
            });
            lastMessageId = Math.max(lastMessageId, ...messages.map(function (m) { return parseInt(m.id, 10); }));
            scrollToBottom();
            if (window.lucide) lucide.createIcons();
        })
        .catch(function (err) {
            console.error('Message poll failed:', err);
        });
    }

    function startPolling() {
        if (pollTimer === null) pollTimer = setInterval(poll, 4000);
    }

    function stopPolling() {
        if (pollTimer !== null) { clearInterval(pollTimer); pollTimer = null; }
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            stopPolling();
        } else {
            startPolling();
        }
    });

    if (document.visibilityState !== 'hidden') startPolling();
});
</script>
@endpush
