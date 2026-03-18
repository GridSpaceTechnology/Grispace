@php
    $assessment = $user->candidateAssessment;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Onboarding - Step {{ $step }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12">
        <div class="max-w-2xl mx-auto px-4">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 text-center">Candidate Onboarding</h1>
                <p class="text-gray-600 text-center mt-2">Step {{ $step }} of {{ $totalSteps }}</p>
            </div>

            <div class="mb-8">
                <div class="flex justify-between mb-2">
                    @for ($i = 1; $i <= $totalSteps; $i++)
                        <div class="flex-1 h-2 mx-1 rounded {{ $i <= $step ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                    @endfor
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-lg p-8">
                <form method="POST" action="{{ route('candidate.onboarding.store', ['step' => $step]) }}">
                    @csrf

                    <h2 class="text-xl font-semibold mb-6">Skill Assessment</h2>
                    <p class="text-gray-600 mb-6">Rate your overall skill level to help us match you with appropriate roles.</p>

                    <div class="space-y-6">
                        <div>
                            <label for="skill_score" class="block text-sm font-medium text-gray-700 mb-2">
                                Overall Skill Score: <span id="score-display">{{ old('skill_score', $assessment?->skill_score ?? 50) }}</span>/100
                            </label>
                            <input type="range" name="skill_score" id="skill_score" min="0" max="100" 
                                   value="{{ old('skill_score', $assessment?->skill_score ?? 50) }}"
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                   oninput="document.getElementById('score-display').textContent = this.value">
                            <div class="flex justify-between text-sm text-gray-500 mt-1">
                                <span>Beginner</span>
                                <span>Expert</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subskill Breakdown (Optional)</label>
                            <div id="subskills-container" class="space-y-2">
                                @foreach(old('subskill_breakdown', $assessment?->subskill_breakdown_json ?? [['name' => '', 'score' => 50]]) as $index => $subskill)
                                <div class="flex gap-3 subskill-row">
                                    <input type="text" name="subskill_breakdown[{{ $index }}][name]" placeholder="Subskill name"
                                           value="{{ $subskill['name'] ?? '' }}"
                                           class="flex-1 rounded-md border-gray-300 shadow-sm">
                                    <input type="number" name="subskill_breakdown[{{ $index }}][score]" placeholder="Score" min="0" max="100"
                                           value="{{ $subskill['score'] ?? 50 }}"
                                           class="w-24 rounded-md border-gray-300 shadow-sm">
                                </div>
                                @endforeach
                            </div>
                            <button type="button" onclick="addSubskillRow()" class="mt-3 text-sm text-indigo-600 hover:text-indigo-800">+ Add Subskill</button>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('candidate.onboarding.step', ['step' => $step - 1]) }}" 
                           class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Back
                        </a>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                            Next Step
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
