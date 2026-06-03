@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Personality Assessment Analytics</h1>
    <p class="text-gray-600 mt-1">Track assessment completion and engagement</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Candidate Completion</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $completionPercentage }}%</p>
        <p class="text-sm text-gray-500 mt-1">{{ $completedAssessments }} of {{ $totalCandidates }} candidates</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Employer Culture</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $employerCompletionPercentage }}%</p>
        <p class="text-sm text-gray-500 mt-1">{{ $completedCulture }} of {{ $totalEmployers }} employers</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Total Questions</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalQuestions }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $activeQuestions }} active</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Total Answers</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalAnswers }}</p>
        <p class="text-sm text-gray-500 mt-1">across all questions</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Drop-off Analysis</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Step</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Answers</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Drop-off Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($dropOffData as $data)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $data['step'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">#{{ $data['question_id'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $data['answers_count'] }}</td>
                        <td class="px-4 py-3">
                            <span class="text-sm {{ $data['drop_off_rate'] > 20 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $data['drop_off_rate'] }}%
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
