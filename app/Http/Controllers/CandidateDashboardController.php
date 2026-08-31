<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\MatchingEngine;
use App\Services\MatchingEngineService;
use App\Services\ProfileCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateDashboardController extends Controller
{
    protected MatchingEngine $matchingEngine;

    protected MatchingEngineService $matchingEngineService;

    public function __construct(MatchingEngine $matchingEngine, MatchingEngineService $matchingEngineService)
    {
        $this->matchingEngine = $matchingEngine;
        $this->matchingEngineService = $matchingEngineService;
    }

    public function index(Request $request)
    {
        $user = User::where('id', Auth::id())->first();

        if (! $user->onboarding_completed) {
            return redirect()->route('candidate.onboarding.step', ['step' => 1]);
        }

        $profile = $user->candidateProfile;
        $personalityProfile = $user->personalityProfile;
        $applications = $user->jobApplications()
            ->with('job')
            ->orderBy('created_at', 'desc')
            ->get();

        $matchingJobs = $this->matchingEngine->getTopMatchingJobs($user, 10);

        $recommendedJobs = $personalityProfile?->assessment_completed
            ? $this->matchingEngineService->getTopMatchingJobs($user, 5)
            : collect();

        $completionService = app(ProfileCompletionService::class);
        $completion = $completionService->sync($user);

        return view('candidate.dashboard', [
            'profile' => $profile,
            'personalityProfile' => $personalityProfile,
            'applications' => $applications,
            'matchingJobs' => $matchingJobs,
            'recommendedJobs' => $recommendedJobs,
            'profileCompletion' => $completion,
            'profileCompletionItems' => $completionService->items($user),
            'profileComplete' => $completionService->complete($user),
        ]);
    }

    public function apply(Request $request, Job $job)
    {
        $user = Auth::user();

        if (! $user->onboarding_completed) {
            return redirect()->route('candidate.onboarding.step', ['step' => 1])
                ->with('error', 'Please complete your profile before applying to jobs.');
        }

        $existingApplication = JobApplication::where('job_id', $job->id)
            ->where('candidate_id', $user->id)
            ->first();

        if ($existingApplication) {
            return back()->with('error', 'You have already applied to this job.');
        }

        $matchScores = $this->matchingEngine->calculateMatch($user, $job);

        $application = JobApplication::create([
            'job_id' => $job->id,
            'candidate_id' => $user->id,
            'status' => JobApplication::STATUS_APPLIED,
            'match_score' => $matchScores['total_score'] ?? 0,
            'applied_at' => now(),
        ]);

        if (auth()->user()->personalityProfile?->assessment_completed) {
            $this->matchingEngineService->saveMatch($user, $job);
        }

        return back()->with('success', 'Application submitted successfully!');
    }

    public function jobs()
    {
        $user = Auth::user();
        $matchingJobs = $this->matchingEngine->getTopMatchingJobs($user, 50);

        return view('candidate.jobs', [
            'jobs' => $matchingJobs,
        ]);
    }

    public function applications()
    {
        $user = Auth::user();
        $applications = $user->jobApplications()
            ->with(['job.employer.company'])
            ->orderBy('applied_at', 'desc')
            ->get();

        return view('candidate.applications', [
            'applications' => $applications,
        ]);
    }

    public function interviews()
    {
        $user = Auth::user();
        $interviews = $user->scheduledInterviews()
            ->with(['job', 'employer'])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return view('candidate.interviews.index', [
            'interviews' => $interviews,
        ]);
    }
}
