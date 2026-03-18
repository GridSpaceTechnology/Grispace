@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('employer.jobs.show', ['job' => $job->id]) }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Job
            </a>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Hiring Pipeline</h1>
                <p class="text-gray-600 mt-1">{{ $job->title }}</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="{{ route('employer.jobs.candidates', ['job' => $job->id]) }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    View Matches
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex gap-4 overflow-x-auto pb-4">
            @php
                $stageConfig = [
                    'applied' => ['title' => 'Applied', 'color' => 'blue', 'count' => $stages['applied']->count()],
                    'shortlisted' => ['title' => 'Shortlisted', 'color' => 'purple', 'count' => $stages['shortlisted']->count()],
                    'interview' => ['title' => 'Interview', 'color' => 'yellow', 'count' => $stages['interview']->count()],
                    'offer' => ['title' => 'Offer', 'color' => 'orange', 'count' => $stages['offer']->count()],
                    'hired' => ['title' => 'Hired', 'color' => 'green', 'count' => $stages['hired']->count()],
                    'rejected' => ['title' => 'Rejected', 'color' => 'red', 'count' => $stages['rejected']->count()],
                ];
            @endphp

            @foreach($stageConfig as $status => $config)
                <div class="flex-shrink-0 w-72">
                    <div class="bg-slate-100 rounded-t-lg px-4 py-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700">{{ $config['title'] }}</h3>
                        <span class="px-2 py-1 bg-{{ $config['color'] }}-200 text-{{ $config['color'] }}-700 text-xs font-medium rounded-full">
                            {{ $config['count'] }}
                        </span>
                    </div>
                    <div class="bg-slate-50 rounded-b-lg p-3 min-h-[500px] max-h-[600px] overflow-y-auto">
                        @forelse($stages[$status] as $application)
                            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 mb-3">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                            <span class="text-brand-primary font-medium text-sm">{{ substr($application->candidate->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 text-sm">{{ $application->candidate->name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $application->candidate->candidateProfile?->desired_role ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                                    @if($application->candidate->candidateProfile?->location)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ $application->candidate->candidateProfile->location }}
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $application->candidate->candidateProfile?->years_of_experience ?? 0 }} yrs
                                    </span>
                                </div>

                                @if($application->match_score)
                                    <div class="mb-3">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-gray-500">Match Score</span>
                                            <span class="font-medium @if($application->match_score >= 80) text-green-600 @elseif($application->match_score >= 60) text-yellow-600 @else text-red-600 @endif">
                                                {{ $application->match_score }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full @if($application->match_score >= 80) bg-green-500 @elseif($application->match_score >= 60) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ $application->match_score }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                <div class="text-xs text-gray-400 mb-3">
                                    Applied {{ $application->applied_at?->diffForHumans() ?? $application->created_at->diffForHumans() }}
                                </div>

                                <div class="flex gap-2">
                                    @if($status !== 'rejected' && $status !== 'hired')
                                        <form method="POST" action="{{ route('employer.applications.move', ['application' => $application->id]) }}" class="flex-1">
                                            @csrf
                                            <input type="hidden" name="action" value="next">
                                            <button type="submit" class="w-full px-2 py-1.5 bg-brand-primary text-white text-xs rounded hover:bg-brand-primary-hover transition-colors">
                                                Move →
                                            </button>
                                        </form>
                                    @endif

                                    @if($status !== 'applied')
                                        <form method="POST" action="{{ route('employer.applications.move', ['application' => $application->id]) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="previous">
                                            <button type="submit" class="px-2 py-1.5 border border-slate-300 text-slate-700 text-xs rounded hover:bg-slate-50 transition-colors">
                                                ←
                                            </button>
                                        </form>
                                    @endif

                                    @if($status !== 'rejected' && $status !== 'hired')
                                        <form method="POST" action="{{ route('employer.applications.move', ['application' => $application->id]) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="px-2 py-1.5 border border-red-300 text-red-700 text-xs rounded hover:bg-red-50 transition-colors" onclick="return confirm('Are you sure you want to reject this candidate?')">
                                                ✕
                                            </button>
                                        </form>
                                    @endif

                                    @if($status === 'interview')
                                        <a href="{{ route('employer.applications.schedule-interview', ['application' => $application->id]) }}" 
                                            class="mt-2 block w-full px-2 py-1.5 bg-emerald-100 text-emerald-700 text-xs font-medium rounded hover:bg-emerald-200 transition-colors text-center">
                                            Schedule
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400 text-sm">
                                No candidates
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
