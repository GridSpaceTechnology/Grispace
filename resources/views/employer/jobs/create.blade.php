@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Post a New Job</h1>
            <p class="text-gray-600 mt-1">Fill in the details to attract top talent</p>
        </div>

        <form method="POST" action="{{ route('employer.jobs.store') }}" id="job-form">
            @csrf

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Job Title *</label>
                            <input type="text" name="title" id="title" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="e.g. Senior Software Engineer">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                            <input type="text" name="role" id="role" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="e.g. Engineering">
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <input type="text" name="location" id="location"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="e.g. San Francisco, CA or Remote">
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
                                <option value="full_time">Full-time</option>
                                <option value="part_time">Part-time</option>
                                <option value="contract">Contract</option>
                                <option value="freelance">Freelance</option>
                                <option value="internship">Internship</option>
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
                                <option value="remote">Remote</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="onsite">On-site</option>
                                <option value="flexible">Flexible</option>
                            </select>
                            @error('work_preference')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="salary_currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select name="salary_currency" id="salary_currency"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(config('currencies.currencies') as $code => $currency)
                                    <option value="{{ $code }}" @if(old('salary_currency', 'NGN') === $code) selected @endif>
                                        {{ $currency['symbol'] }} – {{ $code }} ({{ $currency['name'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="salary_min" class="block text-sm font-medium text-gray-700 mb-1">Minimum Salary</label>
                            <input type="number" name="salary_min" id="salary_min" min="0"
                                   value="{{ old('salary_min') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="50000">
                            @error('salary_max')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="salary_max" class="block text-sm font-medium text-gray-700 mb-1">Maximum Salary</label>
                            <input type="number" name="salary_max" id="salary_max" min="0"
                                   value="{{ old('salary_max') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="80000">
                        </div>

                        <div>
                            <label for="salary_period" class="block text-sm font-medium text-gray-700 mb-1">Salary Period</label>
                            <select name="salary_period" id="salary_period"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Not specified</option>
                                @foreach(config('currencies.salary_periods') as $key => $period)
                                    <option value="{{ $key }}" @if(old('salary_period') === $key) selected @endif>{{ $period }}</option>
                                @endforeach
                            </select>
                            @error('salary_period')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="minimum_experience" class="block text-sm font-medium text-gray-700 mb-1">Minimum Experience (years)</label>
                            <input type="number" name="minimum_experience" id="minimum_experience" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="3">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Description</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Job Description *</label>
                            <textarea name="description" id="description" rows="5" required
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Describe the role, responsibilities, and what makes this opportunity exciting..."></textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="responsibilities" class="block text-sm font-medium text-gray-700 mb-1">Key Responsibilities</label>
                            <textarea name="responsibilities" id="responsibilities" rows="4"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="List the main responsibilities...">{{ old('responsibilities') }}</textarea>
                            @error('responsibilities')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="requirements" class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                            <textarea name="requirements" id="requirements" rows="4"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Qualifications, experience, and other requirements...">{{ old('requirements') }}</textarea>
                            @error('requirements')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="benefits" class="block text-sm font-medium text-gray-700 mb-1">Benefits</label>
                            <textarea name="benefits" id="benefits" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Health insurance, flexible hours, remote stipend...">{{ old('benefits') }}</textarea>
                            @error('benefits')
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
                               placeholder="e.g. JavaScript, React, Node.js, Python">
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
                                <option value="analytical">Analytical</option>
                                <option value="expressive">Expressive</option>
                                <option value="amiable">Amiable</option>
                                <option value="driver">Driver</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('employer.dashboard') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Post Job
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
