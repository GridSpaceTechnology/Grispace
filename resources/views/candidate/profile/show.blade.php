@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-secondary to-primary h-32"></div>
            <div class="px-6 pb-6">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between -mt-12 mb-6">
                    <div class="flex items-end gap-4">
                        <div class="w-24 h-24 bg-white rounded-xl shadow-lg flex items-center justify-center -mt-4">
                            <span class="text-indigo-600 font-bold text-3xl">{{ substr($candidate->name, 0, 1) }}</span>
                        </div>
                        <div class="pb-2">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $candidate->name }}</h1>
                            <p class="text-gray-600">{{ $candidate->candidateProfile?->desired_role ?? 'Not specified' }}</p>
                        </div>
                    </div>
                </div>

                @if($candidate->candidateProfile)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Experience</p>
                            <p class="font-semibold text-gray-900">{{ $candidate->candidateProfile->years_of_experience ?? 0 }} years</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Industry</p>
                            <p class="font-semibold text-gray-900">{{ $candidate->candidateProfile->industry ?? 'Not specified' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Work Preference</p>
                            <p class="font-semibold text-gray-900">{{ $candidate->candidateProfile->work_preference ? ucfirst($candidate->candidateProfile->work_preference) : 'Not specified' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Employment Type</p>
                            <p class="font-semibold text-gray-900">{{ $candidate->candidateProfile->employment_type_preference ? ucfirst($candidate->candidateProfile->employment_type_preference) : 'Not specified' }}</p>
                        </div>
                    </div>

                    @if($candidate->candidateProfile->location)
                        <div class="flex items-center gap-2 text-gray-600 mb-6">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $candidate->candidateProfile->location }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <div class="lg:col-span-2 space-y-6">
                @if($candidate->candidateProfile?->career_achievement)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Greatest Career Achievement</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ $candidate->candidateProfile->career_achievement }}</p>
                    </div>
                @endif

                @if($candidate->candidateExperiences->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Work Experience</h2>
                        <div class="space-y-6">
                            @foreach($candidate->candidateExperiences as $experience)
                                <div>
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $experience->role }}</h3>
                                            <p class="text-gray-600">{{ $experience->company }}</p>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $experience->duration }}</span>
                                    </div>
                                    @if($experience->description)
                                        <p class="mt-2 text-sm text-gray-600">{{ $experience->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($candidate->candidateEducation->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Education</h2>
                        <div class="space-y-4">
                            @foreach($candidate->candidateEducation as $education)
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $education->qualification }}</h3>
                                        <p class="text-gray-600">{{ $education->institution }}</p>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $education->year_completed }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                @if($candidate->candidateSkills->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Skills</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($candidate->candidateSkills as $skill)
                                <span class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-full text-sm">
                                    {{ $skill->skill_name }}
                                    <span class="ml-1 text-xs text-indigo-500">({{ $skill->proficiency_level }}/5)</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($candidate->candidateAssessment)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Assessment Results</h2>
                        
                        @if($candidate->candidateAssessment->temperament_type)
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">Temperament</p>
                                <span class="px-3 py-1.5 bg-purple-50 text-purple-700 rounded-full text-sm font-medium">
                                    {{ $candidate->candidateAssessment->temperament_type }}
                                </span>
                            </div>
                        @endif

                        @if($candidate->candidateAssessment->personality_scores_json)
                            <div>
                                <p class="text-sm text-gray-500 mb-2">Personality Traits</p>
                                <div class="space-y-2">
                                    @foreach($candidate->candidateAssessment->personality_scores_json as $trait => $score)
                                        <div>
                                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                                <span class="capitalize">{{ str_replace('_', ' ', $trait) }}</span>
                                                <span>{{ $score }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $score }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if($candidate->candidateProfile?->linkedin_url || $candidate->candidateProfile?->portfolio_url)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Links</h2>
                        <div class="space-y-2">
                            @if($candidate->candidateProfile?->linkedin_url)
                                <a href="{{ $candidate->candidateProfile->linkedin_url }}" target="_blank" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                    </svg>
                                    LinkedIn Profile
                                </a>
                            @endif
                            @if($candidate->candidateProfile?->portfolio_url)
                                <a href="{{ $candidate->candidateProfile->portfolio_url }}" target="_blank" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                    </svg>
                                    Portfolio
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
