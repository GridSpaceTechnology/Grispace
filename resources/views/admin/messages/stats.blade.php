@extends('layouts.admin')

@section('admin-content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Message Statistics</h1>
            <p class="text-sm text-slate-500 mt-1">Overview of platform messaging activity</p>
        </div>
        <a href="{{ route('admin.messages.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
            Back to Messages
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-slate-500 font-medium">Total Conversations</p>
                <div class="w-10 h-10 bg-[#052E5C]/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#052E5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalConversations) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $conversationsToday }} created today</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-slate-500 font-medium">Total Messages</p>
                <div class="w-10 h-10 bg-[#EB5233]/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#EB5233]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalMessages) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $messagesToday }} sent today</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-slate-500 font-medium">Avg Per Conversation</p>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $avgMessagesPerConversation }}</p>
            <p class="text-xs text-slate-400 mt-1">Messages per conversation</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-slate-500 font-medium">Messages Today</p>
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $messagesToday }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $conversationsToday }} conversations started</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-slate-500 font-medium">Active Conversations</p>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $totalConversations }}</p>
            <p class="text-xs text-slate-400 mt-1">Total on platform</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900">Most Active Conversations</h2>
            </div>
            <div class="p-4">
                @forelse($topConversations as $conv)
                    <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 bg-[#052E5C] rounded-full flex items-center justify-center text-white text-[10px] font-semibold ring-2 ring-white">
                                    {{ substr($conv->employer->name, 0, 1) }}
                                </div>
                                <div class="w-6 h-6 bg-[#EB5233] rounded-full flex items-center justify-center text-white text-[10px] font-semibold ring-2 ring-white">
                                    {{ substr($conv->candidate->name, 0, 1) }}
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $conv->employer->name }} ↔ {{ $conv->candidate->name }}</p>
                                @if($conv->job)
                                    <p class="text-xs text-slate-400">{{ $conv->job->title }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-[#EB5233]">{{ $conv->messages_count }} msgs</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-4">No conversations yet</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900">Recent Messages</h2>
            </div>
            <div class="p-4 max-h-[400px] overflow-y-auto">
                @forelse($latestMessages as $msg)
                    <div class="py-2 border-b border-slate-50 last:border-0">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-sm font-medium text-slate-800">{{ $msg->sender->name }}</span>
                            <span class="text-xs text-slate-400">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-slate-500 truncate">{{ $msg->message ?: '(Attachment)' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-4">No messages yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
