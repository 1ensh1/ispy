@extends('layouts.parent')
@section('title', 'Messaging')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Teacher Chat</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            @if($teacher && $student)
                Message {{ $student->name }}'s teacher, {{ $teacher->name }}
            @else
                No teacher assigned yet.
            @endif
        </p>
    </div>

    @if($teacher && $engagement)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col" style="height:calc(100vh - 260px); min-height:400px;">

        {{-- Teacher info header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                <span class="text-blue-700 text-sm font-bold">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">{{ $teacher->name }}</p>
                <p class="text-xs text-gray-400">Teacher · {{ $student?->classList?->class_name ?? 'Class' }}</p>
            </div>
        </div>

        {{-- Chat messages --}}
        <div class="flex flex-col flex-1 overflow-y-auto px-5 py-4 space-y-3" id="chat-area">
            @forelse($messages as $msg)
                @php $isParent = $msg->sender_role === 'Parent'; @endphp
                <div class="flex flex-col w-full {{ $isParent ? 'items-end' : 'items-start' }}">
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
        <div class="border-t border-gray-100 px-4 py-3">
            <form method="POST" action="{{ route('parent.messaging.store') }}" class="flex items-center gap-3">
                @csrf
                <input type="text" name="message_body" required maxlength="2000"
                       placeholder="Type a message..."
                       class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                <button type="submit"
                        class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0 transition-colors hover:opacity-80"
                        style="background:#1e3a5f;">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-gray-400">
            <i data-lucide="message-square" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
            <p>No teacher assigned to your child's class yet.</p>
        </div>
    @endif

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
