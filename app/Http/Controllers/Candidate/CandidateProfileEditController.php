<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateCandidateMatches;
use App\Models\CandidateMedia;
use App\Models\CandidateProfile;
use App\Services\ProfileCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CandidateProfileEditController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('candidate.profile.edit', [
            'profile' => $user->candidateProfile ?? null,
            'media' => $user->candidateMedia ?? null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_role' => 'nullable|string|max:255',
            'desired_role' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'industry' => 'nullable|string|max:255',
            'employment_type_preference' => 'nullable|in:full_time,part_time,contract,freelance,internship',
            'salary_expectation' => 'nullable|numeric|min:0',
            'work_preference' => 'nullable|in:remote,hybrid,onsite,flexible',
            'greatest_achievement' => 'nullable|string',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'role_video_url' => 'nullable|url|max:255',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        CandidateProfile::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $portfolioLinks = $validated['portfolio_url'] ?? null
            ? [$validated['portfolio_url']]
            : [];

        $existingMedia = $user->candidateMedia;

        $media = CandidateMedia::updateOrCreate(
            ['user_id' => $user->id],
            [
                'linkedin_url' => $validated['linkedin_url'] ?? $existingMedia?->linkedin_url,
                'github_url' => $validated['github_url'] ?? $existingMedia?->github_url,
                'role_video_url' => $validated['role_video_url'] ?? ($existingMedia?->role_video_url ?? ''),
                'portfolio_links_json' => $portfolioLinks,
                'cv_path' => $existingMedia?->cv_path ?? '',
            ]
        );

        if ($request->hasFile('resume')) {
            if ($media->cv_path) {
                Storage::disk('public')->delete($media->cv_path);
            }

            $media->cv_path = $request->file('resume')->store('cvs/'.$user->id, 'public');
            $media->save();
        }

        app(ProfileCompletionService::class)->sync($user);

        RecalculateCandidateMatches::dispatch($user);

        return redirect()->route('candidate.profile.edit')
            ->with('success', 'Profile updated successfully!');
    }
}
