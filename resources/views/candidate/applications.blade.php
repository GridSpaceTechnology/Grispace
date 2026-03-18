@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">My Applications</h1>
            <p class="text-slate-600 mt-1">Track the status of your job applications</p>
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($applications as $application)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $application->job->title }}</h3>
                            <p class="text-sm text-slate-500">
                                {{ $application->job->employer->company_name }}
                                @if($application->job->employer->company?->is_verified)
                                    <svg class="w-3.5 h-3.5 text-blue-500 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </p>
                        </div>
                        @php
                            $statusConfig = [
                                'applied' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Applied'],
                                'shortlisted' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'Shortlisted'],
                                'interview' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Interview'],
                                'offer' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => 'Offer'],
                                'hired' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Hired'],
                                'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Rejected'],
                            ];
                            $status = $statusConfig[$application->status] ?? $statusConfig['applied'];
                        @endphp
                        <span class="px-2 py-1 {{ $status['bg'] }} {{ $status['text'] }} text-xs font-medium rounded-full">
                            {{ $status['label'] }}
                        </span>
                    </div>

                    @if($application->match_score)
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500">Match Score</span>
                                <span class="font-medium 
                                    @if($application->match_score >= 80) text-green-600
                                    @elseif($application->match_score >= 60) text-yellow-600
                                    @else text-red-600
                                    @endif">
                                    {{ $application->match_score }}%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full @if($application->match_score >= 80) bg-green-500 @elseif($application->match_score >= 60) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ $application->match_score }}%"></div>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>Applied {{ $application->applied_at?->diffForHumans() ?? $application->created_at->diffForHumans() }}</span>
                        <a href="{{ route('employer.jobs.show', ['job' => $application->job->id]) }}" class="text-brand-primary hover:text-brand-primary-hover">
                            View Job →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">No applications yet</h3>
                        <p class="text-slate-500 mb-4">Start applying to jobs to see them here</p>
                        <a href="{{ route('candidate.jobs') }}" class="inline-flex px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                            Browse Jobs
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
