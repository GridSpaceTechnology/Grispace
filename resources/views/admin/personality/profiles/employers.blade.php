@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Employer Culture Profiles</h1>
    <p class="text-gray-600 mt-1">View completed employer culture profiles</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Work Environment</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Communication</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leadership</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company Pace</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Independence</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($employerProfiles as $profile)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $profile->user?->name ?? 'Unknown' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->work_environment }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->communication_style }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->leadership_style }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->company_pace }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->independence_level }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $employerProfiles->links() }}
</div>
@endsection
