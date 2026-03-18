<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Onboarding' }} - Gridspace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-4">
                    <img src="{{ asset('logo.jpeg') }}" alt="Gridspace" class="h-8 w-auto">
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $title ?? 'Candidate Onboarding' }}</h1>
                <p class="text-gray-600 mt-1">Step {{ $step ?? 1 }} of {{ $totalSteps ?? 8 }}</p>
            </div>

            <x-progress-bar :value="$step ?? 1" :max="$totalSteps ?? 8" size="sm" color="indigo" class="mb-8" />

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                {{ $slot }}
            </div>

            @if(($step ?? 1) > 1)
            <div class="mt-6 text-center">
                <form action="{{ route('candidate.onboarding.skip') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
                        Skip onboarding
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
