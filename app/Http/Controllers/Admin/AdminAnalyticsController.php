<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalCandidates = User::where('role', 'candidate')->count();
        $totalEmployers = User::where('role', 'employer')->count();
        $totalJobs = Job::count();
        $activeJobs = Job::where('status', 'open')->count();
        $totalApplications = JobApplication::count();
        $completedOnboardings = User::where('onboarding_completed', true)->count();

        $recentUsers = User::orderBy('created_at', 'desc')->limit(10)->get();
        $recentJobs = Job::with('company')->orderBy('created_at', 'desc')->limit(10)->get();
        $recentApplications = JobApplication::with(['user', 'job'])->orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.analytics', [
            'totalUsers' => $totalUsers,
            'totalCandidates' => $totalCandidates,
            'totalEmployers' => $totalEmployers,
            'totalJobs' => $totalJobs,
            'activeJobs' => $activeJobs,
            'totalApplications' => $totalApplications,
            'completedOnboardings' => $completedOnboardings,
            'recentUsers' => $recentUsers,
            'recentJobs' => $recentJobs,
            'recentApplications' => $recentApplications,
        ]);
    }
}
