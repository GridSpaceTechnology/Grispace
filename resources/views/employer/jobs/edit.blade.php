@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Job</h1>
            <p class="text-gray-600 mt-1">Update your job posting</p>
        </div>

        <form method="POST" action="{{ route('employer.jobs.update', ['job' => $job->id]) }}">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Job Title *</label>
                            <input type="text" name="title" id="title" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('title', $job->title) }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                            <input type="text" name="role" id="role" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('role', $job->role) }}">
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <input type="text" name="department" id="department"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('department', $job->role) }}">
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <input type="text" name="location" id="location"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('location', $job->location) }}">
                        </div>

                        <div>
                            <label for="location_country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input type="text" name="location_country" id="location_country"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('location_country', $job->location_country) }}">
                        </div>

                        <div>
                            <label for="experience_level" class="block text-sm font-medium text-gray-700 mb-1">Experience Level</label>
                            <select name="experience_level" id="experience_level"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="any" {{ old('experience_level', $job->experience_level) === 'any' ? 'selected' : '' }}>Any</option>
                                <option value="entry" {{ old('experience_level', $job->experience_level) === 'entry' ? 'selected' : '' }}>Entry Level</option>
                                <option value="junior" {{ old('experience_level', $job->experience_level) === 'junior' ? 'selected' : '' }}>Junior</option>
                                <option value="mid" {{ old('experience_level', $job->experience_level) === 'mid' ? 'selected' : '' }}>Mid Level</option>
                                <option value="senior" {{ old('experience_level', $job->experience_level) === 'senior' ? 'selected' : '' }}>Senior</option>
                                <option value="lead" {{ old('experience_level', $job->experience_level) === 'lead' ? 'selected' : '' }}>Lead</option>
                                <option value="principal" {{ old('experience_level', $job->experience_level) === 'principal' ? 'selected' : '' }}>Principal</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Employment Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-1">Employment Type *</label>
                            <select name="employment_type" id="employment_type" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select type</option>
                                <option value="full_time" {{ old('employment_type', $job->employment_type) === 'full_time' ? 'selected' : '' }}>Full-time</option>
                                <option value="part_time" {{ old('employment_type', $job->employment_type) === 'part_time' ? 'selected' : '' }}>Part-time</option>
                                <option value="contract" {{ old('employment_type', $job->employment_type) === 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="freelance" {{ old('employment_type', $job->employment_type) === 'freelance' ? 'selected' : '' }}>Freelance</option>
                                <option value="internship" {{ old('employment_type', $job->employment_type) === 'internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                            @error('employment_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="work_preference" class="block text-sm font-medium text-gray-700 mb-1">Work Mode *</label>
                            <select name="work_preference" id="work_preference" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select preference</option>
                                <option value="remote" {{ old('work_preference', $job->work_preference) === 'remote' ? 'selected' : '' }}>Remote</option>
                                <option value="hybrid" {{ old('work_preference', $job->work_preference) === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                <option value="onsite" {{ old('work_preference', $job->work_preference) === 'onsite' ? 'selected' : '' }}>On-site</option>
                                <option value="flexible" {{ old('work_preference', $job->work_preference) === 'flexible' ? 'selected' : '' }}>Flexible</option>
                            </select>
                            @error('work_preference')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="salary_min" class="block text-sm font-medium text-gray-700 mb-1">Minimum Salary ($)</label>
                            <input type="number" name="salary_min" id="salary_min" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('salary_min', $job->salary_min) }}">
                        </div>

                        <div>
                            <label for="salary_max" class="block text-sm font-medium text-gray-700 mb-1">Maximum Salary ($)</label>
                            <input type="number" name="salary_max" id="salary_max" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('salary_max', $job->salary_max) }}">
                        </div>

                        <div>
                            <label for="minimum_experience" class="block text-sm font-medium text-gray-700 mb-1">Minimum Experience (years)</label>
                            <input type="number" name="minimum_experience" id="minimum_experience" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('minimum_experience', $job->minimum_experience) }}">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="draft" {{ old('status', $job->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="open" {{ old('status', $job->status) === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="paused" {{ old('status', $job->status) === 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="closed" {{ old('status', $job->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                                <option value="filled" {{ old('status', $job->status) === 'filled' ? 'selected' : '' }}>Filled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Description</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Job Description *</label>
                            <textarea name="description" id="description" rows="5" required
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $job->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Required Skills</h2>
                    
                    <div>
                        <label for="required_skills" class="block text-sm font-medium text-gray-700 mb-1">Skills (comma-separated)</label>
                        <input type="text" name="required_skills" id="required_skills"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('required_skills', implode(', ', $job->required_skills_json ?? [])) }}">
                        <p class="mt-1 text-sm text-gray-500">Enter skills separated by commas</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Candidate Preferences</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="temperament_preference" class="block text-sm font-medium text-gray-700 mb-1">Preferred Temperament</label>
                            <select name="temperament_preference" id="temperament_preference"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Any</option>
                                <option value="analytical" {{ old('temperament_preference', $job->temperament_preference) === 'analytical' ? 'selected' : '' }}>Analytical</option>
                                <option value="expressive" {{ old('temperament_preference', $job->temperament_preference) === 'expressive' ? 'selected' : '' }}>Expressive</option>
                                <option value="amiable" {{ old('temperament_preference', $job->temperament_preference) === 'amiable' ? 'selected' : '' }}>Amiable</option>
                                <option value="driver" {{ old('temperament_preference', $job->temperament_preference) === 'driver' ? 'selected' : '' }}>Driver</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between gap-4">
                    <form method="POST" action="{{ route('employer.jobs.destroy', ['job' => $job->id]) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this job?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                            Delete Job
                        </button>
                    </form>
                    
                    <div class="flex gap-4">
                        <a href="{{ route('employer.jobs.show', ['job' => $job->id]) }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Update Job
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
