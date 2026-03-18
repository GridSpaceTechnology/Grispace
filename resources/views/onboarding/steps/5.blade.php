@php
    $assessment = $user->candidateAssessment;
    $temperaments = ['analytical', 'driver', 'expressive', 'amiable'];
    $descriptions = [
        'analytical' => 'Problem-solver, detail-oriented, methodical',
        'driver' => 'Results-oriented, decisive, assertive',
        'expressive' => 'Creative, enthusiastic, persuasive',
        'amiable' => 'Supportive, cooperative, relationship-focused'
    ];
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

                    <h2 class="text-xl font-semibold mb-6">Temperament Selection</h2>
                    <p class="text-gray-600 mb-6">Select the temperament that best describes you.</p>

                    <div class="space-y-4">
                        @foreach($temperaments as $type)
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('temperament_type', $assessment?->temperament_type) == $type ? 'border-indigo-500 bg-indigo-50' : '' }}">
                            <input type="radio" name="temperament_type" value="{{ $type }}"
                                   {{ old('temperament_type', $assessment?->temperament_type) == $type ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 border-gray-300">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">{{ ucfirst($type) }}</span>
                                <span class="block text-sm text-gray-500">{{ $descriptions[$type] }}</span>
                            </div>
                        </label>
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
