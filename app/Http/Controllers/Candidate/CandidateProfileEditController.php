<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateCandidateMatches;
use App\Models\CandidateProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidateProfileEditController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $profile = $user->candidateProfile ?? null;

        return view('candidate.profile.edit', [
            'profile' => $profile,
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
        ]);

        CandidateProfile::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        RecalculateCandidateMatches::dispatch($user);

        return redirect()->route('candidate.profile.edit')
            ->with('success', 'Profile updated successfully!');
    }
}
