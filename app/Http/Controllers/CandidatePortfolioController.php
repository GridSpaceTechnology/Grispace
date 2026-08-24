<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidatePortfolioController extends Controller
{
    public function show(Request $request, User $candidate): View
    {
        abort_unless($candidate->isCandidate(), 404);

        $viewer = $request->user();

        $candidate->load([
            'candidateProfile',
            'candidateSkills.skill',
            'candidateExperiences',
            'candidateEducation',
            'candidateMedia',
            'trustScore',
            'personalityProfile',
            'candidateVerifications' => fn ($q) => $q->with('verificationType'),
        ]);

        $approvedVerifications = $candidate->candidateVerifications
            ->where('status', 'approved')
            ->pluck('verificationType.slug')
            ->unique()
            ->values();

        $canViewPhoneNumber = $this->canViewPhoneNumber($viewer, $candidate);

        return view('candidates.show', [
            'candidate' => $candidate,
            'approvedVerifications' => $approvedVerifications,
            'trustScore' => $candidate->trustScore,
            'canViewPhoneNumber' => $canViewPhoneNumber,
        ]);
    }

    private function canViewPhoneNumber(?User $viewer, User $candidate): bool
    {
        if (! $viewer) {
            return false;
        }

        if ($viewer->id === $candidate->id || $viewer->role === 'admin') {
            return true;
        }

        return $viewer->isEmployer();
    }
}
