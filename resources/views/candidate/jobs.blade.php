@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Available Jobs</h1>
            <p class="text-gray-600 mt-1">Find your next opportunity</p>
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

        @if($jobs->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No jobs available</h3>
                <p class="text-gray-500">Check back later for new opportunities</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($jobs as $match)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-indigo-600 font-semibold text-lg">{{ substr($match['job']->employer->name ?? 'C', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-900">{{ $match['job']->title }}</h2>
                                        <p class="text-gray-600">
                                            {{ $match['job']->employer->name ?? 'Company' }}
                                            @if($match['job']->company?->is_verified)
                                                <svg class="w-4 h-4 text-blue-500 inline" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                </svg>
                                                {{ $match['job']->location ?? 'Remote' }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                                </svg>
                                                {{ ucfirst($match['job']->work_preference) }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ ucfirst($match['job']->employment_type) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <x-badge variant="indigo">{{ $match['job']->role }}</x-badge>
                                    @if($match['job']->salary_min || $match['job']->salary_max)
                                        <x-badge variant="success">
                                            ${{ number_format($match['job']->salary_min ?? 0) }} - ${{ number_format($match['job']->salary_max ?? 0) }}
                                        </x-badge>
                                    @endif
                                    @if($match['job']->industry)
                                        <x-badge>{{ $match['job']->industry }}</x-badge>
                                    @endif
                                </div>

                                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Skills</span>
                                        <div class="font-medium text-gray-900">{{ $match['skill_score'] }}%</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Experience</span>
                                        <div class="font-medium text-gray-900">{{ $match['experience_score'] }}%</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Personality</span>
                                        <div class="font-medium text-gray-900">{{ $match['personality_score'] }}%</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Temperament</span>
                                        <div class="font-medium text-gray-900">{{ $match['temperament_score'] }}%</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-3">
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-indigo-600">{{ $match['match_percentage'] }}%</div>
                                    <div class="text-xs text-gray-500">match score</div>
                                </div>
                                <form method="POST" action="{{ route('candidate.jobs.apply', ['job' => $match['job']->id]) }}">
                                    @csrf
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                        Apply Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
