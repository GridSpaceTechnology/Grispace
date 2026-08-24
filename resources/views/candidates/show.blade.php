@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ url()->previous() ?: route('jobs.index') }}" class="text-brand-primary hover:text-brand-primary-hover flex items-center gap-2 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Header --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row items-start gap-5">
                        @if($candidate->profile_photo_path)
                            <img src="{{ Storage::url($candidate->profile_photo_path) }}" alt="{{ $candidate->name }}" class="w-24 h-24 rounded-full object-cover flex-shrink-0 border border-slate-200">
                        @else
                            <div class="w-24 h-24 rounded-full bg-brand-primary/10 flex items-center justify-center flex-shrink-0">
                                <span class="text-brand-primary text-3xl font-semibold">{{ mb_strtoupper(mb_substr($candidate->name, 0, 1)) }}</span>
                            </div>
                        @endif

                        <div class="flex-1 min-w-0 w-full">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $candidate->name }}</h1>

                            <p class="text-lg text-gray-600 mt-1">
                                {{ $candidate->candidateProfile?->current_role ?? $candidate->candidateProfile?->desired_role ?? 'Professional' }}
                                @if($candidate->candidateProfile?->years_of_experience)
                                    · {{ $candidate->candidateProfile->years_of_experience }}+ yrs experience
                                @endif
                            </p>

                            @if($candidate->candidateProfile?->desired_role && $candidate->candidateProfile?->current_role)
                                <p class="text-gray-500">Seeking: {{ $candidate->candidateProfile->desired_role }}</p>
                            @endif

                            @if($candidate->hasVerifiedEmail())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    <x-verification-badge type="email" status="verified"/>
                                    @foreach($approvedVerifications as $slug)
                                        <x-verification-badge :type="$slug" status="verified"/>
                                    @endforeach
                                </div>
                            @endif

                            @if($canViewPhoneNumber && $candidate->phone_number)
                                <p class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $candidate->phone_number }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600 border-t border-slate-100 pt-5">
                        @if($candidate->candidateProfile?->industry)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $candidate->candidateProfile->industry }}
                            </div>
                        @endif
                        @if($candidate->candidateProfile?->work_preference)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Prefers {{ ucfirst($candidate->candidateProfile->work_preference) }}
                            </div>
                        @endif
                        @if($candidate->candidateProfile?->employment_type_preference)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ ucfirst(str_replace('_', '-', $candidate->candidateProfile->employment_type_preference)) }}
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Achievement / About --}}
                @if($candidate->candidateProfile?->greatest_achievement)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">About</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($candidate->candidateProfile->greatest_achievement)) !!}</div>
                    </div>
                @endif

                {{-- Experience --}}
                @if($candidate->candidateExperiences->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Work Experience</h2>
                        <ol class="space-y-6">
                            @foreach($candidate->candidateExperiences as $experience)
                                <li class="relative pl-8 border-l-2 border-slate-100 last:border-transparent">
                                    <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full bg-brand-primary/70"></span>
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $experience->role }}</h3>
                                        <p class="text-sm text-gray-600">
                                            {{ $experience->company }}
                                            @if($experience->duration)
                                                · {{ $experience->duration }}
                                            @endif
                                        </p>
                                        @if($experience->description)
                                            <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">{{ $experience->description }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                {{-- Education --}}
                @if($candidate->candidateEducation->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Education</h2>
                        <ul class="space-y-4">
                            @foreach($candidate->candidateEducation as $education)
                                <li>
                                    <h3 class="font-medium text-gray-900">{{ $education->qualification }}</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $education->institution }}
                                        @if($education->year_completed) · {{ $education->year_completed }}@endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Skills --}}
                @if($candidate->candidateSkills->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Skills</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($candidate->candidateSkills as $skill)
                                <x-badge variant="brand">{{ $skill->skill?->name ?? $skill->skill_name }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Portfolio links --}}
                @if($candidate->candidateMedia && ($candidate->candidateMedia->portfolio_links_json || $candidate->candidateMedia->linkedin_url || $candidate->candidateMedia->github_url))
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Portfolio & Links</h2>
                        <ul class="space-y-2 text-sm">
                            @foreach(collect($candidate->candidateMedia->portfolio_links_json ?? []) as $link)
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    <a href="{{ is_array($link) ? ($link['url'] ?? '#') : $link }}" target="_blank" rel="noopener noreferrer" class="text-brand-primary hover:underline break-all">
                                        {{ is_array($link) ? ($link['label'] ?? $link['url']) : $link }}
                                    </a>
                                </li>
                            @endforeach
                            @if($candidate->candidateMedia->linkedin_url)
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M16.338 0H3.662C1.641 0 0 1.641 0 3.662v12.676C0 18.359 1.641 20 3.662 20h12.676C18.359 20 20 18.359 20 16.338V3.662C20 1.641 18.359 0 16.338 0zM6.35 17H3.55V7.5h2.8V17zM4.95 6.25a1.65 1.65 0 110-3.3 1.65 1.65 0 010 3.3zM17 17h-2.8v-4.55c0-1.08-.02-2.47-1.5-2.47-1.51 0-1.74 1.18-1.74 2.39V17H8.16V7.5h2.68v1.3h.04c.37-.71 1.29-1.46 2.66-1.46 2.85 0 3.38 1.87 3.38 4.31V17z"/></svg>
                                    <a href="{{ $candidate->candidateMedia->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="text-brand-primary hover:underline">LinkedIn</a>
                                </li>
                            @endif
                            @if($candidate->candidateMedia->github_url)
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-800 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd"/></svg>
                                    <a href="{{ $candidate->candidateMedia->github_url }}" target="_blank" rel="noopener noreferrer" class="text-brand-primary hover:underline">GitHub</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                @if($trustScore)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex justify-center">
                        <x-trust-score :score="$trustScore->score" :level="$trustScore->level" size="md"/>
                    </div>
                @endif

                @if($candidate->personalityProfile && $candidate->personalityProfile->assessment_completed)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h2 class="text-base font-semibold text-gray-900 mb-3">Personality Highlights</h2>
                        <dl class="space-y-2 text-sm">
                            @foreach(collect([
                                'Work Style' => $candidate->personalityProfile->work_style,
                                'Communication' => $candidate->personalityProfile->communication_style,
                                'Collaboration' => $candidate->personalityProfile->collaboration_style,
                                'Leadership' => $candidate->personalityProfile->leadership_style,
                                'Motivation' => $candidate->personalityProfile->motivation_type,
                                'Temperament' => $candidate->personalityProfile->temperament_type,
                            ])->filter() as $label => $value)
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-gray-500">{{ $label }}</dt>
                                    <dd class="font-medium text-gray-800 text-right">{{ Str::title(str_replace('_', ' ', $value)) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        @if($candidate->personalityProfile->strengths_summary)
                            <p class="mt-3 pt-3 border-t border-slate-100 text-sm text-gray-600">{{ Str::limit($candidate->personalityProfile->strengths_summary, 220) }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
