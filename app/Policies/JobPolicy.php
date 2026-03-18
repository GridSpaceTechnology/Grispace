<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function view(?User $user, Job $job): bool
    {
        if ($job->status === 'open') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->id === $job->employer_id) {
            return true;
        }

        return $user->role === 'admin';
    }

    public function update(User $user, Job $job): bool
    {
        return $user->id === $job->employer_id || $user->role === 'admin';
    }

    public function delete(User $user, Job $job): bool
    {
        return $user->id === $job->employer_id || $user->role === 'admin';
    }

    public function close(User $user, Job $job): bool
    {
        return $user->id === $job->employer_id;
    }

    public function publish(User $user, Job $job): bool
    {
        return $user->id === $job->employer_id;
    }

    public function viewApplications(User $user, Job $job): bool
    {
        return $user->id === $job->employer_id || $user->role === 'admin';
    }

    public function manageRequirements(User $user, Job $job): bool
    {
        return $user->id === $job->employer_id;
    }
}
