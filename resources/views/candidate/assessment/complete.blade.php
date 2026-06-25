@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-white">
    <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="text-center mb-12">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="background-color: #EB5233;">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4">
                Profile Complete!
            </h1>
            <p class="text-xl text-slate-600">
                Your personality profile is ready. Here's what we've discovered.
            </p>
        </div>

        @if($profile)
            <div class="space-y-6">
                @if($profile->dimension_scores)
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                        <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <span>Personality Dimensions</span>
                        </h2>
                        <div class="space-y-4">
                            @php
                                $sorted = collect($profile->dimension_scores)->sortDesc();
                            @endphp
                            @foreach($sorted as $dimension => $score)
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-sm font-medium text-slate-700">{{ $dimension }}</span>
                                        <span class="text-sm font-bold" style="color: {{ $score >= 70 ? '#EB5233' : '#052E5C' }};">{{ $score }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-700 ease-out"
                                             style="width: {{ $score }}%; background-color: {{ $score >= 70 ? '#EB5233' : '#052E5C' }};">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($profile->dominant_traits)
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                        <h2 class="text-xl font-bold text-slate-900 mb-4">
                            Dominant Traits
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($profile->dominant_traits as $trait)
                                <span class="px-4 py-2 rounded-full text-sm font-medium text-white" style="background-color: #EB5233;">
                                    {{ $trait }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span>Personality Summary</span>
                    </h2>
                    <p class="text-lg text-slate-700 leading-relaxed">
                        {{ $profile->personality_summary }}
                    </p>
                </div>

                @if($profile->workplace_compatibility)
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                        <h2 class="text-xl font-bold text-slate-900 mb-4">
                            Workplace Compatibility
                        </h2>
                        <p class="text-lg text-slate-700 leading-relaxed">
                            {{ $profile->workplace_compatibility }}
                        </p>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">
                        Work Style
                    </h2>
                    <p class="text-lg text-slate-700 leading-relaxed">
                        {{ $profile->work_style_summary }}
                    </p>
                    <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-orange-50" style="color: #EB5233;">
                        {{ $profile->work_style }}
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">
                        Top Career Strengths
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(', ', $profile->strengths_summary) as $strength)
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-slate-100 text-slate-700">
                                {{ $strength }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">
                        Recommended Work Environment
                    </h2>
                    <p class="text-lg text-slate-700">
                        {{ $profile->organizational_fit }}
                    </p>
                </div>
            </div>

            @if($recommendedJobs && $recommendedJobs->isNotEmpty())
                <div class="mt-8 bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-6">
                        Recommended Job Matches
                    </h2>
                    <div class="space-y-4">
                        @foreach($recommendedJobs as $match)
                            @php $job = $match['job'] ?? null; @endphp
                            @if($job)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 hover:border-orange-400 hover:bg-orange-50 transition-all">
                                    <div>
                                        <h3 class="font-semibold text-slate-900">{{ $job->title }}</h3>
                                        <p class="text-sm text-slate-500">{{ $job->company?->name ?? 'Company' }}</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-slate-500">Match</span>
                                            <div class="text-lg font-bold" style="color: #EB5233;">{{ $match['overall_match_score'] ?? 0 }}%</div>
                                        </div>
                                        <a href="{{ route('jobs.show', $job) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #052E5C;">
                                            View Job
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-10 text-center">
                <a href="{{ route('candidate.dashboard') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white rounded-xl hover:opacity-90 transition-all duration-200 shadow-lg" style="background-color: #EB5233;">
                    View My Matches
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
