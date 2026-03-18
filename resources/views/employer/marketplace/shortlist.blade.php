@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Shortlist</h1>
            <p class="text-gray-600 mt-1">Candidates you've shortlisted for your positions</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($shortlists->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No shortlisted candidates</h3>
                <p class="text-gray-500 mb-4">Start discovering candidates in the marketplace</p>
                <a href="{{ route('employer.marketplace.index') }}" class="inline-block px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Browse Candidates
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($shortlists as $shortlist)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                    <span class="text-brand-primary font-semibold">{{ substr($shortlist->candidate->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $shortlist->candidate->name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $shortlist->candidate->candidateProfile?->desired_role ?? 'Not specified' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($shortlist->job)
                                    <span class="text-sm text-gray-500">
                                        For: {{ $shortlist->job->title }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">
                                    Shortlisted {{ $shortlist->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <a href="{{ route('employer.marketplace.candidate', ['candidate' => $shortlist->candidate->id]) }}" 
                               class="text-sm text-brand-primary hover:text-brand-primary-hover">
                                View Profile
                            </a>
                            <span class="text-gray-300">|</span>
                            <a href="{{ route('employer.interviews.create', ['candidate_id' => $shortlist->candidate->id, 'job_id' => $shortlist->job_id]) }}" 
                               class="text-sm text-brand-primary hover:text-brand-primary-hover">
                                Schedule Interview
                            </a>
                            <span class="text-gray-300">|</span>
                            <form method="POST" action="{{ route('employer.marketplace.shortlist', ['candidate' => $shortlist->candidate->id]) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-red-600 hover:text-red-700">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $shortlists->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
