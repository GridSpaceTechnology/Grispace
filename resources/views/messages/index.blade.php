@extends('layouts.app')

@section('content')
<div x-data="messenger()" class="h-[calc(100vh-64px)] bg-slate-50" x-init="init()">
    <div class="h-full max-w-7xl mx-auto px-0 sm:px-4 py-0 sm:py-4">
        <div class="bg-white sm:rounded-xl shadow-sm border border-slate-200 h-full flex overflow-hidden">
            <div
                class="w-full md:w-80 lg:w-96 {{ $selectedConversation ? 'hidden md:flex' : 'flex' }} flex-col border-r border-slate-200 bg-white"
                :class="{ 'hidden md:flex': selectedConversationId, 'flex': !selectedConversationId }"
            >
                <div class="p-4 border-b border-slate-200 bg-white">
                    <div class="flex items-center justify-between mb-3">
                        <h1 class="text-lg font-bold text-slate-900">Messages</h1>
                        <div class="flex items-center gap-2">
                            <button @click="showSearch = !showSearch; if(showSearch) setTimeout(() => $refs.searchInput.focus(), 100)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div x-show="showSearch" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                x-ref="searchInput"
                                type="text"
                                placeholder="Search conversations..."
                                x-model="searchQuery"
                                @input.debounce.300ms="searchConversations()"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-100 border-0 rounded-xl text-sm focus:ring-2 focus:ring-[#EB5233] focus:bg-white placeholder-slate-400 transition-all"
                            >
                            <button x-show="searchQuery" @click="searchQuery = ''; searchConversations()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto" x-ref="conversationsList">
                    <template x-if="loading">
                        <div class="p-4 space-y-4">
                            <template x-for="i in 5" :key="i">
                                <div class="flex items-center gap-3 animate-pulse">
                                    <div class="w-12 h-12 bg-slate-200 rounded-full flex-shrink-0"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-3 bg-slate-200 rounded w-3/4"></div>
                                        <div class="h-2.5 bg-slate-100 rounded w-1/2"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!loading && conversations.length === 0">
                        <div class="p-8 text-center">
                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-slate-200">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-slate-700 font-semibold" x-text="searchQuery ? 'No results found' : 'No conversations yet'"></p>
                            <p class="text-slate-400 text-sm mt-1" x-text="searchQuery ? 'Try a different search term' : 'Start by applying to jobs or shortlisting candidates'"></p>
                            <template x-if="!searchQuery">
                                <div class="mt-6 flex flex-col items-center gap-3">
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 bg-[#EB5233]/10 rounded-full flex items-center justify-center ring-2 ring-white">
                                            <svg class="w-4 h-4 text-[#EB5233]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                        <div class="w-8 h-8 bg-[#052E5C]/10 rounded-full flex items-center justify-center ring-2 ring-white">
                                            <svg class="w-4 h-4 text-[#052E5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <a href="{{ auth()->user()->isEmployer() ? route('employer.marketplace.index') : route('candidate.jobs') }}" class="text-sm text-[#EB5233] hover:text-[#d94728] font-medium transition-colors">
                                        {{ auth()->user()->isEmployer() ? 'Browse talent marketplace' : 'Browse open positions' }}
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-for="conversation in conversations" :key="conversation.id">
                        <a
                            :href="conversation.url"
                            @click.prevent="selectConversation(conversation)"
                            class="block p-4 border-b border-slate-50 hover:bg-slate-50 transition-all group"
                            :class="{ 'bg-[#EB5233]/5 border-l-2 border-l-[#EB5233] hover:bg-[#EB5233]/5': selectedConversationId === conversation.id }"
                        >
                            <div class="flex items-center gap-3">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-sm"
                                        :class="conversation.other_user_role === 'employer' ? 'bg-[#052E5C]' : 'bg-[#EB5233]'"
                                        x-text="conversation.other_user_name.charAt(0).toUpperCase()"
                                    ></div>
                                    <div
                                        x-show="conversation.is_online"
                                        class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"
                                    ></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span
                                            class="text-sm font-semibold truncate"
                                            :class="conversation.unread_count > 0 ? 'text-slate-900' : 'text-slate-700'"
                                            x-text="conversation.other_user_name"
                                        ></span>
                                        <span class="text-[11px] text-slate-400 flex-shrink-0 ml-2" x-text="conversation.last_message_time"></span>
                                    </div>
                                    <p
                                        class="text-sm truncate"
                                        :class="conversation.unread_count > 0 ? 'text-slate-700 font-medium' : 'text-slate-500'"
                                        x-text="conversation.last_message_preview"
                                    ></p>
                                    <div class="flex items-center justify-between mt-0.5" x-show="conversation.job_title">
                                        <span class="text-[11px] text-[#052E5C] font-medium truncate" x-text="conversation.job_title"></span>
                                    </div>
                                </div>
                                <template x-if="conversation.unread_count > 0">
                                    <div class="flex-shrink-0">
                                        <span
                                            class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 bg-[#EB5233] text-white text-[11px] font-bold rounded-full"
                                            x-text="conversation.unread_count > 99 ? '99+' : conversation.unread_count"
                                        ></span>
                                    </div>
                                </template>
                            </div>
                        </a>
                    </template>
                </div>
            </div>

            <div
                class="flex-1 flex flex-col bg-white"
                :class="{ 'hidden md:flex': !selectedConversationId, 'flex': selectedConversationId }"
            >
                @if($selectedConversation)
                    @include('messages._chat', ['conversation' => $selectedConversation])
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
                                Select a conversation from the sidebar to start chatting. All your hiring communication in one place.
                            </p>
                            <div class="mt-6 flex items-center justify-center gap-6 text-xs text-slate-400">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <circle cx="10" cy="10" r="5"/>
                                    </svg>
                                    Real-time
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#EB5233]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Secure
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#052E5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    Attachments
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $conversationsJson = $conversations->map(function ($c) {
        $isEmployer = auth()->user()->isEmployer();
        $other = auth()->user()->id === $c->employer_id ? $c->candidate : $c->employer;
        $lastMsg = $c->latestMessage;
        return [
            'id' => $c->id,
            'other_user_name' => $other->name,
            'other_user_role' => $other->role,
            'other_user_id' => $other->id,
            'job_title' => $c->job?->title,
            'unread_count' => $c->unreadMessagesCount(auth()->user()),
            'last_message_preview' => $lastMsg
                ? ($lastMsg->sender_id === auth()->id() ? 'You: ' : '') . \Illuminate\Support\Str::limit($lastMsg->message, 60)
                : 'No messages yet',
            'last_message_time' => $lastMsg ? $lastMsg->created_at->diffForHumans() : '',
            'is_online' => $other->isOnline(),
            'url' => $isEmployer
                ? route('employer.messages.show', $c->id)
                : route('messages.show', $c->id),
        ];
    });
@endphp

@push('scripts')
<script>
    function messenger() {
        return {
            conversations: @json($conversationsJson),
            selectedConversationId: {{ $selectedConversation?->id ?? 'null' }},
            showSearch: false,
            searchQuery: '',
            loading: false,
            init() {
                if (this.selectedConversationId) {
                    this.initEcho(this.selectedConversationId);
                }
            },
            selectConversation(conversation) {
                this.selectedConversationId = conversation.id;
                const url = '{{ auth()->user()->isEmployer() ? "/employer/messages/" : "/messages/" }}' + conversation.id;
                window.location.href = url;
            },
            searchConversations() {
                if (!this.searchQuery.trim()) {
                    return;
                }
                this.loading = true;
                fetch('{{ auth()->user()->isEmployer() ? "/employer/messages" : "/messages" }}/search?q=' + encodeURIComponent(this.searchQuery))
                    .then(r => r.json())
                    .then(data => {
                        this.conversations = data;
                        this.loading = false;
                    })
                    .catch(() => this.loading = false);
            },
            initEcho(conversationId) {
                if (window.Echo) {
                    window.Echo.private('conversation.' + conversationId)
                        .listen('MessageSent', (e) => {
                            const container = document.getElementById('chat-messages');
                            if (!container) return;
                            const isMine = e.sender_id === {{ auth()->id() }};
                            const messageHtml = this.buildMessageHtml(e, isMine);
                            container.insertAdjacentHTML('beforeend', messageHtml);
                            container.scrollTop = container.scrollHeight;
                        })
                        .listen('MessageRead', (e) => {
                            const readIndicator = document.querySelector(`[data-message-id="${e.message_id}"] .read-status`);
                            if (readIndicator) {
                                readIndicator.innerHTML = '<span class="text-[10px] text-[#052E5C]/60">Read</span>';
                            }
                        })
                        .listen('TypingIndicator', (e) => {
                            const typingEl = document.getElementById('typing-indicator');
                            if (typingEl) {
                                typingEl.innerHTML = e.typing ? `<span class="text-xs text-slate-400 italic">${e.user_name} is typing...</span>` : '';
                            }
                        });
                }
            },
            buildMessageHtml(e, isMine) {
                const time = new Date(e.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                return `
                    <div class="flex ${isMine ? 'justify-end' : 'justify-start'} animate-fade-in">
                        <div class="max-w-[75%] ${isMine ? 'bg-[#EB5233] text-white' : 'bg-slate-100 text-slate-900'} rounded-2xl px-4 py-2.5 shadow-sm">
                            <p class="text-sm leading-relaxed">${e.message}</p>
                            <div class="flex items-center justify-end gap-1 mt-1">
                                <span class="text-[10px] ${isMine ? 'text-white/70' : 'text-slate-400'}">${time}</span>
                                ${isMine ? '<span class="text-[10px] text-white/70">✓✓</span>' : ''}
                            </div>
                        </div>
                    </div>
                `;
            }
        };
    }
</script>
@endpush

<style>
    .animate-fade-in {
        animation: fadeIn 0.2s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
