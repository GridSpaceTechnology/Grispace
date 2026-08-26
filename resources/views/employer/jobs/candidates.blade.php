@extends('layouts.app')

@section('content')
<style>
    .view-profile-btn {
        position: relative;
        background: transparent;
        border: none;
        z-index: 1;
        overflow: visible;
    }

    .view-profile-btn::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        padding: 2px;
        background: conic-gradient(from var(--angle, 0deg), #052E5C, #EB5233, #052E5C, #EB5233, #052E5C);
        -webkit-mask:
            linear-gradient(#fff 0 0) content-box,
            linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        animation: spinBorder 2s linear infinite;
    }

    .view-profile-btn::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: transparent;
        z-index: -1;
        box-shadow: 0 0 0 0 transparent;
        transition: box-shadow 0.3s ease;
    }

    .view-profile-btn:hover::after {
        box-shadow:
            0 0 12px 2px rgba(235, 82, 51, 0.25),
            0 0 24px 4px rgba(5, 46, 92, 0.12);
    }

    @property --angle {
        syntax: "<angle>";
        initial-value: 0deg;
        inherits: false;
    }

    @keyframes spinBorder {
        to { --angle: 360deg; }
    }

    @media (prefers-reduced-motion: reduce) {
        .view-profile-btn::before {
            animation: none;
        }
    }
</style>
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('employer.jobs.show', ['job' => $job->id]) }}" class="text-[#052E5C] hover:underline flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Job
            </a>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Matching Candidates</h1>
                <p class="text-gray-600 mt-1">Ranked by professional fit for: <span class="font-medium">{{ $job->title }}</span></p>
            </div>
            @if($job->status === 'open')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 w-fit">
                    Open for applications
                </span>
            @endif
        </div>

        <form method="GET" action="{{ route('employer.jobs.candidates', ['job' => $job->id]) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, role or skill"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <input type="text" name="skills" value="{{ request('skills') }}" placeholder="Skills (comma separated)"
                       class="w-full rounded-lg border-slate-300 text-sm">
                <select name="min_score" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Any match strength</option>
                    @foreach(['90' => 'Excellent (90%+)', '80' => 'Strong (80%+)', '70' => 'Good (70%+)', '60' => 'Potential (60%+)'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('min_score') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#EB5233] hover:bg-[#d94527] text-white text-sm font-medium rounded-lg transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('employer.jobs.candidates', ['job' => $job->id]) }}"
                       class="px-3 py-2 border border-slate-300 text-slate-500 text-sm rounded-lg hover:bg-slate-50 transition-colors">
                        Reset
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">
                <div class="flex items-center gap-2">
                    <input type="number" name="experience_min" value="{{ request('experience_min') }}" min="0" placeholder="Min years"
                           class="w-full rounded-lg border-slate-300 text-sm">
                    <span class="text-gray-400 text-sm">–</span>
                    <input type="number" name="experience_max" value="{{ request('experience_max') }}" min="0" placeholder="Max years"
                           class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <select name="work_preference" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Any work preference</option>
                    @foreach(['remote' => 'Prefers remote', 'hybrid' => 'Prefers hybrid', 'onsite' => 'Prefers on-site', 'flexible' => 'Flexible'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('work_preference') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="location" value="{{ request('location') }}" placeholder="Location"
                       class="w-full rounded-lg border-slate-300 text-sm">
                <select name="availability" class="w-full rounded-lg border-slate-300 text-sm" disabled title="Coming soon">
                    <option value="">Availability (coming soon)</option>
                </select>
            </div>
        </form>

        @if($candidates->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No matching candidates found</h3>
                <p class="text-gray-500">Try widening your filters - new candidates join every week</p>
            </div>
        @else
            <p class="text-sm text-gray-500 mb-4">{{ $candidates->total() }} {{ Str::plural('candidate', $candidates->total()) }} ranked</p>

            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($candidates as $item)
                    @php
                        $candidate = $item['candidate'];
                        $hasApplied = in_array($candidate->id, $appliedIds ?? []);
                        $isShortlisted = in_array($candidate->id, $shortlistedIds ?? []);
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200 relative flex flex-col">
                        <div class="absolute top-3 right-3">
                            <x-match-badge :score="$item['overall_score']" :category="$item['category']" />
                        </div>

                        <div class="flex items-center gap-3 mb-4 pr-16">
                            @if($candidate->profile_photo_url)
                                <img src="{{ $candidate->profile_photo_url }}" alt="{{ $candidate->name }}"
                                     class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div class="w-12 h-12 bg-[#052E5C]/10 rounded-full flex items-center justify-center shrink-0">
                                    <span class="text-[#052E5C] font-semibold">{{ substr($candidate->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $candidate->name }}</h3>
                                <p class="text-sm text-gray-500 truncate">
                                    {{ $candidate->candidateProfile?->desired_role ?? $candidate->candidateProfile?->current_role ?? 'Role not specified' }}
                                </p>
                            </div>
                        </div>

                        @if($hasApplied || $isShortlisted)
                            <div class="flex gap-1.5 mb-3">
                                @if($hasApplied)
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded text-xs font-medium">Applied</span>
                                @endif
                                @if($isShortlisted)
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-xs font-medium">Shortlisted</span>
                                @endif
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 mb-3">
                            <span>{{ $candidate->candidateProfile?->years_of_experience ?? 0 }} yrs experience</span>
                            @if($candidate->candidateProfile?->work_preference)
                                <span class="uppercase tracking-wide">{{ $candidate->candidateProfile->work_preference }} preferred</span>
                            @endif
                            @if($candidate->candidateProfile?->location_country)
                                <span>{{ $candidate->candidateProfile->location_country }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($candidate->candidateSkills->take(5) as $skill)
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-xs text-slate-700">{{ $skill->skill_name }}</span>
                            @endforeach
                        </div>

                        @if($item['matched_skills'])
                            <p class="text-xs text-gray-500 mb-1">Matching skills:</p>
                            <div class="flex flex-wrap gap-1 mb-2">
                                @foreach($item['matched_skills'] as $skill)
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-xs">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($item['missing_skills'])
                            <p class="text-xs text-gray-500 mb-1">Skill gaps:</p>
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($item['missing_skills'] as $skill)
                                    <span class="px-2 py-0.5 bg-red-50 text-red-600 border border-red-100 rounded text-xs">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($item['strengths'])
                            <ul class="space-y-1 mt-auto mb-3">
                                @foreach($item['strengths'] as $strength)
                                    <li class="text-xs text-gray-600 flex items-start gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $strength }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="flex gap-2 mt-auto pt-2">
                            <a href="{{ route('employer.marketplace.candidate', ['candidate' => $candidate->id]) }}"
                               class="flex-1 text-center text-sm view-profile-btn text-[#052E5C] hover:text-[#EB5233] py-2 rounded-lg font-medium transition-colors">
                                View Profile
                            </a>
                            @unless($isShortlisted)
                                <form method="POST" action="{{ route('employer.marketplace.shortlist', ['candidate' => $candidate->id]) }}">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                    <button type="submit" class="px-4 py-2 border border-[#EB5233] text-[#EB5233] hover:bg-orange-50 text-sm rounded-lg transition-colors">
                                        Shortlist
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
