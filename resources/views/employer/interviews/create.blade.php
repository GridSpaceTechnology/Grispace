@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Schedule Interview</h1>
            <p class="text-gray-600 mt-1">Set up an interview with a candidate</p>
        </div>

        <form method="POST" action="{{ route('employer.interviews.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="space-y-4">
                    <div>
                        <label for="candidate_id" class="block text-sm font-medium text-gray-700 mb-1">Candidate *</label>
                        <select name="candidate_id" id="candidate_id" required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a candidate</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate->id }}" {{ old('candidate_id', $selectedCandidateId) == $candidate->id ? 'selected' : '' }}>
                                    {{ $candidate->name }}
                                    @if($candidate->candidateProfile?->desired_role)
                                        - {{ $candidate->candidateProfile->desired_role }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('candidate_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="job_id" class="block text-sm font-medium text-gray-700 mb-1">Job (optional)</label>
                        <select name="job_id" id="job_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a job</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" {{ old('job_id', $selectedJobId) == $job->id ? 'selected' : '' }}>
                                    {{ $job->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('job_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="interview_type" class="block text-sm font-medium text-gray-700 mb-1">Interview Type *</label>
                        <select name="interview_type" id="interview_type" required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select type</option>
                            <option value="phone" {{ old('interview_type') === 'phone' ? 'selected' : '' }}>Phone Screen</option>
                            <option value="video" {{ old('interview_type') === 'video' ? 'selected' : '' }}>Video Call</option>
                            <option value="onsite" {{ old('interview_type') === 'onsite' ? 'selected' : '' }}>On-site</option>
                            <option value="technical" {{ old('interview_type') === 'technical' ? 'selected' : '' }}>Technical</option>
                            <option value="behavioral" {{ old('interview_type') === 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                            <option value="panel" {{ old('interview_type') === 'panel' ? 'selected' : '' }}>Panel</option>
                        </select>
                        @error('interview_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">Date & Time *</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               value="{{ old('scheduled_at') }}">
                        @error('scheduled_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Add any notes about this interview...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('employer.interviews.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Schedule Interview
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
