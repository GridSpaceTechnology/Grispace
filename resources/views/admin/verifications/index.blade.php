@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Verification Center</h1>
            <p class="text-gray-600 mt-1">Review and manage candidate verification requests</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.verifications.stats') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                Analytics
            </a>
            <a href="{{ route('admin.verifications.pending') }}" class="px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                Review Pending ({{ $stats['pending'] }})
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500">Total</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
            <p class="text-sm text-gray-500">Pending</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['under_review'] }}</p>
            <p class="text-sm text-gray-500">Under Review</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
            <p class="text-sm text-gray-500">Approved</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
            <p class="text-sm text-gray-500">Rejected</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <h2 class="font-semibold text-gray-900">All Verification Requests</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reviewed By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($verifications as $verification)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                        <span class="text-brand-primary text-xs font-semibold">{{ substr($verification->candidate->name ?? '?', 0, 1) }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $verification->candidate?->name ?? 'Deleted User' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-700">{{ $verification->verificationType->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                    @if($verification->status === 'approved') bg-green-100 text-green-700
                                    @elseif($verification->status === 'rejected') bg-red-100 text-red-700
                                    @elseif($verification->status === 'under_review') bg-blue-100 text-blue-700
                                    @else bg-amber-100 text-amber-700
                                    @endif">
                                    {{ ucfirst($verification->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $verification->submitted_at?->diffForHumans() ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $verification->reviewer?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.verifications.show', $verification) }}"
                                    class="text-brand-primary text-sm hover:underline">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No verification requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $verifications->links() }}
        </div>
    </div>
</div>
@endsection
