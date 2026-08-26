<?php

namespace App\Http\Controllers;

use App\Models\EmployerShortlist;
use App\Models\Job;
use App\Services\JobMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerJobCandidateController extends Controller
{
    public function __construct(protected JobMatchingService $matchingEngine) {}

    public function index(Request $request, Job $job)
    {
        $user = Auth::user();

        abort_unless($job->employer_id === $user->id || $user->role === 'admin', 403);

        $filters = $request->only([
            'q',
            'skills',
            'experience_min',
            'experience_max',
            'work_preference',
            'location',
            'availability',
            'min_score',
        ]);

        $candidates = $this->matchingEngine->rankCandidatesForJob($job, $filters, 12);

        $shortlistedIds = EmployerShortlist::where('employer_id', $user->id)
            ->pluck('candidate_id')
            ->all();

        $appliedIds = $job->applications()->pluck('candidate_id')->all();

        return view('employer.jobs.candidates', [
            'job' => $job,
            'candidates' => $candidates,
            'shortlistedIds' => $shortlistedIds,
            'appliedIds' => $appliedIds,
        ]);
    }
}
