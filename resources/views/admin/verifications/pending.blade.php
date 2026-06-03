@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pending Reviews</h1>
            <p class="text-gray-600 mt-1">Verification requests awaiting review</p>
        </div>
        <a href="{{ route('admin.verifications.index') }}" class="text-brand-primary hover:underline">Back to All</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
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
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $verification->verificationType->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $verification->submitted_at?->diffForHumans() ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.verifications.show', $verification) }}"
                                    class="px-3 py-1 bg-brand-primary text-white text-xs font-medium rounded-lg hover:bg-brand-primary-hover transition-colors">
                                    Review Now
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No pending verifications.</td>
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
