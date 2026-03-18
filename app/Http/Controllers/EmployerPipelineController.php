<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Services\MatchingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerPipelineController extends Controller
{
    protected MatchingEngine $matchingEngine;

    public function __construct(MatchingEngine $matchingEngine)
    {
        $this->matchingEngine = $matchingEngine;
    }

    public function index(Job $job)
    {
        $user = Auth::user();

        if ($job->employer_id !== $user->id) {
            abort(403, 'You can only view your own job pipelines.');
        }

        $applications = JobApplication::where('job_id', $job->id)
            ->with(['candidate.candidateProfile', 'candidate.candidateSkills'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stages = [
            JobApplication::STATUS_APPLIED => $applications->where('status', JobApplication::STATUS_APPLIED)->values(),
            JobApplication::STATUS_SHORTLISTED => $applications->where('status', JobApplication::STATUS_SHORTLISTED)->values(),
            JobApplication::STATUS_INTERVIEW => $applications->where('status', JobApplication::STATUS_INTERVIEW)->values(),
            JobApplication::STATUS_OFFER => $applications->where('status', JobApplication::STATUS_OFFER)->values(),
            JobApplication::STATUS_HIRED => $applications->where('status', JobApplication::STATUS_HIRED)->values(),
            JobApplication::STATUS_REJECTED => $applications->where('status', JobApplication::STATUS_REJECTED)->values(),
        ];

        return view('employer.pipeline.index', [
            'job' => $job,
            'applications' => $applications,
            'stages' => $stages,
        ]);
    }

    public function moveStage(Request $request, JobApplication $application)
    {
        $user = Auth::user();
        $job = $application->job;

        if ($job->employer_id !== $user->id) {
            abort(403, 'You can only manage your own job pipelines.');
        }

        $request->validate([
            'action' => 'required|in:next,previous,reject',
        ]);

        $currentStatus = $application->status;
        $now = now();

        if ($request->action === 'reject') {
            $application->update([
                'status' => JobApplication::STATUS_REJECTED,
                'rejected_at' => $now,
            ]);

            $application->candidate->notify(new \App\Notifications\ApplicationRejected($application));

            return redirect()->back()->with('success', 'Candidate has been rejected.');
        }

        if ($request->action === 'next') {
            $nextStatus = $this->getNextStatus($currentStatus);

            if (! $nextStatus) {
                return redirect()->back()->with('error', 'Cannot move to next stage.');
            }

            $updateData = ['status' => $nextStatus];

            switch ($nextStatus) {
                case JobApplication::STATUS_SHORTLISTED:
                    $updateData['shortlisted_at'] = $now;
                    break;
                case JobApplication::STATUS_INTERVIEW:
                    $updateData['interview_at'] = $now;
                    break;
                case JobApplication::STATUS_OFFER:
                    $updateData['offer_sent_at'] = $now;
                    break;
                case JobApplication::STATUS_HIRED:
                    $updateData['hired_at'] = $now;
                    break;
            }

            $application->update($updateData);

            $this->sendStageNotification($application, $nextStatus);

            return redirect()->back()->with('success', 'Candidate moved to next stage.');
        }

        if ($request->action === 'previous') {
            $prevStatus = $this->getPreviousStatus($currentStatus);

            if (! $prevStatus) {
                return redirect()->back()->with('error', 'Cannot move to previous stage.');
            }

            $application->update(['status' => $prevStatus]);

            return redirect()->back()->with('success', 'Candidate moved to previous stage.');
        }

        return redirect()->back();
    }

    protected function getNextStatus(string $currentStatus): ?string
    {
        $transitions = [
            JobApplication::STATUS_APPLIED => JobApplication::STATUS_SHORTLISTED,
            JobApplication::STATUS_SHORTLISTED => JobApplication::STATUS_INTERVIEW,
            JobApplication::STATUS_INTERVIEW => JobApplication::STATUS_OFFER,
            JobApplication::STATUS_OFFER => JobApplication::STATUS_HIRED,
        ];

        return $transitions[$currentStatus] ?? null;
    }

    protected function getPreviousStatus(string $currentStatus): ?string
    {
        $transitions = [
            JobApplication::STATUS_SHORTLISTED => JobApplication::STATUS_APPLIED,
            JobApplication::STATUS_INTERVIEW => JobApplication::STATUS_SHORTLISTED,
            JobApplication::STATUS_OFFER => JobApplication::STATUS_INTERVIEW,
            JobApplication::STATUS_HIRED => JobApplication::STATUS_OFFER,
            JobApplication::STATUS_REJECTED => JobApplication::STATUS_APPLIED,
        ];

        return $transitions[$currentStatus] ?? null;
    }

    protected function sendStageNotification(JobApplication $application, string $newStatus): void
    {
        switch ($newStatus) {
            case JobApplication::STATUS_SHORTLISTED:
                $application->candidate->notify(new \App\Notifications\ApplicationShortlisted($application));
                break;
            case JobApplication::STATUS_INTERVIEW:
                $application->candidate->notify(new \App\Notifications\ApplicationInterview($application));
                break;
            case JobApplication::STATUS_OFFER:
                $application->candidate->notify(new \App\Notifications\ApplicationOffer($application));
                break;
            case JobApplication::STATUS_HIRED:
                $application->candidate->notify(new \App\Notifications\ApplicationHired($application));
                break;
        }
    }
}
