@php
    $preferences = $user->candidatePreferences;
    $orgTypes = ['startup', 'mid-size', 'enterprise', 'non-profit', 'government'];
    $motivationDrivers = ['growth', 'impact', 'autonomy', 'collaboration', 'stability', 'creativity', 'leadership', 'learning'];
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

                    <h2 class="text-xl font-semibold mb-6">Organizational Preferences</h2>

                    <div class="space-y-6">
                        <div>
                            <label for="organizational_type" class="block text-sm font-medium text-gray-700 mb-2">Preferred Organization Type</label>
                            <select name="organizational_type" id="organizational_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select type</option>
                                @foreach($orgTypes as $type)
                                <option value="{{ $type }}" {{ old('organizational_type', $preferences?->organizational_type) == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Motivation Drivers (select all that apply)</label>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($motivationDrivers as $driver)
                                <label class="flex items-center">
                                    <input type="checkbox" name="motivation_drivers[]" value="{{ $driver }}"
                                           {{ in_array($driver, old('motivation_drivers', $preferences?->motivation_drivers_json ?? [])) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    <span class="ml-2 text-sm text-gray-700">{{ ucfirst($driver) }}</span>
                                </label>
                                @endforeach
                            </div>
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
