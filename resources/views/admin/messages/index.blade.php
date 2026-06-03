@extends('layouts.admin')

@section('admin-content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Messages</h1>
            <p class="text-sm text-slate-500 mt-1">Monitor all conversations on the platform</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.messages.stats') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
                View Statistics
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" class="flex gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, job, or keyword..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]">
                </div>
                <button type="submit" class="px-4 py-2.5 bg-[#EB5233] text-white rounded-lg hover:bg-[#d94728] transition-colors text-sm font-medium">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.messages.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Participants</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Job</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Last Message</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Messages</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Created</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($conversations as $conversation)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 bg-[#052E5C] rounded-full flex items-center justify-center text-white text-xs font-semibold ring-2 ring-white">
                                            {{ substr($conversation->employer->name, 0, 1) }}
                                        </div>
                                        <div class="w-8 h-8 bg-[#EB5233] rounded-full flex items-center justify-center text-white text-xs font-semibold ring-2 ring-white">
                                            {{ substr($conversation->candidate->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">
                                            {{ $conversation->employer->name }}
                                            <span class="text-slate-400 mx-1">→</span>
                                            {{ $conversation->candidate->name }}
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            Employer · Candidate
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if($conversation->job)
                                    <span class="text-sm text-slate-700">{{ $conversation->job->title }}</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">No job</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($conversation->latestMessage)
                                    <p class="text-sm text-slate-600 truncate max-w-[200px]">{{ $conversation->latestMessage->message ?: '(Attachment)' }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $conversation->latestMessage->created_at->diffForHumans() }}</p>
                                @else
                                    <span class="text-xs text-slate-400 italic">No messages</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-sm text-slate-700">{{ $conversation->messages_count ?? $conversation->messages()->count() }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-sm text-slate-500">{{ $conversation->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.messages.show', $conversation) }}" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-xs font-medium">
                                        View
                                    </a>
                                    <form method="POST" action="{{ route('admin.messages.destroy', $conversation) }}" onsubmit="return confirm('Delete this conversation? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 border border-red-200 text-red-600 rounded-lg hover:bg-red-100 transition-colors text-xs font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <p class="text-slate-500 font-medium">No conversations found</p>
                                <p class="text-slate-400 text-sm mt-1">Try adjusting your search criteria</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conversations->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
