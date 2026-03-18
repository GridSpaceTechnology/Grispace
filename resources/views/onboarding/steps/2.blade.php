@php
    $skills = $user->candidateSkills;
    $experiences = $user->candidateExperiences;
    $education = $user->candidateEducation;
@endphp

@extends('onboarding.layout', ['step' => $step, 'totalSteps' => $totalSteps, 'title' => 'Skills, Experience & Education'])

@section('content')
    <form method="POST" action="{{ route('candidate.onboarding.store', ['step' => $step]) }}">
        @csrf

        <div class="space-y-8">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Skills</h3>
                <div id="skills-container" class="space-y-3">
                    @foreach(old('skills', $skills->toArray() ?: [['name' => '', 'level' => 3]]) as $index => $skill)
                    <div class="flex gap-3 skill-row">
                        <input type="text" name="skills[{{ $index }}][name]" placeholder="Skill name"
                               value="{{ $skill['skill_name'] ?? $skill['name'] ?? '' }}"
                               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <select name="skills[{{ $index }}][level]" class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ ($skill['proficiency_level'] ?? $skill['level'] ?? 3) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    @endforeach
                </div>
                <button type="button" onclick="addSkillRow()" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700">+ Add Skill</button>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Work Experience</h3>
                <div id="experiences-container" class="space-y-4">
                    @foreach(old('experiences', $experiences->toArray() ?: [['company' => '', 'role' => '', 'duration' => '', 'description' => '']]) as $index => $exp)
                    <div class="p-4 border border-gray-200 rounded-lg experience-row">
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <input type="text" name="experiences[{{ $index }}][company]" placeholder="Company" 
                                   value="{{ $exp['company'] ?? '' }}"
                                   class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" name="experiences[{{ $index }}][role]" placeholder="Role"
                                   value="{{ $exp['role'] ?? '' }}"
                                   class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="mb-3">
                            <input type="text" name="experiences[{{ $index }}][duration]" placeholder="Duration (e.g. 2020-2023)"
                                   value="{{ $exp['duration'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <textarea name="experiences[{{ $index }}][description]" placeholder="Description"
                                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="2">{{ $exp['description'] ?? '' }}</textarea>
                    </div>
                    @endforeach
                </div>
                <button type="button" onclick="addExperienceRow()" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700">+ Add Experience</button>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Education</h3>
                <div id="education-container" class="space-y-3">
                    @foreach(old('education', $education->toArray() ?: [['institution' => '', 'qualification' => '', 'year_completed' => '']]) as $index => $edu)
                    <div class="grid grid-cols-3 gap-3 education-row">
                        <input type="text" name="education[{{ $index }}][institution]" placeholder="Institution"
                               value="{{ $edu['institution'] ?? '' }}"
                               class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <input type="text" name="education[{{ $index }}][qualification]" placeholder="Qualification"
                               value="{{ $edu['qualification'] ?? '' }}"
                               class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <input type="number" name="education[{{ $index }}][year_completed]" placeholder="Year"
                               value="{{ $edu['year_completed'] ?? '' }}"
                               class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    @endforeach
                </div>
                <button type="button" onclick="addEducationRow()" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700">+ Add Education</button>
            </div>
        </div>

        <div class="mt-8 flex justify-between">
            <a href="{{ route('candidate.onboarding.step', ['step' => $step - 1]) }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Back
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Continue
            </button>
        </div>
    </form>
@endsection
