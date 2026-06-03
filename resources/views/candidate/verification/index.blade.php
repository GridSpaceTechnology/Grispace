@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Verification Center</h1>
            <p class="mt-2 text-gray-600">Verify your information to build trust with employers and improve your visibility.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-center">
                    <x-trust-score :score="$trustScore->score" :level="$trustScore->level" size="md" class="mx-auto"/>
                    <div class="mt-4">
                        <x-verification-progress :score="$trustScore->score" :level="$trustScore->level"/>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Badges</h2>
                    <div class="flex flex-wrap gap-2">
                        <x-verification-badge type="email" :status="$emailVerified ? 'verified' : 'pending'"/>
                        <x-verification-badge type="phone" :status="$phoneVerified ? 'verified' : 'pending'"/>
                        @foreach($verificationTypes as $type)
                            @php
                                $existing = $existingVerifications[$type->id] ?? null;
                                $status = $existing ? $existing->status : 'pending';
                            @endphp
                            <x-verification-badge :type="$type->slug" :status="$status"/>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Verification Summary</h2>
                    <p class="text-sm text-gray-500 mb-4">Track the status of your verifications</p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Email</p>
                                    <p class="text-xs text-gray-500">Automatic via registration</p>
                                </div>
                            </div>
                            @if($emailVerified)
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Verified</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs font-medium rounded-full">Not Verified</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Phone</p>
                                    <p class="text-xs text-gray-500">Verify via OTP</p>
                                </div>
                            </div>
                            @if($phoneVerified)
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Verified</span>
                            @else
                                <button onclick="document.getElementById('phoneVerificationModal').classList.remove('hidden')"
                                    class="px-3 py-1 bg-brand-primary text-white text-xs font-medium rounded-lg hover:bg-brand-primary-hover transition-colors">
                                    Verify Now
                                </button>
                            @endif
                        </div>

                        @foreach($verificationTypes as $type)
                            @php
                                $existing = $existingVerifications[$type->id] ?? null;
                                $status = $existing ? $existing->status : 'not_submitted';
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                                        @if($status === 'approved') bg-green-100
                                        @elseif(in_array($status, ['pending', 'under_review'])) bg-amber-100
                                        @elseif($status === 'rejected') bg-red-100
                                        @else bg-gray-100
                                        @endif">
                                        <svg class="w-4 h-4
                                            @if($status === 'approved') text-green-600
                                            @elseif(in_array($status, ['pending', 'under_review'])) text-amber-600
                                            @elseif($status === 'rejected') text-red-600
                                            @else text-gray-400
                                            @endif"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($status === 'approved')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @elseif($status === 'rejected')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364a9 9 0 11-12.728-12.728 9 9 0 0112.728 12.728z"/>
                                            @endif
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $type->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $type->description }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($status === 'approved')
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Approved</span>
                                    @elseif($status === 'under_review')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">Under Review</span>
                                    @elseif($status === 'pending')
                                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">Pending</span>
                                    @elseif($status === 'rejected')
                                        <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">Rejected</span>
                                        <button onclick="document.getElementById('uploadModal{{ $type->id }}').classList.remove('hidden')"
                                            class="px-3 py-1 bg-brand-primary text-white text-xs font-medium rounded-lg hover:bg-brand-primary-hover transition-colors">
                                            Resubmit
                                        </button>
                                    @else
                                        <button onclick="document.getElementById('uploadModal{{ $type->id }}').classList.remove('hidden')"
                                            class="px-3 py-1 bg-brand-primary text-white text-xs font-medium rounded-lg hover:bg-brand-primary-hover transition-colors">
                                            Upload
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($existingVerifications->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Submitted Documents</h2>
                        <div class="space-y-4">
                            @foreach($existingVerifications as $verification)
                                @if($verification->documents->isNotEmpty())
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 mb-2">{{ $verification->verificationType->name }}</h3>
                                        <div class="space-y-2">
                                            @foreach($verification->documents as $doc)
                                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                    <span class="text-sm text-gray-600">{{ $doc->document_name }}</span>
                                                    <a href="{{ $doc->url }}" target="_blank" class="text-brand-primary text-sm hover:underline">View</a>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($verification->notes)
                                            <p class="mt-2 text-sm text-gray-500 italic">Notes: {{ $verification->notes }}</p>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="phoneVerificationModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Verify Phone Number</h3>
            <button onclick="this.closest('#phoneVerificationModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        @if(!session('phone_otp_display'))
            <form method="POST" action="{{ route('candidate.verification.phone') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" required
                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        placeholder="+1 (555) 000-0000">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Send OTP
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('candidate.verification.phone.otp') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enter OTP</label>
                    <input type="text" name="otp" required maxlength="6"
                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-center text-2xl tracking-widest"
                        placeholder="000000">
                    <p class="mt-2 text-xs text-gray-500">Enter the 6-digit code sent to your phone.</p>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Verify OTP
                </button>
            </form>
            <form method="POST" action="{{ route('candidate.verification.phone') }}" class="mt-2">
                @csrf
                <input type="hidden" name="phone_number" value="{{ auth()->user()->phone_number ?? '' }}">
                <button type="submit" class="w-full text-sm text-brand-primary hover:underline text-center">Resend OTP</button>
            </form>
        @endif
    </div>
</div>

@foreach($verificationTypes as $type)
    @php
        $existing = $existingVerifications[$type->id] ?? null;
        $show = !$existing || $existing->status === 'rejected';
    @endphp
    @if($show)
    <div id="uploadModal{{ $type->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Upload {{ $type->name }} Documents</h3>
                <button onclick="this.closest('#uploadModal{{ $type->id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('candidate.verification.submit', $type) }}" enctype="multipart/form-data">
                @csrf
                <div id="documentsContainer{{ $type->id }}">
                    <div class="document-entry mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Name</label>
                        <input type="text" name="document_names[]" required
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary mb-2"
                            placeholder="e.g. Passport, Degree Certificate">
                        <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
                        <input type="file" name="documents[]" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-primary file:text-white hover:file:bg-brand-primary-hover">
                        <p class="mt-1 text-xs text-gray-500">Accepted: PDF, JPG, PNG, DOC (max 10MB)</p>
                    </div>
                </div>
                <button type="button" onclick="addDocument({{ $type->id }})"
                    class="text-sm text-brand-primary hover:underline mb-4 block">
                    + Add Another Document
                </button>
                <button type="submit" class="w-full px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                    Submit for Review
                </button>
            </form>
        </div>
    </div>
    @endif
@endforeach

<script>
    function addDocument(typeId) {
        const container = document.getElementById('documentsContainer' + typeId);
        const entry = document.querySelector('.document-entry').cloneNode(true);
        entry.querySelector('input[type="text"]').value = '';
        entry.querySelector('input[type="file"]').value = '';
        container.appendChild(entry);
    }
</script>
@endsection
