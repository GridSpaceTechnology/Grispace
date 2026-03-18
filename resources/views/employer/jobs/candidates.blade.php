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
                <h1 class="text-3xl font-bold text-gray-900">Top Matching Candidates</h1>
                <p class="text-gray-600 mt-1">Candidates best matched for: {{ $job->title }}</p>
            </div>
        </div>

        @if($candidates->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No matching candidates found</h3>
                <p class="text-gray-500">Try posting more jobs or wait for new candidates to join</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($candidates as $item)
                    @php $candidate = $item['candidate']; @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200 relative">
                        <div class="absolute -top-3 -right-3 w-16 h-16 rounded-full flex items-center justify-center 
                            @if($item['match_percentage'] >= 80) bg-green-500
                            @elseif($item['match_percentage'] >= 60) bg-yellow-500
                            @else bg-gray-400
                            @endif">
                            <span class="text-white font-bold text-sm">{{ $item['match_percentage'] }}%</span>
                        </div>

                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                <span class="text-brand-primary font-semibold">{{ substr($candidate->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $candidate->name }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ $candidate->candidateProfile?->desired_role ?? 'Not specified' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($candidate->candidateSkills->take(5) as $skill)
                                <span class="px-2 py-1 bg-slate-100 rounded text-xs text-slate-700">
                                    {{ $skill->skill_name }}
                                </span>
                            @endforeach
                            @if($candidate->candidateSkills->count() > 5)
                                <span class="px-2 py-1 text-xs text-gray-500">
                                    +{{ $candidate->candidateSkills->count() - 5 }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-3 text-xs text-gray-500 mb-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $candidate->candidateProfile?->years_of_experience ?? 0 }} years
                            </span>
                            @if($candidate->candidateProfile?->location)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    {{ $candidate->candidateProfile->location }}
                                </span>
                            @endif
                        </div>

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

                        <div class="flex gap-2">
                            <a href="{{ route('employer.marketplace.candidate', ['candidate' => $candidate->id]) }}" 
                               class="flex-1 text-center text-sm border border-slate-300 text-slate-700 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                                View Profile
                            </a>
                            @if(in_array($candidate->id, $shortlistedIds ?? []))
                                <button disabled class="px-4 py-2 bg-green-100 text-green-700 text-sm rounded-lg cursor-default">
                                    Shortlisted
                                </button>
                            @else
                                <form method="POST" action="{{ route('employer.marketplace.shortlist', ['candidate' => $candidate->id]) }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-brand-primary text-white text-sm rounded-lg hover:bg-brand-primary-hover transition-colors">
                                        Shortlist
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
