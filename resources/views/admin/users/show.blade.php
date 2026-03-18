@extends('layouts.admin')

@section('admin-content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">User Details</h1>
        <p class="text-gray-600 mt-1">View user information</p>
    </div>
    <a href="{{ route('admin.users') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Users
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
            </div>
            <div class="p-6">
                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-2xl font-semibold text-gray-600">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Name</label>
                                <p class="mt-1 text-gray-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email</label>
                                <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Role</label>
                                <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full 
                                    @if($user->role === 'admin') bg-purple-100 text-purple-800
                                    @elseif($user->role === 'employer') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Status</label>
                                <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full 
                                    @if($user->is_suspended) bg-red-100 text-red-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ $user->is_suspended ? 'Suspended' : 'Active' }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email Verified</label>
                                <p class="mt-1 text-gray-900">
                                    @if($user->email_verified_at)
                                        {{ $user->email_verified_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-red-500">Not verified</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Member Since</label>
                                <p class="mt-1 text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($user->role === 'candidate' && $user->candidateProfile)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Candidate Profile</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Headline</label>
                        <p class="mt-1 text-gray-900">{{ $user->candidateProfile->headline ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Location</label>
                        <p class="mt-1 text-gray-900">{{ $user->candidateProfile->location ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Years of Experience</label>
                        <p class="mt-1 text-gray-900">{{ $user->candidateProfile->years_of_experience ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Onboarding</label>
                        <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-semibold rounded-full 
                            @if($user->onboarding_completed) bg-green-100 text-green-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ $user->onboarding_completed ? 'Completed' : 'Pending' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user->role === 'employer' && $user->company)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Company Profile</h2>
                @if($user->company->is_verified)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Verified
                    </span>
                @endif
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Company Name</label>
                        <p class="mt-1 text-gray-900">{{ $user->company->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Industry</label>
                        <p class="mt-1 text-gray-900">{{ $user->company->industry ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Company Size</label>
                        <p class="mt-1 text-gray-900">{{ $user->company->company_size ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Website</label>
                        <p class="mt-1 text-gray-900">{{ $user->company->website ?? 'Not set' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
            </div>
            <div class="p-6 space-y-4">
                @if($user->role === 'employer' && $user->company)
                    <form method="POST" action="{{ route('admin.users.verify', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium rounded-lg transition-colors
                            @if($user->company->is_verified)
                                bg-red-100 text-red-700 hover:bg-red-200
                            @else
                                bg-green-100 text-green-700 hover:bg-green-200
                            @endif">
                            {{ $user->company->is_verified ? 'Unverify Employer' : 'Verify Employer' }}
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                        Delete User
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Statistics</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Posted Jobs</span>
                    <span class="font-semibold text-gray-900">{{ $user->postedJobs->count() }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Applications</span>
                    <span class="font-semibold text-gray-900">{{ $user->jobApplications->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
