@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Review Verification</h1>
            <p class="text-gray-600 mt-1">{{ $verification->verificationType->name }} for {{ $verification->candidate?->name }}</p>
        </div>
        <a href="{{ route('admin.verifications.index') }}" class="text-brand-primary hover:underline">Back to All</a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Candidate Information</h2>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-brand-primary/10 rounded-full flex items-center justify-center">
                        <span class="text-brand-primary font-semibold">{{ substr($verification->candidate->name ?? '?', 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $verification->candidate?->name ?? 'Deleted User' }}</p>
                        <p class="text-sm text-gray-500">{{ $verification->candidate?->email ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Verification Type:</span>
                        <span class="font-medium ml-2">{{ $verification->verificationType->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Status:</span>
                        <span class="inline-flex ml-2 px-2 py-0.5 text-xs font-medium rounded-full
                            @if($verification->status === 'approved') bg-green-100 text-green-700
                            @elseif($verification->status === 'rejected') bg-red-100 text-red-700
                            @elseif($verification->status === 'under_review') bg-blue-100 text-blue-700
                            @else bg-amber-100 text-amber-700
                            @endif">
                            {{ ucfirst($verification->status) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Submitted:</span>
                        <span class="font-medium ml-2">{{ $verification->submitted_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Reviewed by:</span>
                        <span class="font-medium ml-2">{{ $verification->reviewer?->name ?? 'Not yet reviewed' }}</span>
                    </div>
                </div>
            </div>

            @if($verification->documents->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Documents</h2>
                    <div class="space-y-3">
                        @foreach($verification->documents as $doc)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-brand-primary/10 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $doc->document_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $doc->document_type }} &middot; {{ $doc->uploaded_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <a href="{{ $doc->url }}" target="_blank"
                                    class="px-3 py-1 border border-slate-300 text-slate-700 text-xs rounded-lg hover:bg-slate-50 transition-colors">
                                    View
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-center">
                    <p class="text-gray-500">No documents uploaded yet.</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>

                @if($verification->status !== 'approved')
                    <form method="POST" action="{{ route('admin.verifications.approve', $verification) }}" class="mb-4">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700 mb-1">Review Notes (optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-green-500 focus:ring-green-500 mb-3 text-sm" placeholder="Add any notes..."></textarea>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            Approve Verification
                        </button>
                    </form>
                @endif

                @if($verification->status !== 'rejected')
                    <form method="POST" action="{{ route('admin.verifications.reject', $verification) }}" class="mb-4">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason <span class="text-red-500">*</span></label>
                        <textarea name="notes" rows="2" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 mb-3 text-sm" placeholder="Explain why the verification was rejected..."></textarea>
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                            onclick="return confirm('Are you sure you want to reject this verification?')">
                            Reject Verification
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.verifications.request-info', $verification) }}">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 mb-1">Request More Info <span class="text-red-500">*</span></label>
                    <textarea name="notes" rows="2" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 mb-3 text-sm" placeholder="What additional information is needed?"></textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                        Request More Information
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Status</h2>
                <div class="space-y-3">
                    @if($verification->notes)
                        <div>
                            <p class="text-sm font-medium text-gray-700">Notes</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $verification->notes }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-700">Status History</p>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span class="text-gray-600">Submitted {{ $verification->submitted_at?->diffForHumans() }}</span>
                            </div>
                            @if($verification->verified_at)
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <span class="text-gray-600">Reviewed {{ $verification->verified_at->diffForHumans() }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
