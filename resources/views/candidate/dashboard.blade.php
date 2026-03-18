@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(auth()->user()->onboarding_completed)
            <x-welcome-banner type="candidate" />
        @endif

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Candidate Dashboard</h1>
            <p class="text-slate-600 mt-1">Track your applications and find new opportunities</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-brand-primary/10 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Profile Completion</p>
                            <p class="text-2xl font-bold text-slate-900">{{ $profile?->profile_completion_percentage ?? 0 }}%</p>
                        </div>
                    </div>
                </div>
                <x-progress-bar :value="$profile?->profile_completion_percentage ?? 0" size="sm" />
                @if(($profile?->profile_completion_percentage ?? 0) < 100)
                    <a href="{{ route('candidate.onboarding.step', ['step' => 1]) }}" class="mt-3 block text-sm text-brand-primary hover:text-brand-primary-hover">
                        Complete your profile &rarr;
                    </a>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Applications</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $applications->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Matching Jobs</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $matchingJobs->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-slate-900">Top Matching Jobs</h2>
                    <a href="{{ route('candidate.jobs') }}" class="text-sm text-brand-primary hover:text-brand-primary-hover">View all</a>
                </div>
                <div class="p-6">
                    @if($matchingJobs->isEmpty())
                        <p class="text-gray-500 text-center py-4">No matching jobs found</p>
                    @else
                        <div class="space-y-4">
                            @foreach($matchingJobs as $match)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $match['job']->title }}</h3>
                                            <p class="text-sm text-gray-500">{{ $match['job']->company ?? $match['job']->employer->name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-indigo-600">{{ $match['match_percentage'] }}%</div>
                                            <div class="text-xs text-gray-500">match</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500">
                                        <span class="bg-gray-100 px-2 py-1 rounded">{{ $match['job']->role }}</span>
                                        <span class="bg-gray-100 px-2 py-1 rounded">{{ $match['job']->employment_type }}</span>
                                        <span class="bg-gray-100 px-2 py-1 rounded">{{ $match['job']->work_preference }}</span>
                                    </div>
                                    <div class="mt-3">
                                        <form method="POST" action="{{ route('candidate.jobs.apply', ['job' => $match['job']->id]) }}">
                                            @csrf
                                            <button type="submit" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                                Apply Now
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Your Applications</h2>
                </div>
                <div class="p-6">
                    @if($applications->isEmpty())
                        <p class="text-gray-500 text-center py-4">No applications yet</p>
                    @else
                        <div class="space-y-4">
                            @foreach($applications as $application)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $application->job->title }}</h3>
                                            <p class="text-sm text-gray-500">{{ $application->job->employer->name }}</p>
                                        </div>
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full 
                                            @switch($application->status)
                                                @case('applied') bg-blue-100 text-blue-800 @break
                                                @case('shortlisted') bg-purple-100 text-purple-800 @break
                                                @case('interview') bg-yellow-100 text-yellow-800 @break
                                                @case('offer') bg-green-100 text-green-800 @break
                                                @case('rejected') bg-red-100 text-red-800 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-500">
                                        Match Score: {{ $application->match_score_snapshot }}%
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
