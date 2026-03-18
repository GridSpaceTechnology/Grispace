@php
    $profile = $user->candidateProfile;
@endphp

@extends('onboarding.layout', ['step' => $step, 'totalSteps' => $totalSteps, 'title' => 'Basic Professional Info'])

@section('content')
    <form method="POST" action="{{ route('candidate.onboarding.store', ['step' => $step]) }}">
        @csrf

        <div class="space-y-6">
            <div>
                <label for="current_role" class="block text-sm font-medium text-gray-700 mb-1">Current Role</label>
                <input type="text" name="current_role" id="current_role" 
                       value="{{ old('current_role', $profile?->current_role) }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="e.g. Software Engineer">
            </div>

            <div>
                <label for="desired_role" class="block text-sm font-medium text-gray-700 mb-1">Desired Role *</label>
                <input type="text" name="desired_role" id="desired_role" required
                       value="{{ old('desired_role', $profile?->desired_role) }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="e.g. Senior Software Engineer">
                @error('desired_role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="years_of_experience" class="block text-sm font-medium text-gray-700 mb-1">Years of Experience</label>
                    <input type="number" name="years_of_experience" id="years_of_experience" min="0" max="50"
                           value="{{ old('years_of_experience', $profile?->years_of_experience ?? 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                    <input type="text" name="industry" id="industry"
                           value="{{ old('industry', $profile?->industry) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="e.g. Technology">
                </div>
            </div>

            <div>
                <label for="employment_type_preference" class="block text-sm font-medium text-gray-700 mb-1">Employment Type Preference</label>
                <select name="employment_type_preference" id="employment_type_preference"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select preference</option>
                    <option value="full-time" {{ old('employment_type_preference', $profile?->employment_type_preference) == 'full-time' ? 'selected' : '' }}>Full-time</option>
                    <option value="part-time" {{ old('employment_type_preference', $profile?->employment_type_preference) == 'part-time' ? 'selected' : '' }}>Part-time</option>
                    <option value="contract" {{ old('employment_type_preference', $profile?->employment_type_preference) == 'contract' ? 'selected' : '' }}>Contract</option>
                    <option value="freelance" {{ old('employment_type_preference', $profile?->employment_type_preference) == 'freelance' ? 'selected' : '' }}>Freelance</option>
                </select>
            </div>

            <div>
                <label for="work_preference" class="block text-sm font-medium text-gray-700 mb-1">Work Preference</label>
                <select name="work_preference" id="work_preference"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select preference</option>
                    <option value="remote" {{ old('work_preference', $profile?->work_preference) == 'remote' ? 'selected' : '' }}>Remote</option>
                    <option value="hybrid" {{ old('work_preference', $profile?->work_preference) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    <option value="onsite" {{ old('work_preference', $profile?->work_preference) == 'onsite' ? 'selected' : '' }}>On-site</option>
                </select>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('home') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Continue
            </button>
        </div>
    </form>
@endsection
