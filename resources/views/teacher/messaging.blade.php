@extends('layouts.teacher')
@section('title', 'Messaging')

@section('content')
<div class="flex gap-0 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
     style="height:calc(100vh - 172px); min-height:480px;">

    {{-- Left panel: conversation list --}}
    <div class="flex flex-col border-r border-gray-200 shrink-0" style="width:280px;">

        <div class="px-3 py-3 border-b border-gray-100 shrink-0">
            <div class="flex items-center justify-between mb-2 px-1">
                <h2 class="text-sm font-semibold text-gray-800">Messages</h2>
                <select onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.assign(u.toString());})(this.value)"
                        class="text-xs border border-gray-200 rounded bg-white text-gray-500 focus:outline-none py-0.5 px-1">
                    <option value="10" {{ $perPage === 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $perPage === 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
            <form method="GET" action="{{ route('teacher.messaging') }}" class="relative">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                @if(request('engagement_id'))
                    <input type="hidden" name="engagement_id" value="{{ request('engagement_id') }}">
                @endif
                <i data-lucide="search"
                   class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                <input type="text" name="search" id="conv-search" value="{{ request('search') }}"
                       placeholder="Search conversations..."
                       class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm
                              focus:ring-2 focus:ring-indigo-500 outline-none">
            </form>
            @if(request('search'))
                <a href="{{ route('teacher.messaging', array_filter(['per_page' => $perPage, 'engagement_id' => request('engagement_id')])) }}"
                   class="mt-1 block text-xs text-gray-400 hover:text-gray-600 text-right">Clear search</a>
            @endif
        </div>

        <div class="overflow-y-auto divide-y divide-gray-50" id="conv-list" style="flex:1;">
            @forelse($engagements as $eng)
                @php
                    $parentName = $eng->parentProfile?->name ?? 'Unknown Parent';
                    $initials   = strtoupper(substr($parentName, 0, 1));
                    $lastMsg    = $eng->latestMessage;
                    $unread     = $eng->unreadCount;
                    $isActive   = $activeEngagement?->id === $eng->id;
                @endphp
                <a href="{{ route('teacher.messaging') }}?engagement_id={{ $eng->id }}"
                   class="conv-item flex items-center gap-3 px-3 py-3.5 hover:bg-gray-50 transition-colors
                          {{ $isActive ? 'bg-indigo-50 border-r-2 border-indigo-600' : '' }}"
                   data-name="{{ strtolower($parentName) }}">

                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                        <span class="text-indigo-700 font-semibold text-sm">{{ $initials }}</span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $parentName }}</p>
                            @if($lastMsg)
                                <span class="text-[10px] text-gray-400 shrink-0">
                                    {{ \Carbon\Carbon::parse($lastMsg->sent_at)->format('h:i A') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 truncate mt-0.5">
                            {{ $lastMsg
                                ? \Illuminate\Support\Str::limit($lastMsg->message_body, 38)
                                : 'No messages yet' }}
                        </p>
                    </div>

                    @if($unread > 0)
                        <div class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shrink-0">
                            <span class="text-[10px] text-white font-bold leading-none">
                                {{ $unread > 9 ? '9+' : $unread }}
                            </span>
                        </div>
                    @endif
                </a>
            @empty
                <div class="px-4 py-10 text-center text-gray-400 text-sm">
                    <i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2 opacity-25"></i>
                    No conversations yet.
                </div>
            @endforelse
        </div>
        @if($engagements->hasPages())
            <div class="px-2 py-2 border-t border-gray-100 shrink-0 overflow-x-auto">
                {{ $engagements->links() }}
            </div>
        @endif
    </div>

    {{-- Right panel: chat area --}}
    <div class="flex flex-col flex-1 min-w-0">

        @if($activeEngagement)
            @php $parentName = $activeEngagement->parentProfile?->name ?? 'Unknown Parent'; @endphp

            {{-- Chat header --}}
            <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 shrink-0">
                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                    <span class="text-indigo-700 font-semibold text-sm">
                        {{ strtoupper(substr($parentName, 0, 1)) }}
                    </span>
                </div>
                <p class="font-semibold text-gray-900">{{ $parentName }}</p>
            </div>

            {{-- Messages --}}
            <div class="flex flex-col flex-1 overflow-y-auto px-5 py-4 space-y-3" id="chat-area">
                @forelse($messages as $msg)
                    @php $isTeacher = $msg->sender_role === 'Teacher'; @endphp
                    <div class="flex flex-col w-full {{ $isTeacher ? 'items-end' : 'items-start' }}"
                         data-message-id="{{ $msg->id }}">
                        <div class="max-w-xs lg:max-w-md">
                            <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed
                                        {{ $isTeacher ? 'text-white rounded-br-sm' : 'text-gray-800 rounded-bl-sm' }}"
                                 style="{{ $isTeacher ? 'background:#1e3a5f;' : 'background:#f1f3f4;' }}">
                                {{ $msg->message_body }}
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 {{ $isTeacher ? 'text-right' : '' }}">
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
            <div class="border-t border-gray-100 px-4 py-3 shrink-0">
                <form method="POST" action="{{ route('teacher.messaging.store') }}"
                      id="msg-form" class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="engagement_id" value="{{ $activeEngagement->id }}">
                    <input type="text" name="message_body" id="msg-input" maxlength="2000"
                           placeholder="Type a message..."
                           class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-sm
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                    <button type="submit" id="msg-send"
                            class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0
                                   transition-colors hover:opacity-80 opacity-50 cursor-not-allowed"
                            style="background:#1e3a5f;" disabled>
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

        @else
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 select-none">
                <i data-lucide="message-square" class="w-12 h-12 mb-3 opacity-20"></i>
                <p class="text-sm">Select a conversation to start messaging.</p>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatArea = document.getElementById('chat-area');
    if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;

    // --- Realtime messaging (AJAX send + polling) ---
    const ENGAGEMENT_ID = {{ $activeEngagement?->id ?? 0 }};
    const POLL_URL  = "{{ route('teacher.messaging.poll') }}";
    const STORE_URL = "{{ route('teacher.messaging.ajax.store') }}";
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
        const isTeacher = msg.sender_role === 'Teacher';
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col w-full ' + (isTeacher ? 'items-end' : 'items-start');
        wrapper.setAttribute('data-message-id', msgId);
        wrapper.innerHTML =
            '<div class="max-w-xs lg:max-w-md">' +
                '<div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed ' +
                     (isTeacher ? 'text-white rounded-br-sm' : 'text-gray-800 rounded-bl-sm') + '" ' +
                     'style="' + (isTeacher ? 'background:#1e3a5f;' : 'background:#f1f3f4;') + '">' +
                    escapeHtml(msg.message_body) +
                '</div>' +
                '<p class="text-[10px] text-gray-400 mt-1 ' + (isTeacher ? 'text-right' : '') + '">' +
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
                lastMessageId = Math.max(lastMessageId, data.message.id);
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
            lastMessageId = Math.max(lastMessageId, ...messages.map(function (m) { return m.id; }));
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
