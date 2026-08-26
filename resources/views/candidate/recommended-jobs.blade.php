@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Recommended Jobs</h1>
                <p class="text-gray-600 mt-1">Ranked by how well each role fits your profile, skills and preferences</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('candidate.jobs') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    Browse All Jobs
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('candidate.recommended-jobs') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-2">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search roles or keywords"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <input type="text" name="location" value="{{ request('location') }}" placeholder="Location"
                       class="w-full rounded-lg border-slate-300 text-sm">
                <select name="work_preference" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Any work mode</option>
                    @foreach(['remote' => 'Remote', 'hybrid' => 'Hybrid', 'onsite' => 'On-site', 'flexible' => 'Flexible'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('work_preference') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="employment_type" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Any type</option>
                    @foreach(['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'freelance' => 'Freelance', 'internship' => 'Internship'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('employment_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#EB5233] hover:bg-[#d94527] text-white text-sm font-medium rounded-lg transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('candidate.recommended-jobs') }}"
                       class="px-3 py-2 border border-slate-300 text-slate-500 text-sm rounded-lg hover:bg-slate-50 transition-colors">
                        Reset
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                <select name="min_score" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Any match strength</option>
                    @foreach(['90' => 'Excellent (90%+)', '80' => 'Strong (80%+)', '70' => 'Good (70%+)', '60' => 'Potential (60%+)'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('min_score') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="number" name="salary_min" value="{{ request('salary_min') }}" min="0" placeholder="Minimum salary"
                       class="w-full rounded-lg border-slate-300 text-sm">
                <input type="text" name="company" value="{{ request('company') }}" placeholder="Company"
                       class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </form>

        @if($jobs->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No recommended jobs yet</h3>
                <p class="text-gray-500">Try adjusting your filters, or complete your profile so we can rank more roles for you</p>
            </div>
        @else
            <p class="text-sm text-gray-500 mb-4">{{ $jobs->total() }} {{ Str::plural('job', $jobs->total()) }} ranked for you</p>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($jobs as $item)
                    @php
                        $job = $item['job'];
                        $components = collect($item['breakdown']['components']);
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200 relative flex flex-col">
                        <div class="absolute top-3 right-3">
                            <x-match-badge :score="$item['overall_score']" :category="$item['category']" />
                        </div>

                        <div class="mb-4 pr-16">
                            <h3 class="font-semibold text-gray-900 leading-snug">{{ $job->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $job->company?->name ?? $job->employer->name ?? 'Confidential' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 mb-4">
                            <span>{{ $job->location ?? $job->location_country ?? 'Anywhere' }}</span>
                            <span class="uppercase tracking-wide">{{ $job->work_preference }}</span>
                            @if($job->salaryLabel())
                                <span class="font-medium text-gray-700">{{ $job->salaryLabel() }}</span>
                            @endif
                        </div>

                        @if($item['matched_skills'])
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($item['matched_skills'] as $skill)
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-xs">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($item['missing_skills'])
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($item['missing_skills'] as $skill)
                                    <span class="px-2 py-0.5 bg-red-50 text-red-600 border border-red-100 rounded text-xs">Missing: {{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($item['top_reasons'])
                            <ul class="space-y-1 mb-3">
                                @foreach($item['top_reasons'] as $reason)
                                    <li class="text-xs text-gray-600 flex items-start gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $reason }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <details class="border-t border-slate-100 pt-3 mt-auto">
                            <summary class="text-sm font-medium text-[#052E5C] cursor-pointer select-none">
                                Why this match?
                            </summary>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @foreach($components as $key => $component)
                                    <div class="flex items-center justify-between bg-slate-50 rounded px-2 py-1.5">
                                        <span class="text-xs text-gray-500">{{ $component['label'] }}</span>
                                        <span class="text-xs font-semibold text-gray-800">{{ $component['score'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>

                        <a href="{{ route('jobs.show', ['job' => $job]) }}"
                           class="mt-4 block w-full text-center text-sm bg-[#EB5233] hover:bg-[#d94527] text-white py-2 rounded-lg transition-colors font-medium">
                            View Job
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
