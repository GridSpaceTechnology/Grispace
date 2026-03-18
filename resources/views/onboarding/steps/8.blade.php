@php
    $media = $user->candidateMedia;
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

                    <h2 class="text-xl font-semibold mb-6">Video & CV Upload</h2>
                    <p class="text-gray-600 mb-6">Add your video introduction and CV to complete your profile.</p>

                    <div class="space-y-6">
                        <div>
                            <label for="role_video_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Role Video URL * <span class="text-gray-500">(required)</span>
                            </label>
                            <input type="url" name="role_video_url" id="role_video_url" required
                                   value="{{ old('role_video_url', $media?->role_video_url) }}"
                                   placeholder="https://..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-sm text-gray-500">Link to your video (YouTube, Vimeo, etc.)</p>
                            @error('role_video_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cv_path" class="block text-sm font-medium text-gray-700 mb-2">
                                CV/Resume Path * <span class="text-gray-500">(required)</span>
                            </label>
                            <input type="text" name="cv_path" id="cv_path" required
                                   value="{{ old('cv_path', $media?->cv_path) }}"
                                   placeholder="cv/your-name-resume.pdf"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-sm text-gray-500">Path to your CV file in storage</p>
                            @error('cv_path')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" id="linkedin_url"
                                   value="{{ old('linkedin_url', $media?->linkedin_url) }}"
                                   placeholder="https://linkedin.com/in/..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="github_url" class="block text-sm font-medium text-gray-700 mb-2">GitHub URL</label>
                            <input type="url" name="github_url" id="github_url"
                                   value="{{ old('github_url', $media?->github_url) }}"
                                   placeholder="https://github.com/..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('candidate.onboarding.step', ['step' => $step - 1]) }}" 
                           class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Back
                        </a>
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                            Complete Onboarding
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
