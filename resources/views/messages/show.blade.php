@extends('messages.index')

@section('chat-content')
@if($conversation)
    <div class="flex-1 flex flex-col">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-brand-primary/10 rounded-full flex items-center justify-center">
                    <span class="text-brand-primary font-medium">
                        {{ substr($conversation->otherParticipant(auth()->user())->name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">{{ $conversation->otherParticipant(auth()->user())->name }}</h2>
                    @if($conversation->job)
                        <p class="text-sm text-slate-500">Re: {{ $conversation->job->title }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
            @forelse($conversation->messages as $message)
                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] {{ $message->sender_id === auth()->id() 
                        ? 'bg-brand-primary text-white' 
                        : 'bg-slate-100 text-slate-900' }} rounded-2xl px-4 py-2">
                        <p class="text-sm">{{ $message->message }}</p>
                        <p class="text-xs mt-1 {{ $message->sender_id === auth()->id() ? 'text-indigo-200' : 'text-slate-400' }}">
                            {{ $message->created_at->format('g:i A') }}
                            @if($message->sender_id === auth()->id() && $message->is_read)
                                <span class="ml-1">✓✓</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-slate-400">No messages yet. Start the conversation!</p>
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-200">
            <form id="message-form" class="flex gap-3">
                @csrf
                <input type="text" name="message" id="message-input" 
                    class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                    placeholder="Type a message...">
                <button type="submit" 
                    class="px-6 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Send
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const conversationId = {{ $conversation->id }};
        const messagesContainer = document.getElementById('chat-messages');
        const messageInput = document.getElementById('message-input');
        const messageForm = document.getElementById('message-form');

        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        if (window.Echo) {
            window.Echo.private('conversation.' + conversationId)
                .listen('MessageSent', (e) => {
                    const isMine = e.sender_id === {{ auth()->id() }};
                    const messageHtml = `
                        <div class="flex ${isMine ? 'justify-end' : 'justify-start'}">
                            <div class="max-w-[70%] ${isMine ? 'bg-brand-primary text-white' : 'bg-slate-100 text-slate-900'} rounded-2xl px-4 py-2">
                                <p class="text-sm">${e.message}</p>
                                <p class="text-xs mt-1 ${isMine ? 'text-indigo-200' : 'text-slate-400'}">
                                    ${new Date(e.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </p>
                            </div>
                        </div>
                    `;
                    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
        }

        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            try {
                const response = await fetch('/messages/' + conversationId + '/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message })
                });

                if (response.ok) {
                    messageInput.value = '';
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        });
    </script>
    @endpush
@else
    <div class="flex-1 flex items-center justify-center">
        <div class="text-center">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-slate-500">Select a conversation to start messaging</p>
        </div>
    </div>
@endif
@endsection