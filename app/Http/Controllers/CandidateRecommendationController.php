<?php

namespace App\Http\Controllers;

use App\Services\MatchingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateRecommendationController extends Controller
{
    protected MatchingEngine $matchingEngine;

    public function __construct(MatchingEngine $matchingEngine)
    {
        $this->matchingEngine = $matchingEngine;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $jobs = $this->matchingEngine->getTopMatchingJobs($user, 50);

        return view('candidate.recommended-jobs', [
            'jobs' => $jobs,
        ]);
    }
}
