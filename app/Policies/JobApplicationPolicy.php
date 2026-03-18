<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function view(?User $user, JobApplication $application): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isCandidate() && $user->id === $application->candidate_id) {
            return true;
        }

        if ($user->isEmployer()) {
            $job = $application->job;

            return $job && $job->employer_id === $user->id;
        }

        return $user->role === 'admin';
    }

    public function update(?User $user, JobApplication $application): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->isEmployer()) {
            $job = $application->job;

            return $job && $job->employer_id === $user->id;
        }

        return false;
    }

    public function withdraw(User $user, JobApplication $application): bool
    {
        return $user->isCandidate()
            && $user->id === $application->candidate_id
            && $application->canWithdraw();
    }

    public function shortlist(User $user, JobApplication $application): bool
    {
        if (! $user->isEmployer()) {
            return false;
        }

        $job = $application->job;

        return $job && $job->employer_id === $user->id;
    }

    public function reject(User $user, JobApplication $application): bool
    {
        return $this->shortlist($user, $application);
    }

    public function scheduleInterview(User $user, JobApplication $application): bool
    {
        return $this->shortlist($user, $application);
    }
}
