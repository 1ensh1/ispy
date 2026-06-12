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
                    <div class="flex flex-col w-full {{ $isTeacher ? 'items-end' : 'items-start' }}">
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
                      class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="engagement_id" value="{{ $activeEngagement->id }}">
                    <button type="button"
                            class="text-gray-400 hover:text-gray-600 transition-colors shrink-0"
                            tabindex="-1">
                        <i data-lucide="paperclip" class="w-5 h-5"></i>
                    </button>
                    <input type="text" name="message_body" required maxlength="2000"
                           placeholder="Type a message..."
                           class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-sm
                                  focus:ring-2 focus:ring-indigo-500 outline-none">
                    <button type="submit"
                            class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0
                                   transition-colors hover:opacity-80"
                            style="background:#1e3a5f;">
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

});
</script>
@endpush
