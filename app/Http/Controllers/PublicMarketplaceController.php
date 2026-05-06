<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PublicMarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->with('candidateProfile');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('candidateProfile', function ($profile) use ($search) {
                        $profile->where('current_role', 'like', "%{$search}%")
                            ->orWhere('desired_role', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('skills') && $request->skills) {
            $skills = array_map('trim', explode(',', $request->skills));
            $query->whereHas('candidateSkills', function ($q) use ($skills) {
                $q->whereIn('skill_name', $skills);
            });
        }

        if ($request->has('location') && $request->location) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('location', 'like', '%'.$request->location.'%')
                    ->orWhere('location_country', 'like', '%'.$request->location.'%');
            });
        }

        if ($request->has('work_preference') && $request->work_preference) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('work_preference', $request->work_preference);
            });
        }

        if ($request->has('availability') && $request->availability) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('availability', $request->availability);
            });
        }

        $candidates = $query->paginate(20)->withQueryString();

        return view('marketplace.index', [
            'candidates' => $candidates,
        ]);
    }

    public function showCandidate(User $candidate)
    {
        $candidate->load([
            'candidateProfile',
            'candidateSkills',
            'candidateExperiences',
            'candidateEducation',
            'candidateAssessment',
            'candidateMedia',
        ]);

        $aiInsights = null;
        $profile = $candidate->candidateProfile;

        if ($profile && $profile->ai_summary) {
            $aiInsights = [
                'summary' => $profile->ai_summary,
                'strengths' => json_decode($profile->ai_strengths ?? '[]', true),
                'risks' => $profile->ai_risks ? json_decode($profile->ai_risks, true) : [],
                'recommendation' => $profile->ai_recommendation,
            ];
        }

        return view('marketplace.candidate', [
            'candidate' => $candidate,
            'aiInsights' => $aiInsights,
        ]);
    }
}
