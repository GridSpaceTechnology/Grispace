@php
    $assessment = $user->candidateAssessment;
    $personalityTraits = ['openness', 'conscientiousness', 'extraversion', 'agreeableness', 'neuroticism'];
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

                    <h2 class="text-xl font-semibold mb-6">Personality Assessment</h2>
                    <p class="text-gray-600 mb-6">Rate your personality traits to help us find the right cultural fit.</p>

                    <div class="space-y-6">
                        @foreach($personalityTraits as $trait)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ ucfirst($trait) }}: <span id="{{ $trait }}-display">{{ old('personality_scores.' . $trait, $assessment?->personality_scores_json[$trait] ?? 50) }}</span>/100
                            </label>
                            <input type="range" name="personality_scores[{{ $trait }}]" 
                                   min="0" max="100"
                                   value="{{ old('personality_scores.' . $trait, $assessment?->personality_scores_json[$trait] ?? 50) }}"
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                   oninput="document.getElementById('{{ $trait }}-display').textContent = this.value">
                            <div class="flex justify-between text-sm text-gray-500 mt-1">
                                <span>Low</span>
                                <span>High</span>
                            </div>
                        </div>
                        @endforeach
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
