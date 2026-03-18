@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Recommended Jobs</h1>
                <p class="text-gray-600 mt-1">Jobs that best match your profile and preferences</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('candidate.jobs') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    Browse All Jobs
                </a>
            </div>
        </div>

        @if($jobs->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No recommended jobs yet</h3>
                <p class="text-gray-500">Complete your profile to get personalized job recommendations</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($jobs as $item)
                    @php $job = $item['job']; @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200 relative">
                        <div class="absolute -top-3 -right-3 w-16 h-16 rounded-full flex items-center justify-center 
                            @if($item['match_percentage'] >= 80) bg-green-500
                            @elseif($item['match_percentage'] >= 60) bg-yellow-500
                            @else bg-gray-400
                            @endif">
                            <span class="text-white font-bold text-sm">{{ $item['match_percentage'] }}%</span>
                        </div>

                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-900">{{ $job->title }}</h3>
                            <p class="text-sm text-gray-500">{{ $job->role }}</p>
                        </div>

                        <div class="flex flex-wrap gap-3 text-xs text-gray-500 mb-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $job->company?->name ?? $job->employer->name ?? 'Company' }}
                                @if($job->company?->is_verified)
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $job->location ?? $job->location_country ?? 'Remote' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                </svg>
                                {{ ucfirst($job->work_preference ?? 'On-site') }}
                            </span>
                        </div>

                        @if($job->salary_min || $job->salary_max)
                            <div class="text-sm font-medium text-gray-700 mb-3">
                                @if($job->salary_min && $job->salary_max)
                                    ${{ number_format($job->salary_min) }} - ${{ number_format($job->salary_max) }}
                                @elseif($job->salary_min)
                                    From ${{ number_format($job->salary_min) }}
                                @else
                                    Up to ${{ number_format($job->salary_max) }}
                                @endif
                            </div>
                        @endif

                        <div class="border-t border-slate-100 pt-3 mb-3">
                            <p class="text-xs text-gray-500 mb-2">Match Breakdown:</p>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div class="text-center">
                                    <div class="font-medium text-gray-700">{{ $item['skill_score'] }}</div>
                                    <div class="text-gray-400">Skills</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-medium text-gray-700">{{ $item['experience_score'] }}</div>
                                    <div class="text-gray-400">Experience</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-medium text-gray-700">{{ $item['location_score'] ?? 0 }}</div>
                                    <div class="text-gray-400">Location</div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('candidate.jobs') }}" 
                           class="block w-full text-center text-sm border border-slate-300 text-slate-700 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                            View Details
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
