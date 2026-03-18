<?php

namespace App\Http\Controllers;

use App\Models\EmployerShortlist;
use App\Models\User;
use App\Services\AI\CandidateInsightService;
use App\Services\MatchingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerMarketplaceController extends Controller
{
    protected MatchingEngine $matchingEngine;

    protected CandidateInsightService $insightService;

    public function __construct(MatchingEngine $matchingEngine, CandidateInsightService $insightService)
    {
        $this->matchingEngine = $matchingEngine;
        $this->insightService = $insightService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

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

        if ($request->has('experience_min') && $request->experience_min) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('years_of_experience', '>=', (int) $request->experience_min);
            });
        }

        if ($request->has('experience_max') && $request->experience_max) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('years_of_experience', '<=', (int) $request->experience_max);
            });
        }

        if ($request->has('location') && $request->location) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('location', 'like', '%'.$request->location.'%')
                    ->orWhere('location_country', 'like', '%'.$request->location.'%');
            });
        }

        if ($request->has('industry') && $request->industry) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('industry', $request->industry);
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

        if ($request->has('temperament') && $request->temperament) {
            $query->whereHas('candidateAssessment', function ($q) use ($request) {
                $q->where('temperament_type', $request->temperament);
            });
        }

        if ($request->has('availability') && $request->availability) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('availability', $request->availability);
            });
        }

        if ($request->has('salary_max') && $request->salary_max) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('salary_expectation', '<=', (int) $request->salary_max);
            });
        }

        if ($request->has('experience_level') && $request->experience_level) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('experience_level', $request->experience_level);
            });
        }

        $candidates = $query->paginate(20)->withQueryString();

        $shortlistedIds = EmployerShortlist::where('employer_id', $user->id)
            ->pluck('candidate_id')
            ->toArray();

        return view('employer.marketplace.index', [
            'candidates' => $candidates,
            'shortlistedIds' => $shortlistedIds,
        ]);
    }

    public function showCandidate(Request $request, User $candidate)
    {
        $user = Auth::user();

        $candidate->load([
            'candidateProfile',
            'candidateSkills',
            'candidateExperiences',
            'candidateEducation',
            'candidateAssessment',
            'candidateMedia',
        ]);

        $isShortlisted = EmployerShortlist::where('employer_id', $user->id)
            ->where('candidate_id', $candidate->id)
            ->exists();

        $profile = $candidate->candidateProfile;
        $aiInsights = null;

        if ($profile && $this->insightService->shouldRegenerate($profile)) {
            $insights = $this->insightService->generateInsights($candidate);

            $profile->update([
                'ai_summary' => $insights['summary'] ?? null,
                'ai_strengths' => json_encode($insights['strengths'] ?? []),
                'ai_risks' => json_encode($insights['risks'] ?? []),
                'ai_recommendation' => $insights['recommendation'] ?? null,
                'ai_last_generated_at' => now(),
            ]);
        }

        if ($profile && $profile->ai_summary) {
            $aiInsights = [
                'summary' => $profile->ai_summary,
                'strengths' => json_decode($profile->ai_strengths ?? '[]', true),
                'risks' => json_decode($profile->ai_risks ?? '[]', true),
                'recommendation' => $profile->ai_recommendation,
                'role_fit' => $insights['role_fit'] ?? null,
            ];
        }

        return view('employer.marketplace.candidate', [
            'candidate' => $candidate,
            'isShortlisted' => $isShortlisted,
            'aiInsights' => $aiInsights,
        ]);
    }

    public function shortlist(Request $request, User $candidate)
    {
        $user = Auth::user();

        $existing = EmployerShortlist::where('employer_id', $user->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return redirect()->back()->with('info', 'Candidate removed from shortlist.');
        }

        EmployerShortlist::create([
            'employer_id' => $user->id,
            'candidate_id' => $candidate->id,
            'job_id' => $request->get('job_id'),
        ]);

        return redirect()->back()->with('success', 'Candidate shortlisted successfully!');
    }

    public function shortlists(Request $request)
    {
        $user = Auth::user();

        $shortlists = EmployerShortlist::where('employer_id', $user->id)
            ->with(['candidate.candidateProfile', 'job'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('employer.marketplace.shortlist', [
            'shortlists' => $shortlists,
        ]);
    }
}
