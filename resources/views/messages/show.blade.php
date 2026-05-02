@extends('messages.index')

@section('chat-content')
@if($conversation)
    <div class="flex-1 flex flex-col">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <div class="flex items-center gap-3">
                <!-- Mobile Back Button -->
                @php
                    $indexRoute = auth()->user()->isEmployer() 
                        ? route('employer.messages') 
                        : route('messages.index');
                @endphp
                <a href="{{ $indexRoute }}" class="md:hidden -ml-2 p-2 hover:bg-slate-200 rounded-lg">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-medium text-sm">
                        {{ substr($conversation->otherParticipant(auth()->user())->name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">{{ $conversation->otherParticipant(auth()->user())->name }}</h2>
                    @if($conversation->job)
                        <p class="text-sm text-indigo-600">{{ $conversation->job->title }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chat-messages">
            @forelse($conversation->messages as $message)
                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] {{ $message->sender_id === auth()->id() 
                        ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
                        : 'bg-slate-100 text-slate-900' }} rounded-2xl px-4 py-3">
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

        <div class="p-4 border-t border-slate-200 bg-white">
            <form id="message-form" class="flex gap-2">
                @csrf
                <input type="text" name="message" id="message-input" 
                    class="flex-1 px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Type a message...">
                <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
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
                            <div class="max-w-[80%] ${isMine ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'bg-slate-100 text-slate-900'} rounded-2xl px-4 py-3">
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

            const sendUrl = '{{ auth()->user()->isEmployer() ? "/employer/messages" : "/messages" }}/' + conversationId + '/send';
            try {
                const response = await fetch(sendUrl, {
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
        <div class="text-center p-8">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <p class="text-slate-500 font-medium">Select a conversation</p>
            <p class="text-slate-400 text-sm mt-1">Choose from your existing conversations</p>
        </div>
    </div>
@endif
@endsection