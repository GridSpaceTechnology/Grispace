@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Candidate Personality Profiles</h1>
    <p class="text-gray-600 mt-1">View completed candidate assessment profiles</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Work Style</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Communication</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collaboration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leadership</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Temperament</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($candidateProfiles as $profile)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $profile->candidate?->name ?? 'Unknown' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->work_style }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->communication_style }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->collaboration_style }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->leadership_style }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                            {{ $profile->temperament_type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $profile->completed_at?->diffForHumans() ?? 'N/A' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $candidateProfiles->links() }}
</div>
@endsection
