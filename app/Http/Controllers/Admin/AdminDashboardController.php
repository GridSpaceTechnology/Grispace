<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalCandidates = User::where('role', 'candidate')->count();
        $totalEmployers = User::where('role', 'employer')->count();
        $totalJobs = Job::count();
        $completedOnboardings = User::where('onboarding_completed', true)->count();
        $pendingOnboardings = User::where('onboarding_completed', false)->count();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalCandidates' => $totalCandidates,
            'totalEmployers' => $totalEmployers,
            'totalJobs' => $totalJobs,
            'completedOnboardings' => $completedOnboardings,
            'pendingOnboardings' => $pendingOnboardings,
        ]);
    }
}
