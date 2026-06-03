<?php

namespace App\Http\Controllers\Employer\ATS;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ATSController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $employerId = $user->id;

        $jobIds = Job::where('employer_id', $employerId)->pluck('id');

        $query = JobApplication::whereIn('job_id', $jobIds)
            ->with(['candidate.candidateProfile', 'candidate.candidateSkills', 'job']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        if ($request->filled('min_score')) {
            $query->withMinScore((int) $request->min_score);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $allowedSorts = ['created_at', 'match_score', 'status', 'applied_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $applications = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => JobApplication::whereIn('job_id', $jobIds)->count(),
            'applied' => JobApplication::whereIn('job_id', $jobIds)->byStatus('applied')->count(),
            'shortlisted' => JobApplication::whereIn('job_id', $jobIds)->byStatus('shortlisted')->count(),
            'interview' => JobApplication::whereIn('job_id', $jobIds)->byStatus('interview')->count(),
            'offer' => JobApplication::whereIn('job_id', $jobIds)->byStatus('offer')->count(),
            'hired' => JobApplication::whereIn('job_id', $jobIds)->byStatus('hired')->count(),
            'rejected' => JobApplication::whereIn('job_id', $jobIds)->byStatus('rejected')->count(),
        ];

        $jobs = Job::where('employer_id', $employerId)
            ->where('status', 'open')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('employer.ats.dashboard', [
            'applications' => $applications,
            'stats' => $stats,
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'status', 'job_id', 'min_score', 'sort', 'direction']),
        ]);
    }

    public function show(JobApplication $application)
    {
        $user = Auth::user();

        if ($application->job->employer_id !== $user->id) {
            abort(403);
        }

        $application->load([
            'candidate.candidateProfile',
            'candidate.candidateSkills.skill',
            'candidate.candidateExperiences',
            'candidate.candidateEducation',
            'candidate.personalityProfile',
            'candidate.jobMatchScores' => fn ($q) => $q->where('job_id', $application->job_id),
            'job',
            'notes.employer',
            'ratings',
            'stageHistories.employer',
            'interviews',
            'matchProfile',
        ]);

        $ratings = $application->ratings->groupBy('category');

        return view('employer.ats.show', [
            'application' => $application,
            'ratings' => $ratings,
        ]);
    }

    public function analytics(Request $request)
    {
        $user = Auth::user();
        $jobIds = Job::where('employer_id', $user->id)->pluck('id');

        $totalApplications = JobApplication::whereIn('job_id', $jobIds)->count();

        $stageCounts = collect([
            'applied', 'shortlisted', 'interview', 'offer', 'hired', 'rejected', 'withdrawn',
        ])->mapWithKeys(function ($status) use ($jobIds) {
            $count = JobApplication::whereIn('job_id', $jobIds)->byStatus($status)->count();

            return [$status => $count];
        });

        $avgMatchScore = JobApplication::whereIn('job_id', $jobIds)->avg('match_score');

        $applicationsByJob = Job::where('employer_id', $user->id)
            ->withCount('applications')
            ->orderBy('applications_count', 'desc')
            ->get(['id', 'title']);

        $conversionRate = $totalApplications > 0
            ? round(($stageCounts->get('hired', 0) / $totalApplications) * 100, 1)
            : 0;

        $monthlyTrend = JobApplication::whereIn('job_id', $jobIds)
            ->selectRaw("strftime('%Y-%m', created_at) as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('employer.ats.analytics', [
            'totalApplications' => $totalApplications,
            'stageCounts' => $stageCounts,
            'avgMatchScore' => round($avgMatchScore ?? 0, 1),
            'applicationsByJob' => $applicationsByJob,
            'conversionRate' => $conversionRate,
            'monthlyTrend' => $monthlyTrend,
        ]);
    }
}
