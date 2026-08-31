@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Profile</h1>
            <p class="mt-2 text-slate-600">Update your information, photo and resume.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="font-semibold text-slate-900">Profile Completion</p>
                <p class="text-xl font-bold text-brand-primary">{{ $profileCompletion }}%</p>
            </div>
            <x-progress-bar :value="$profileCompletion" size="sm" />
            <ul class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($profileCompletionItems as $item)
                    <li class="flex items-center gap-2 text-sm {{ $item['earned'] ? 'text-slate-700' : 'text-slate-400' }}">
                        @if($item['earned'])
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                        {{ $item['label'] }}
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('candidate.profile.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Basic Information</h2>
                
                <div class="space-y-6">
                    <div>
                        <label for="current_role" class="block text-sm font-medium text-slate-700 mb-1">Current Role</label>
                        <input type="text" name="current_role" id="current_role" value="{{ old('current_role', $profile?->current_role) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="desired_role" class="block text-sm font-medium text-slate-700 mb-1">Desired Role</label>
                        <input type="text" name="desired_role" id="desired_role" value="{{ old('desired_role', $profile?->desired_role) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="years_of_experience" class="block text-sm font-medium text-slate-700 mb-1">Years of Experience</label>
                            <input type="number" name="years_of_experience" id="years_of_experience" value="{{ old('years_of_experience', $profile?->years_of_experience) }}" min="0"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="industry" class="block text-sm font-medium text-slate-700 mb-1">Industry</label>
                            <input type="text" name="industry" id="industry" value="{{ old('industry', $profile?->industry) }}"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Preferences</h2>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="employment_type_preference" class="block text-sm font-medium text-slate-700 mb-1">Employment Type</label>
                            <select name="employment_type_preference" id="employment_type_preference"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select type</option>
                                @foreach(['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'freelance' => 'Freelance', 'internship' => 'Internship'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('employment_type_preference', $profile?->employment_type_preference) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="work_preference" class="block text-sm font-medium text-slate-700 mb-1">Work Preference</label>
                            <select name="work_preference" id="work_preference"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select preference</option>
                                @foreach(['remote' => 'Remote', 'hybrid' => 'Hybrid', 'onsite' => 'On-site', 'flexible' => 'Flexible'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('work_preference', $profile?->work_preference) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="salary_expectation" class="block text-sm font-medium text-slate-700 mb-1">Salary Expectation</label>
                        <input type="number" name="salary_expectation" id="salary_expectation" value="{{ old('salary_expectation', $profile?->salary_expectation) }}" min="0"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Resume & Profile Assets</h2>

                <div class="space-y-6">
                    <div>
                        <label for="resume" class="block text-sm font-medium text-slate-700 mb-1">Resume (PDF, DOC, DOCX)</label>
                        <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @if($media?->cv_path)
                            <div class="mt-2 inline-flex items-center gap-3 text-sm">
                                <span class="text-emerald-600">Resume uploaded</span>
                                <a href="{{ route('candidate.resume.view') }}" target="_blank"
                                    class="text-indigo-600 hover:text-indigo-700 underline">View</a>
                                <a href="{{ route('candidate.resume.download') }}"
                                    class="text-indigo-600 hover:text-indigo-700 underline">Download</a>
                            </div>
                        @endif
                        @error('resume')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="linkedin_url" class="block text-sm font-medium text-slate-700 mb-1">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $media?->linkedin_url) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="github_url" class="block text-sm font-medium text-slate-700 mb-1">GitHub / Portfolio URL</label>
                        <input type="url" name="github_url" id="github_url" value="{{ old('github_url', $media?->github_url) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="role_video_url" class="block text-sm font-medium text-slate-700 mb-1">Intro / Role Video URL</label>
                        <input type="url" name="role_video_url" id="role_video_url" value="{{ old('role_video_url', $media?->role_video_url) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">About You</h2>
                
                <div>
                    <label for="greatest_achievement" class="block text-sm font-medium text-slate-700 mb-1">Greatest Achievement</label>
                    <textarea name="greatest_achievement" id="greatest_achievement" rows="4"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('greatest_achievement', $profile?->greatest_achievement) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection