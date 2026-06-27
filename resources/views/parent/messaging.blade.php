@extends('layouts.parent')
@section('title', 'Messaging')

@section('content')
<div class="flex gap-0 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
     style="height: calc(100vh - 172px); min-height: 480px;">

    {{-- Left panel: conversation list --}}
    <div class="flex flex-col border-r border-gray-200 shrink-0" style="width:280px;">
        <div class="px-3 py-3 border-b border-gray-100 shrink-0">
            <h2 class="text-sm font-semibold text-gray-800 px-1">Messages</h2>
        </div>

        <div class="overflow-y-auto divide-y divide-gray-50" id="conv-list" style="flex:1;">
            @forelse($conversations as $conv)
                @php
                    $convTeacher = $conv->teacher?->name ?? 'Unknown Teacher';
                    $initials    = strtoupper(substr($convTeacher, 0, 1));
                    $lastMsg     = $conv->latestMessage;
                    $unread      = $conv->unreadCount;
                    $isActive    = $engagement?->id === $conv->id;
                @endphp
                <a href="{{ route('parent.messaging') }}?engagement_id={{ $conv->id }}"
                   data-engagement-id="{{ $conv->id }}"
                   data-counterpart="{{ $convTeacher }}"
                   class="conv-item flex items-center gap-3 px-3 py-3.5 hover:bg-gray-50 transition-colors
                          {{ $isActive ? 'bg-teal-50 border-r-2 border-teal-600' : '' }}">

                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-white shrink-0"
                         style="background:#1e3a5f;">
                        {{ $initials }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $convTeacher }}</p>
                            <span class="conv-time text-[10px] text-gray-400 shrink-0">
                                {{ $lastMsg ? \Carbon\Carbon::parse($lastMsg->sent_at)->format('h:i A') : '' }}
                            </span>
                        </div>
                        <p class="conv-preview text-xs text-gray-500 truncate mt-0.5">
                            {{ $lastMsg
                                ? \Illuminate\Support\Str::limit($lastMsg->message_body, 50)
                                : 'No messages yet' }}
                        </p>
                    </div>

                    <div class="conv-unread w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                         style="background:#1e3a5f;{{ $unread > 0 ? '' : 'display:none;' }}">
                        <span class="text-[10px] text-white font-bold leading-none">
                            {{ $unread > 9 ? '9+' : $unread }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="px-4 py-10 text-center text-gray-400 text-sm">
                    <i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2 opacity-25"></i>
                    No conversations yet.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right panel: chat area --}}
    <div class="flex flex-col flex-1 min-w-0">

    @if($teacher && $engagement)

        {{-- Chat header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200 shrink-0">
            <div id="chat-header-avatar"
                 class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-white shrink-0"
                 style="background:#1e3a5f;">
                {{ strtoupper(substr($teacher->name, 0, 1)) }}
            </div>
            <div>
                <div id="chat-header-name" class="font-semibold text-gray-900 text-sm">{{ $teacher->name }}</div>
                <div id="chat-header-sub" class="text-xs text-gray-500">Teacher · {{ $activeClassName ?? '' }}</div>
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

    </div>{{-- /right panel --}}

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatArea = document.getElementById('chat-area');
    if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;

    // --- Realtime messaging (AJAX send + polling) ---
    // ENGAGEMENT_ID is reassigned on instant conversation switch (see below).
    let   ENGAGEMENT_ID = {{ $engagement?->id ?? 0 }};
    const POLL_URL   = "{{ route('parent.messaging.poll') }}";
    const THREAD_URL = "{{ route('parent.messaging.thread') }}";
    const STORE_URL  = "{{ route('parent.messaging.ajax.store') }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const ACTIVE_CLASSES = ['bg-teal-50', 'border-r-2', 'border-teal-600'];

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

    // --- Live sidebar preview + instant switching helpers ---
    function truncatePreview(str, n) {
        str = str == null ? '' : String(str);
        return str.length > n ? str.slice(0, n) + '...' : str;
    }

    function updateSidebarEntry(engId, body, sentAt) {
        const entry = document.querySelector('#conv-list [data-engagement-id="' + engId + '"]');
        if (!entry) return;
        const prev = entry.querySelector('.conv-preview');
        const time = entry.querySelector('.conv-time');
        if (prev) prev.textContent = truncatePreview(body, 50);
        if (time) time.textContent = formatTime(sentAt);
    }

    function setActiveEntry(entry) {
        document.querySelectorAll('#conv-list .conv-item').forEach(function (el) {
            ACTIVE_CLASSES.forEach(function (c) { el.classList.remove(c); });
        });
        ACTIVE_CLASSES.forEach(function (c) { entry.classList.add(c); });
        const badge = entry.querySelector('.conv-unread');
        if (badge) badge.style.display = 'none';
    }

    function updateHeader(data) {
        const nameEl = document.getElementById('chat-header-name');
        const subEl  = document.getElementById('chat-header-sub');
        const avEl   = document.getElementById('chat-header-avatar');
        const name   = data.counterpart_name || '';
        if (nameEl) nameEl.textContent = name;
        if (subEl)  subEl.textContent  = 'Teacher · ' + (data.class_name || '');
        if (avEl)   avEl.textContent   = (name || '?').charAt(0).toUpperCase();
    }

    function renderThread(data) {
        chatArea.innerHTML = '';
        renderedIds.clear();
        lastMessageId = 0;
        const msgs = (data && data.messages) || [];
        if (msgs.length === 0) {
            chatArea.innerHTML =
                '<div class="text-center text-gray-400 text-sm py-8">' +
                    '<i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>' +
                    'No messages yet. Start the conversation!' +
                '</div>';
        } else {
            msgs.forEach(function (m) {
                appendMessage(m);
                const id = parseInt(m.id, 10);
                if (Number.isInteger(id) && id > lastMessageId) lastMessageId = id;
            });
        }
        scrollToBottom();
        if (window.lucide) lucide.createIcons();
    }

    function showNotice(text) {
        let n = document.getElementById('msg-notice');
        if (!n) {
            n = document.createElement('div');
            n.id = 'msg-notice';
            n.className = 'fixed left-1/2 bottom-6 -translate-x-1/2 px-4 py-2 rounded-lg text-white text-sm shadow-lg';
            n.style.background = '#dc2626';
            n.style.zIndex = '50';
            document.body.appendChild(n);
        }
        n.textContent = text;
        n.style.display = 'block';
        clearTimeout(n._t);
        n._t = setTimeout(function () { n.style.display = 'none'; }, 3000);
    }

    function switchThread(engId, entry) {
        fetch(THREAD_URL + '?engagement_id=' + engId, { headers: { 'Accept': 'application/json' } })
        .then(function (res) {
            if (res.status === 403) throw new Error('forbidden');
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            ENGAGEMENT_ID = engId;
            const hidden = document.querySelector('#msg-form input[name="engagement_id"]');
            if (hidden) hidden.value = engId;
            updateHeader(data);
            renderThread(data);
            setActiveEntry(entry);
            try { history.replaceState(null, '', '?engagement_id=' + engId); } catch (e) {}
        })
        .catch(function (err) {
            showNotice(err && err.message === 'forbidden'
                ? 'This conversation is no longer available.'
                : 'Could not open this conversation. Please try again.');
        });
    }

    // Instant switch via event delegation on the conversation list.
    const convList = document.getElementById('conv-list');
    if (convList) {
        convList.addEventListener('click', function (e) {
            const entry = e.target.closest('.conv-item');
            if (!entry) return;
            e.preventDefault();
            const engId = parseInt(entry.getAttribute('data-engagement-id'), 10);
            if (!Number.isInteger(engId) || engId === ENGAGEMENT_ID) return;
            switchThread(engId, entry);
        });
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
                updateSidebarEntry(ENGAGEMENT_ID, data.message.message_body, data.message.sent_at);
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
            const lastMsg = messages[messages.length - 1];
            updateSidebarEntry(ENGAGEMENT_ID, lastMsg.message_body, lastMsg.sent_at);
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
