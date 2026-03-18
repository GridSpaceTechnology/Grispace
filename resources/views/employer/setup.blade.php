@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-900">Set Up Your Company Profile</h1>
            <p class="mt-2 text-slate-600">This profile will be visible to candidates viewing your job postings.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('employer.setup') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Required Information</h2>
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Company Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $company?->name) }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="industry" class="block text-sm font-medium text-slate-700 mb-1">Industry *</label>
                            <select name="industry" id="industry" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary @error('industry') border-red-500 @enderror">
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
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary @error('company_size') border-red-500 @enderror">
                                <option value="">Select size</option>
                                <option value="1-10" {{ old('company_size', $company?->company_size) == '1-10' ? 'selected' : '' }}>1–10 employees</option>
                                <option value="10-50" {{ old('company_size', $company?->company_size) == '10-50' ? 'selected' : '' }}>10–50 employees</option>
                                <option value="50-200" {{ old('company_size', $company?->company_size) == '50-200' ? 'selected' : '' }}>50–200 employees</option>
                                <option value="200+" {{ old('company_size', $company?->company_size) == '200+' ? 'selected' : '' }}>200+ employees</option>
                            </select>
                            @error('company_size')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-slate-700 mb-1">City/Location *</label>
                            <input type="text" name="location" id="location" value="{{ old('location', $company?->location) }}" required placeholder="e.g. San Francisco"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary @error('location') border-red-500 @enderror">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location_country" class="block text-sm font-medium text-slate-700 mb-1">Country *</label>
                            <input type="text" name="location_country" id="location_country" value="{{ old('location_country', $company?->location_country) }}" required placeholder="e.g. United States"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary @error('location_country') border-red-500 @enderror">
                            @error('location_country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="website" class="block text-sm font-medium text-slate-700 mb-1">Company Website *</label>
                        <input type="url" name="website" id="website" value="{{ old('website', $company?->website) }}" required placeholder="https://example.com"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary @error('website') border-red-500 @enderror">
                        @error('website')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Optional Information</h2>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $company?->phone_number) }}" placeholder="+1 (555) 123-4567"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">
                        </div>

                        <div>
                            <label for="logo" class="block text-sm font-medium text-slate-700 mb-1">Company Logo</label>
                            <div class="flex items-center gap-4">
                                @if($company?->logo_url)
                                    <img src="{{ asset('storage/' . $company->logo_url) }}" alt="Current logo" class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                                @endif
                                <input type="file" name="logo" id="logo" accept="image/*"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:transition-colors file:cursor-pointer">
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Upload your company logo (optional). Max 2MB.</p>
                            @error('logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Company Description</label>
                        <textarea name="description" id="description" rows="4" placeholder="Tell candidates about your company..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">{{ old('description', $company?->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="work_model" class="block text-sm font-medium text-slate-700 mb-1">Work Model *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="work_model" value="remote" {{ old('work_model', $company?->work_model ?? 'onsite') == 'remote' ? 'checked' : '' }}
                                    class="w-4 h-4 text-brand-primary border-slate-300 focus:ring-brand-primary">
                                <span class="text-sm text-slate-700">Remote</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="work_model" value="hybrid" {{ old('work_model', $company?->work_model ?? 'onsite') == 'hybrid' ? 'checked' : '' }}
                                    class="w-4 h-4 text-brand-primary border-slate-300 focus:ring-brand-primary">
                                <span class="text-sm text-slate-700">Hybrid</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="work_model" value="onsite" {{ old('work_model', $company?->work_model ?? 'onsite') == 'onsite' ? 'checked' : '' }}
                                    class="w-4 h-4 text-brand-primary border-slate-300 focus:ring-brand-primary">
                                <span class="text-sm text-slate-700">Onsite</span>
                            </label>
                        </div>
                        @error('work_model')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="culture_description" class="block text-sm font-medium text-slate-700 mb-1">Culture Description</label>
                        <textarea name="culture_description" id="culture_description" rows="3" placeholder="Describe your company culture..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">{{ old('culture_description', $company?->culture_description) }}</textarea>
                        @error('culture_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-slate-700 mb-1">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $company?->linkedin_url) }}" placeholder="https://linkedin.com/company/..."
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">
                        </div>

                        <div>
                            <label for="instagram_url" class="block text-sm font-medium text-slate-700 mb-1">Instagram URL</label>
                            <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $company?->instagram_url) }}" placeholder="https://instagram.com/..."
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">
                        </div>

                        <div>
                            <label for="twitter_url" class="block text-sm font-medium text-slate-700 mb-1">Twitter/X URL</label>
                            <input type="url" name="twitter_url" id="twitter_url" value="{{ old('twitter_url', $company?->twitter_url) }}" placeholder="https://x.com/..."
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-8 py-3 bg-brand-primary text-white font-medium rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Complete Setup
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
