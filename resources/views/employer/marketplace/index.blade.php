@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Talent Marketplace</h1>
                <p class="text-gray-600 mt-1">Discover top candidates that match your requirements</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="{{ route('employer.shortlists') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    Shortlist
                </a>
                <a href="{{ route('employer.interviews.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    Interviews
                </a>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 sticky top-24">
                    <form method="GET" action="{{ route('employer.marketplace.index') }}" class="space-y-5">
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

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="experience_min" class="block text-sm font-medium text-slate-700 mb-1">Min Exp.</label>
                                <input type="number" name="experience_min" id="experience_min" min="0"
                                       value="{{ request('experience_min') }}"
                                       class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                       placeholder="0">
                            </div>
                            <div>
                                <label for="experience_max" class="block text-sm font-medium text-slate-700 mb-1">Max Exp.</label>
                                <input type="number" name="experience_max" id="experience_max" min="0"
                                       value="{{ request('experience_max') }}"
                                       class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                       placeholder="10">
                            </div>
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-slate-700 mb-1">Location</label>
                            <input type="text" name="location" id="location"
                                   value="{{ request('location') }}"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                   placeholder="City or Remote">
                        </div>

                        <div>
                            <label for="industry" class="block text-sm font-medium text-slate-700 mb-1">Industry</label>
                            <input type="text" name="industry" id="industry"
                                   value="{{ request('industry') }}"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                   placeholder="e.g. Technology">
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
                            <label for="temperament" class="block text-sm font-medium text-slate-700 mb-1">Temperament</label>
                            <select name="temperament" id="temperament"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                                <option value="">Any</option>
                                <option value="analytical" {{ request('temperament') === 'analytical' ? 'selected' : '' }}>Analytical</option>
                                <option value="expressive" {{ request('temperament') === 'expressive' ? 'selected' : '' }}>Expressive</option>
                                <option value="amiable" {{ request('temperament') === 'amiable' ? 'selected' : '' }}>Amiable</option>
                                <option value="driver" {{ request('temperament') === 'driver' ? 'selected' : '' }}>Driver</option>
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

                        <div>
                            <label for="salary_max" class="block text-sm font-medium text-slate-700 mb-1">Max Expected Salary</label>
                            <input type="number" name="salary_max" id="salary_max"
                                   value="{{ request('salary_max') }}"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                                   placeholder="e.g. 100000">
                        </div>

                        <div>
                            <label for="experience_level" class="block text-sm font-medium text-slate-700 mb-1">Experience Level</label>
                            <select name="experience_level" id="experience_level"
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                                <option value="">Any</option>
                                <option value="entry" {{ request('experience_level') === 'entry' ? 'selected' : '' }}>Entry Level</option>
                                <option value="mid" {{ request('experience_level') === 'mid' ? 'selected' : '' }}>Mid Level</option>
                                <option value="senior" {{ request('experience_level') === 'senior' ? 'selected' : '' }}>Senior Level</option>
                                <option value="lead" {{ request('experience_level') === 'lead' ? 'selected' : '' }}>Lead/Principal</option>
                                <option value="executive" {{ request('experience_level') === 'executive' ? 'selected' : '' }}>Executive</option>
                            </select>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                                Filter
                            </button>
                            <a href="{{ route('employer.marketplace.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <div class="flex-1">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

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
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
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
                                    @if($candidate->candidateProfile?->work_preference)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                            </svg>
                                            {{ ucfirst($candidate->candidateProfile->work_preference) }}
                                        </span>
                                    @endif
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

                    <div class="mt-6">
                        {{ $candidates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
