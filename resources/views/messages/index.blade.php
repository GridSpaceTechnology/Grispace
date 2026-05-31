@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-64px)] bg-slate-50">
    <div class="h-full max-w-7xl mx-auto px-0 sm:px-4 py-0 sm:py-4">
        <div class="bg-white sm:rounded-xl shadow-sm border border-slate-200 h-full flex overflow-hidden">
            <!-- Conversations List -->
            <div class="w-full md:w-80 {{ $selectedConversation ? 'hidden md:flex' : 'flex' }} flex-col border-r border-slate-200">
                <div class="p-4 border-b border-slate-200 bg-slate-50">
                    <div class="flex items-center justify-between">
                        <h1 class="text-lg font-bold text-slate-900">Messages</h1>
                        @if($conversations->sum(fn($c) => $c->unreadMessagesCount(auth()->user())) > 0)
                            <span class="px-2 py-1 bg-indigo-600 text-white text-xs rounded-full">
                                {{ $conversations->sum(fn($c) => $c->unreadMessagesCount(auth()->user())) }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    @forelse($conversations as $conversation)
                        @php
                            $otherUser = auth()->user()->isEmployer() ? $conversation->candidate : $conversation->employer;
                            $unreadCount = $conversation->unreadMessagesCount(auth()->user());
                            $lastMessage = $conversation->latestMessage;
                            $msgRoute = auth()->user()->isEmployer() 
                                ? route('employer.messages.show', ['conversation' => $conversation->id])
                                : route('messages.show', ['conversation' => $conversation->id]);
                        @endphp
                        <a href="{{ $msgRoute }}" 
                           class="block p-4 border-b border-slate-100 hover:bg-slate-50 transition-all {{ request()->routeIs('messages.show') && request()->route('conversation')?->id == $conversation->id ? 'bg-indigo-50 border-l-4 border-l-indigo-600' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-semibold text-sm">{{ substr($otherUser->name, 0, 1) }}</span>
                                    </div>
                                    @if($unreadCount > 0)
                                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $unreadCount }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-semibold text-slate-900 truncate {{ $unreadCount > 0 ? 'text-indigo-900' : '' }}">{{ $otherUser->name }}</span>
                                        @if($lastMessage)
                                            <span class="text-xs text-slate-400 flex-shrink-0 ml-2">{{ $lastMessage->created_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    @if($lastMessage)
                                        <p class="text-sm text-slate-500 truncate {{ $unreadCount > 0 ? 'font-medium text-slate-700' : '' }}">
                                            {{ $lastMessage->sender_id === auth()->id() ? 'You: ' : '' }}{{ $lastMessage->message }}
                                        </p>
                                    @else
                                        <p class="text-sm text-slate-400 italic">No messages yet</p>
                                    @endif
                                    @if($conversation->job)
                                        <span class="text-xs text-indigo-600 mt-1 inline-block">{{ $conversation->job->title }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-slate-500 font-medium">No conversations yet</p>
                            <p class="text-slate-400 text-sm mt-1">Start by applying to jobs or shortlisting candidates</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex-col {{ $selectedConversation ? 'flex' : 'hidden' }} md:flex">
                @yield('chat-content')
            </div>
        </div>
    </div>
</div>
@endsection