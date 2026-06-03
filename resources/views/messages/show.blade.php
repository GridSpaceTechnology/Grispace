@extends('messages.index')

@section('chat-content')
    @if($conversation)
        @include('messages._chat', ['conversation' => $conversation])
    @else
        <div class="flex-1 flex items-center justify-center bg-slate-50/50">
            <div class="text-center p-8 max-w-sm">
                <div class="w-24 h-24 bg-gradient-to-br from-[#EB5233]/10 to-[#052E5C]/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-[#EB5233]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Your Messages</h2>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Select a conversation from the sidebar to start chatting.
                </p>
            </div>
        </div>
    @endif
@endsection
