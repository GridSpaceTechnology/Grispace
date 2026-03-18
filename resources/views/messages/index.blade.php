@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-64px)]">
    <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8 py-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 h-full flex overflow-hidden">
            <div class="w-80 border-r border-slate-200 flex flex-col">
                <div class="p-4 border-b border-slate-200">
                    <h1 class="text-xl font-bold text-slate-900">Messages</h1>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    @forelse($conversations as $conversation)
                        @php
                            $otherUser = auth()->user()->isEmployer() ? $conversation->candidate : $conversation->employer;
                            $unreadCount = $conversation->unreadMessagesCount(auth()->user());
                            $lastMessage = $conversation->latestMessage;
                        @endphp
                        <a href="{{ route('messages.show', ['conversation' => $conversation->id]) }}" 
                           class="block p-4 border-b border-slate-100 hover:bg-slate-50 transition-colors {{ request()->routeIs('messages.show') && request()->route('conversation') == $conversation->id ? 'bg-indigo-50' : '' }}">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-brand-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-brand-primary font-medium">{{ substr($otherUser->name, 0, 1) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-slate-900 truncate">{{ $otherUser->name }}</span>
                                        @if($lastMessage)
                                            <span class="text-xs text-slate-400">{{ $lastMessage->created_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    @if($lastMessage)
                                        <p class="text-sm text-slate-500 truncate">
                                            {{ $lastMessage->sender_id === auth()->id() ? 'You: ' : '' }}{{ $lastMessage->message }}
                                        </p>
                                    @endif
                                </div>
                                @if($unreadCount > 0)
                                    <span class="px-2 py-0.5 bg-brand-primary text-white text-xs rounded-full">{{ $unreadCount }}</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-slate-500 text-sm">No conversations yet</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                @yield('chat-content')
            </div>
        </div>
    </div>
</div>
@endsection