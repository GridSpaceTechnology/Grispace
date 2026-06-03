@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Talent Marketplace</h1>
                <p class="text-gray-600 mt-1">Discover top candidates for your team</p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 sticky top-24">
                    <form method="GET" action="{{ route('marketplace.index') }}" class="space-y-5">
                        <div>
                            <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                            <input type="text" name="search" id="search" 
                                   value="{{ request('search') }}"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                   placeholder="Name or role">
                        </div>

                        <div>
                            <label for="skills" class="block text-sm font-medium text-slate-700 mb-1">Skills</label>
                            <input type="text" name="skills" id="skills" 
                                   value="{{ request('skills') }}"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                   placeholder="e.g. JavaScript, Python">
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-slate-700 mb-1">Location</label>
                            <input type="text" name="location" id="location"
                                   value="{{ request('location') }}"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                   placeholder="City or Remote">
                        </div>

                        <div>
                            <label for="work_preference" class="block text-sm font-medium text-slate-700 mb-1">Work Mode</label>
                            <select name="work_preference" id="work_preference"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                                <option value="">Any</option>
                                <option value="remote" {{ request('work_preference') === 'remote' ? 'selected' : '' }}>Remote</option>
                                <option value="hybrid" {{ request('work_preference') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                <option value="onsite" {{ request('work_preference') === 'onsite' ? 'selected' : '' }}>On-site</option>
                            </select>
                        </div>

                        <div>
                            <label for="availability" class="block text-sm font-medium text-slate-700 mb-1">Availability</label>
                            <select name="availability" id="availability"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                                <option value="">Any</option>
                                <option value="immediate" {{ request('availability') === 'immediate' ? 'selected' : '' }}>Immediate</option>
                                <option value="2_weeks" {{ request('availability') === '2_weeks' ? 'selected' : '' }}>2 Weeks</option>
                                <option value="1_month" {{ request('availability') === '1_month' ? 'selected' : '' }}>1 Month</option>
                                <option value="passive" {{ request('availability') === 'passive' ? 'selected' : '' }}>Passive (Open to offers)</option>
                            </select>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                                Filter
                            </button>
                            <a href="{{ route('marketplace.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <div class="flex-1">
                <div class="mb-4 text-sm text-gray-500">
                    Found {{ $candidates->total() }} candidates
                </div>

                @if($candidates->isEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No candidates found</h3>
                        <p class="text-gray-500">Try adjusting your search filters</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($candidates as $candidate)
                            @php
                                $approvedSlugs = $candidate->relationLoaded('candidateVerifications')
                                    ? $candidate->candidateVerifications->where('status', 'approved')->pluck('verificationType.slug')->toArray()
                                    : [];
                            @endphp
                            <a href="{{ route('marketplace.candidate', ['candidate' => $candidate->id]) }}" class="block bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-12 h-12 bg-brand-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-brand-primary font-semibold">{{ substr($candidate->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $candidate->name }}</h3>
                                        <p class="text-sm text-gray-500">
                                            {{ $candidate->candidateProfile?->desired_role ?? 'Not specified' }}
                                        </p>
                                    </div>
                                </div>

                                @if($candidate->relationLoaded('trustScore') && $candidate->trustScore)
                                    <div class="mb-2 flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full
                                                @if($candidate->trustScore->score >= 76) bg-green-500
                                                @elseif($candidate->trustScore->score >= 51) bg-blue-500
                                                @elseif($candidate->trustScore->score >= 26) bg-amber-500
                                                @else bg-gray-400
                                                @endif"
                                                style="width: {{ $candidate->trustScore->score }}%">
                                            </div>
                                        </div>
                                        <span class="text-xs font-medium
                                            @if($candidate->trustScore->score >= 76) text-green-600
                                            @elseif($candidate->trustScore->score >= 51) text-blue-600
                                            @else text-gray-500
                                            @endif">
                                            {{ $candidate->trustScore->score }}/100
                                        </span>
                                    </div>
                                @endif

                                @if(!empty($approvedSlugs))
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        @foreach(array_intersect($approvedSlugs, ['identity', 'education', 'employment', 'certification']) as $slug)
                                            <x-verification-badge :type="$slug" status="verified"/>
                                        @endforeach
                                    </div>
                                @endif

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

                                <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                                    @if($candidate->candidateProfile?->years_of_experience)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $candidate->candidateProfile->years_of_experience }} years
                                        </span>
                                    @endif
                                    @if($candidate->candidateProfile?->location)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ $candidate->candidateProfile->location }}
                                        </span>
                                    @endif
                                    @if($candidate->candidateProfile?->work_preference)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                            </svg>
                                            {{ ucfirst($candidate->candidateProfile->work_preference) }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $candidates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
