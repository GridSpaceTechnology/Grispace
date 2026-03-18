<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = User::where('id', Auth::id())->first();

        if (! $user->onboarding_completed) {
            return redirect()->route('employer.setup');
        }

        $employerProfile = $user->employerProfile;

        $activeJobs = Job::where('employer_id', $user->id)
            ->where('status', 'active')
            ->withCount('applications')
            ->orderBy('created_at', 'desc')
            ->get();

        $recentApplications = JobApplication::whereHas('job', function ($query) use ($user) {
            $query->where('employer_id', $user->id);
        })
            ->with(['job', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $jobIds = Job::where('employer_id', $user->id)->pluck('id');
        $totalApplications = JobApplication::whereIn('job_id', $jobIds)->count();
        $shortlistedCandidates = JobApplication::whereIn('job_id', $jobIds)
            ->where('status', JobApplication::STATUS_SHORTLISTED)->count();
        $activeInterviews = JobApplication::whereIn('job_id', $jobIds)
            ->where('status', JobApplication::STATUS_INTERVIEW)->count();
        $offersSent = JobApplication::whereIn('job_id', $jobIds)
            ->where('status', JobApplication::STATUS_OFFER)->count();
        $hiresCompleted = JobApplication::whereIn('job_id', $jobIds)
            ->where('status', JobApplication::STATUS_HIRED)->count();

        $topCandidates = User::where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->with('candidateProfile')
            ->get()
            ->take(5);

        return view('employer.dashboard', [
            'employerProfile' => $employerProfile,
            'activeJobs' => $activeJobs,
            'recentApplications' => $recentApplications,
            'topCandidates' => $topCandidates,
            'totalApplications' => $totalApplications,
            'shortlistedCandidates' => $shortlistedCandidates,
            'activeInterviews' => $activeInterviews,
            'offersSent' => $offersSent,
            'hiresCompleted' => $hiresCompleted,
        ]);
    }
}
