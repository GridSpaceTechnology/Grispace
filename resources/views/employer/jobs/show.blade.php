@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('employer.jobs.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Jobs
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $job->title }}</h1>
                            <p class="text-gray-600 mt-1">{{ $job->role }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            @if($job->status === 'active') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif
                        ">
                            {{ ucfirst($job->status) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $job->location ?? 'Not specified' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ ucfirst($job->employment_type) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ ucfirst($job->work_preference) }}
                        </span>
                    </div>

                    @if($job->salary_min || $job->salary_max)
                        <div class="bg-green-50 rounded-lg p-4 mb-6">
                            <span class="text-sm text-green-800 font-medium">Salary Range:</span>
                            <span class="text-green-900">
                                ${{ number_format($job->salary_min ?? 0) }} - ${{ number_format($job->salary_max ?? 0) }}
                            </span>
                        </div>
                    @endif

                    <div class="prose max-w-none">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                        <p class="text-gray-600 whitespace-pre-line">{{ $job->description }}</p>

                        @if($job->responsibilities)
                            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">Responsibilities</h3>
                            <p class="text-gray-600 whitespace-pre-line">{{ $job->responsibilities }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Details</h2>
                    
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Posted</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $job->created_at->diffForHumans() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Department</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $job->department ?? 'Not specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Min. Experience</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $job->minimum_experience ?? 0 }} years</dd>
                        </div>
                        @if($job->required_skills_json)
                        <div>
                            <dt class="text-sm text-gray-500">Required Skills</dt>
                            <dd class="mt-2 flex flex-wrap gap-2">
                                @foreach($job->required_skills_json as $skill)
                                    <span class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">{{ $skill }}</span>
                                @endforeach
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Applications ({{ $job->applications->count() }})
                        </h2>
                        <a href="{{ route('employer.jobs.candidates', ['job' => $job->id]) }}" 
                           class="text-sm text-indigo-600 hover:text-indigo-700">
                            View Matches →
                        </a>
                    </div>
                    
                    @if($job->applications->isEmpty())
                        <p class="text-gray-500 text-sm">No applications yet</p>
                    @else
                        <div class="space-y-3">
                            @foreach($job->applications as $application)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-600 text-xs font-medium">{{ substr($application->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $application->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $application->match_score_snapshot }}% match</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-medium 
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
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
