@extends('layouts.app')

@section('content')
@php($user = auth()->user())
<div class="min-h-screen bg-slate-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Profile</h1>
            <p class="mt-2 text-slate-600">Update your information.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="mt-8 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            @include('profile.partials.update-password-form')
        </div>

        <form method="POST" action="{{ route('employer.profile.update') }}" enctype="multipart/form-data" class="space-y-8 mt-8">
            @csrf
            @method('PATCH')

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Basic Information</h2>
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Company Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $company?->name) }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="industry" class="block text-sm font-medium text-slate-700 mb-1">Industry *</label>
                            <select name="industry" id="industry" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('industry') border-red-500 @enderror">
                                <option value="">Select an industry</option>
                                @foreach(['Technology', 'Healthcare', 'Finance', 'Education', 'Retail', 'Manufacturing', 'Media & Entertainment', 'Real Estate', 'Transportation', 'Energy', 'Hospitality', 'Construction', 'Legal', 'Non-profit', 'Government', 'Other'] as $ind)
                                    <option value="{{ $ind }}" {{ old('industry', $company?->industry) == $ind ? 'selected' : '' }}>{{ $ind }}</option>
                                @endforeach
                            </select>
                            @error('industry')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company_size" class="block text-sm font-medium text-slate-700 mb-1">Company Size *</label>
                            <select name="company_size" id="company_size" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('company_size') border-red-500 @enderror">
                                <option value="">Select size</option>
                                @foreach(['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'] as $size)
                                    <option value="{{ $size }}" {{ old('company_size', $company?->company_size) == $size ? 'selected' : '' }}>{{ $size }} employees</option>
                                @endforeach
                            </select>
                            @error('company_size')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-slate-700 mb-1">Location *</label>
                            <input type="text" name="location" id="location" value="{{ old('location', $company?->location) }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('location') border-red-500 @enderror">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location_country" class="block text-sm font-medium text-slate-700 mb-1">Country *</label>
                            <input type="text" name="location_country" id="location_country" value="{{ old('location_country', $company?->location_country) }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('location_country') border-red-500 @enderror">
                            @error('location_country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Company Details</h2>
                
                <div class="space-y-6">
                    <div>
                        <label for="website" class="block text-sm font-medium text-slate-700 mb-1">Website</label>
                        <input type="url" name="website" id="website" value="{{ old('website', $company?->website) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('website') border-red-500 @enderror">
                        @error('website')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $company?->phone_number) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('phone_number') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Company Description</label>
                        <textarea name="description" id="description" rows="4"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $company?->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Work Model</h2>
                
                <div>
                    <label for="work_model" class="block text-sm font-medium text-slate-700 mb-1">Work Model *</label>
                    <select name="work_model" id="work_model" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('work_model') border-red-500 @enderror">
                        <option value="">Select work model</option>
                        <option value="remote" {{ old('work_model', $company?->work_model) == 'remote' ? 'selected' : '' }}>Remote</option>
                        <option value="hybrid" {{ old('work_model', $company?->work_model) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        <option value="onsite" {{ old('work_model', $company?->work_model) == 'onsite' ? 'selected' : '' }}>On-site</option>
                    </select>
                    @error('work_model')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Candidate Messaging</h2>

                <label for="allow_candidate_messages" class="flex items-start justify-between gap-4 cursor-pointer">
                    <span>
                        <span class="block font-medium text-slate-700">Allow candidates to message you</span>
                        <span class="block mt-1 text-sm text-slate-500">When enabled, candidates can start conversations with you from your company profile and job listings. Existing conversations are not affected.</span>
                    </span>
                    <span class="relative inline-flex flex-shrink-0 items-center mt-0.5">
                        <input type="checkbox" name="allow_candidate_messages" id="allow_candidate_messages"
                               value="1"
                               class="peer sr-only"
                               @checked(old('allow_candidate_messages', $company?->allow_candidate_messages ?? true))>
                        <span class="h-6 w-11 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></span>
                        <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 translate-x-0 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                </label>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Social Links</h2>
                
                <div class="space-y-6">
                    <div>
                        <label for="linkedin_url" class="block text-sm font-medium text-slate-700 mb-1">LinkedIn</label>
                        <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $company?->linkedin_url) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('linkedin_url') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="instagram_url" class="block text-sm font-medium text-slate-700 mb-1">Instagram</label>
                        <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $company?->instagram_url) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('instagram_url') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="twitter_url" class="block text-sm font-medium text-slate-700 mb-1">Twitter/X</label>
                        <input type="url" name="twitter_url" id="twitter_url" value="{{ old('twitter_url', $company?->twitter_url) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('twitter_url') border-red-500 @enderror">
                    </div>
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