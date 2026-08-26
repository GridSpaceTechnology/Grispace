<?php

namespace App\Http\Controllers;

use App\Services\JobMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateRecommendationController extends Controller
{
    public function __construct(protected JobMatchingService $matchingEngine) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->candidateProfile || trim((string) $user->candidateProfile->desired_role) === '') {
            return redirect()->route('candidate.profile.edit')
                ->with('info', 'Add your desired role so we can rank jobs that fit you best.');
        }

        $filters = $request->only([
            'q',
            'location',
            'work_preference',
            'employment_type',
            'role',
            'company',
            'salary_min',
            'experience_max',
            'min_score',
        ]);

        $jobs = $this->matchingEngine->recommendJobsForCandidate($user, $filters, 12);

        return view('candidate.recommended-jobs', [
            'jobs' => $jobs,
        ]);
    }
}
