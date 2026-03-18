@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('employer.jobs.pipeline', ['job' => $job->id]) }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Pipeline
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h1 class="text-2xl font-bold text-slate-900 mb-1">Schedule Interview</h1>
            <p class="text-slate-600 mb-6">Schedule an interview with {{ $candidate->name }}</p>

            <div class="bg-slate-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-slate-500 mb-1">Position</div>
                <div class="font-medium text-slate-900">{{ $job->title }}</div>
            </div>

            <form method="POST" action="{{ route('employer.applications.schedule-interview.store', ['application' => $application->id]) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="scheduled_date" class="block text-sm font-medium text-slate-700 mb-2">Interview Date</label>
                        <input type="date" name="scheduled_date" id="scheduled_date" 
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                            min="{{ date('Y-m-d') }}"
                            value="{{ old('scheduled_date') }}">
                        @error('scheduled_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="scheduled_time" class="block text-sm font-medium text-slate-700 mb-2">Interview Time</label>
                        <input type="time" name="scheduled_time" id="scheduled_time" 
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                            value="{{ old('scheduled_time') }}">
                        @error('scheduled_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="interview_type" class="block text-sm font-medium text-slate-700 mb-2">Interview Type</label>
                    <select name="interview_type" id="interview_type" 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">
                        <option value="video" {{ old('interview_type') === 'video' ? 'selected' : '' }}>Video Call</option>
                        <option value="physical" {{ old('interview_type') === 'physical' ? 'selected' : '' }}>In Person</option>
                        <option value="phone" {{ old('interview_type') === 'phone' ? 'selected' : '' }}>Phone Call</option>
                    </select>
                    @error('interview_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6" id="meeting_link_field">
                    <label for="meeting_link" class="block text-sm font-medium text-slate-700 mb-2">Meeting Link</label>
                    <input type="url" name="meeting_link" id="meeting_link" 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                        placeholder="https://zoom.us/j/..."
                        value="{{ old('meeting_link') }}">
                    @error('meeting_link')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6 hidden" id="location_field">
                    <label for="location" class="block text-sm font-medium text-slate-700 mb-2">Location</label>
                    <input type="text" name="location" id="location" 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                        placeholder="Office address"
                        value="{{ old('location') }}">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="3" 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                        placeholder="Any additional information for the candidate...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('employer.jobs.pipeline', ['job' => $job->id]) }}" 
                        class="px-6 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="px-6 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                        Schedule Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('interview_type').addEventListener('change', function() {
        const meetingLinkField = document.getElementById('meeting_link_field');
        const locationField = document.getElementById('location_field');
        
        if (this.value === 'video') {
            meetingLinkField.classList.remove('hidden');
            locationField.classList.add('hidden');
        } else if (this.value === 'physical') {
            meetingLinkField.classList.add('hidden');
            locationField.classList.remove('hidden');
        } else {
            meetingLinkField.classList.add('hidden');
            locationField.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection