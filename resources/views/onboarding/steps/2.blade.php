@php
    $skills = $user->candidateSkills;
    $experiences = $user->candidateExperiences;
    $education = $user->candidateEducation;

    $skillsInitial = collect(old('skills', $skills->toArray() ?: [['name' => '', 'level' => 3]]))
        ->map(fn ($skill) => [
            'name' => $skill['skill_name'] ?? $skill['name'] ?? '',
            'level' => (int) ($skill['proficiency_level'] ?? $skill['level'] ?? 3),
        ])->values()->all();

    $experiencesInitial = collect(old('experiences', $experiences->toArray() ?: [['company' => '', 'role' => '', 'duration' => '', 'description' => '']]))
        ->map(fn ($exp) => [
            'company' => $exp['company'] ?? '',
            'role' => $exp['role'] ?? '',
            'duration' => $exp['duration'] ?? '',
            'description' => $exp['description'] ?? '',
        ])->values()->all();

    $educationInitial = collect(old('education', $education->toArray() ?: [['institution' => '', 'qualification' => '', 'year_completed' => '']]))
        ->map(fn ($edu) => [
            'institution' => $edu['institution'] ?? '',
            'qualification' => $edu['qualification'] ?? '',
            'year_completed' => $edu['year_completed'] ?? '',
        ])->values()->all();
@endphp

@extends('onboarding.layout', ['step' => $step, 'totalSteps' => $totalSteps, 'title' => 'Skills, Experience & Education'])

@section('content')
    <form method="POST" action="{{ route('candidate.onboarding.store', ['step' => $step]) }}">
        @csrf

        <div class="space-y-8">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Skills</h3>
                <div x-data="{
                    skills: {{ \Illuminate\Support\Js::from($skillsInitial) }},
                    addSkill() { this.skills.push({ name: '', level: 3 }); },
                    removeSkill(index) { if (this.skills.length > 1) this.skills.splice(index, 1); }
                }">
                    <div class="space-y-3">
                        <template x-for="(skill, index) in skills" :key="index">
                            <div class="flex gap-3">
                                <input type="text" :name="`skills[${index}][name]`" x-model="skill.name" placeholder="Skill name"
                                       class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <select :name="`skills[${index}][level]`" x-model.number="skill.level" class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                <button type="button" x-on:click="removeSkill(index)" x-show="skills.length > 1"
                                        class="shrink-0 text-gray-400 hover:text-red-600 transition-colors" aria-label="Remove skill">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" x-on:click="addSkill()" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700">+ Add Skill</button>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Work Experience</h3>
                <div x-data="{
                    experiences: {{ \Illuminate\Support\Js::from($experiencesInitial) }},
                    addExperience() { this.experiences.push({ company: '', role: '', duration: '', description: '' }); },
                    removeExperience(index) { if (this.experiences.length > 1) this.experiences.splice(index, 1); }
                }">
                    <div class="space-y-4">
                        <template x-for="(exp, index) in experiences" :key="index">
                            <div class="p-4 border border-gray-200 rounded-lg relative">
                                <button type="button" x-on:click="removeExperience(index)" x-show="experiences.length > 1"
                                        class="absolute top-2 right-2 text-gray-400 hover:text-red-600 transition-colors" aria-label="Remove experience">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <input type="text" :name="`experiences[${index}][company]`" x-model="exp.company" placeholder="Company"
                                           class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" :name="`experiences[${index}][role]`" x-model="exp.role" placeholder="Role"
                                           class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="mb-3">
                                    <input type="text" :name="`experiences[${index}][duration]`" x-model="exp.duration" placeholder="Duration (e.g. 2020-2023)"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <textarea :name="`experiences[${index}][description]`" x-model="exp.description" placeholder="Description"
                                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="2"></textarea>
                            </div>
                        </template>
                    </div>
                    <button type="button" x-on:click="addExperience()" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700">+ Add Experience</button>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Education</h3>
                <div x-data="{
                    education: {{ \Illuminate\Support\Js::from($educationInitial) }},
                    addEducation() { this.education.push({ institution: '', qualification: '', year_completed: '' }); },
                    removeEducation(index) { if (this.education.length > 1) this.education.splice(index, 1); }
                }">
                    <div class="space-y-3">
                        <template x-for="(edu, index) in education" :key="index">
                            <div class="flex gap-3 items-center">
                                <input type="text" :name="`education[${index}][institution]`" x-model="edu.institution" placeholder="Institution"
                                       class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <input type="text" :name="`education[${index}][qualification]`" x-model="edu.qualification" placeholder="Qualification"
                                       class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <input type="number" :name="`education[${index}][year_completed]`" x-model="edu.year_completed" placeholder="Year"
                                       class="w-28 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="button" x-on:click="removeEducation(index)" x-show="education.length > 1"
                                        class="shrink-0 text-gray-400 hover:text-red-600 transition-colors" aria-label="Remove education">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" x-on:click="addEducation()" class="mt-3 text-sm text-indigo-600 hover:text-indigo-700">+ Add Education</button>
                </div>
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
