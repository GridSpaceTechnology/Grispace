@extends('layouts.admin')

@section('admin-content')
<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.messages.index') }}" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Conversation</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $conversation->employer->name }}
                <span class="text-slate-300 mx-1">↔</span>
                {{ $conversation->candidate->name }}
                @if($conversation->job)
                    <span class="text-slate-300 mx-1">·</span>
                    {{ $conversation->job->title }}
                @endif
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 text-sm text-slate-600">
                    <span><strong class="text-slate-700">Created:</strong> {{ $conversation->created_at->format('M d, Y g:i A') }}</span>
                    <span><strong class="text-slate-700">Messages:</strong> {{ $messages->total() }}</span>
                    <span><strong class="text-slate-700">Last activity:</strong> {{ $conversation->last_message_at?->diffForHumans() ?? 'N/A' }}</span>
                </div>
                <form method="POST" action="{{ route('admin.messages.destroy', $conversation) }}" onsubmit="return confirm('Delete this entire conversation? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 bg-red-50 border border-red-200 text-red-600 rounded-lg hover:bg-red-100 transition-colors text-xs font-medium">
                        Delete Conversation
                    </button>
                </form>
            </div>
        </div>

        <div class="p-4 max-h-[600px] overflow-y-auto space-y-3" style="scrollbar-width: thin;">
            @forelse($messages as $message)
                <div class="flex {{ $message->sender_id === $conversation->employer_id ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-[70%] {{ $message->sender_id === $conversation->employer_id ? 'bg-slate-100' : 'bg-[#EB5233]/5 border border-[#EB5233]/20' }} rounded-2xl px-4 py-3">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-semibold {{ $message->sender_id === $conversation->employer_id ? 'text-[#052E5C]' : 'text-[#EB5233]' }}">
                                {{ $message->sender->name }}
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ $message->created_at->format('g:i A · M d') }}
                            </span>
                            @if($message->is_read)
                                <span class="text-[10px] text-green-600">Read</span>
                            @endif
                        </div>
                        @if($message->message)
                            <p class="text-sm text-slate-800 whitespace-pre-wrap">{{ $message->message }}</p>
                        @endif
                        @if($message->attachment_path)
                            <div class="mt-2 flex items-center gap-2 bg-white rounded-lg px-3 py-2 border border-slate-200">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs text-slate-600 truncate flex-1">{{ $message->attachment_name }}</span>
                                <a href="{{ $message->attachment_url }}" target="_blank" class="text-xs text-[#EB5233] hover:text-[#d94728] font-medium">Download</a>
                            </div>
                        @endif
                        @if($message->reads->isNotEmpty())
                            <div class="mt-1 space-y-0.5">
                                @foreach($message->reads as $read)
                                    <p class="text-[10px] text-slate-400">
                                        Read by {{ $read->user->name }} {{ $read->read_at->diffForHumans() }}
                                    </p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <p class="text-slate-400">No messages in this conversation</p>
                </div>
            @endforelse
        </div>

        @if($messages->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
