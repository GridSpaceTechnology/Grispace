<?php

namespace App\Http\Controllers;

use App\Models\EmployerShortlist;
use App\Models\Job;
use App\Services\MatchingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerJobCandidateController extends Controller
{
    protected MatchingEngine $matchingEngine;

    public function __construct(MatchingEngine $matchingEngine)
    {
        $this->matchingEngine = $matchingEngine;
    }

    public function index(Request $request, Job $job)
    {
        $user = Auth::user();

        $candidates = $this->matchingEngine->getTopMatchingCandidates($job, 50);

        $shortlistedIds = EmployerShortlist::where('employer_id', $user->id)
            ->pluck('candidate_id')
            ->toArray();

        return view('employer.jobs.candidates', [
            'job' => $job,
            'candidates' => $candidates,
            'shortlistedIds' => $shortlistedIds,
        ]);
    }
}
