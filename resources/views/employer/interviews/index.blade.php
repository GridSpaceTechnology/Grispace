@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Interviews</h1>
                <p class="text-gray-600 mt-1">Manage your scheduled interviews</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('employer.interviews.create') }}" class="inline-block px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Schedule Interview
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($interviews->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No scheduled interviews</h3>
                <p class="text-gray-500 mb-4">Schedule interviews with candidates</p>
                <a href="{{ route('employer.marketplace.index') }}" class="inline-block px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Browse Candidates
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($interviews as $interview)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                    <span class="text-brand-primary font-semibold">{{ substr($interview->candidate->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $interview->candidate->name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $interview->candidate->candidateProfile?->desired_role ?? 'Not specified' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($interview->status === 'scheduled')
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                        Scheduled
                                    </span>
                                @elseif($interview->status === 'completed')
                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                        Completed
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        Cancelled
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-600">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $interview->scheduled_at->format('M j, Y \a\t g:i A') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                {{ ucfirst($interview->interview_type) }}
                            </span>
                            @if($interview->job)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                    </svg>
                                    {{ $interview->job->title }}
                                </span>
                            @endif
                        </div>

                        @if($interview->status === 'scheduled')
                            <div class="flex gap-2 mt-4 pt-4 border-t border-slate-100">
                                <a href="{{ route('employer.marketplace.candidate', ['candidate' => $interview->candidate->id]) }}" 
                                   class="text-sm text-brand-primary hover:text-brand-primary-hover">
                                    View Candidate
                                </a>
                                <span class="text-gray-300">|</span>
                                <form method="POST" action="{{ route('employer.interviews.complete', ['interview' => $interview->id]) }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-green-600 hover:text-green-700">
                                        Mark Complete
                                    </button>
                                </form>
                                <span class="text-gray-300">|</span>
                                <form method="POST" action="{{ route('employer.interviews.cancel', ['interview' => $interview->id]) }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-700">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $interviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
