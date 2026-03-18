<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewProfile(?User $viewer, User $user): bool
    {
        if (! $user->isCandidate()) {
            return false;
        }

        if (! $user->onboarding_completed) {
            return false;
        }

        if (! $viewer) {
            return $user->profile_visibility === 'public';
        }

        if ($viewer->id === $user->id) {
            return true;
        }

        if ($viewer->role === 'admin') {
            return true;
        }

        if ($viewer->isEmployer()) {
            return $this->employerCanView($viewer, $user);
        }

        if ($viewer->isCandidate()) {
            return $user->profile_visibility === 'candidate';
        }

        return false;
    }

    public function viewContactInfo(?User $viewer, User $user): bool
    {
        if (! $viewer) {
            return false;
        }

        if ($viewer->id === $user->id) {
            return true;
        }

        if ($viewer->role === 'admin') {
            return true;
        }

        if ($viewer->isEmployer()) {
            return $this->employerCanViewContact($viewer, $user);
        }

        return false;
    }

    public function search(?User $viewer): bool
    {
        if (! $viewer) {
            return false;
        }

        return $viewer->isEmployer() || $viewer->role === 'admin';
    }

    public function updateProfile(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    public function deleteProfile(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->role === 'admin';
    }

    public function exportData(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->role === 'admin';
    }

    private function employerCanView(User $employer, User $candidate): bool
    {
        $visibility = $candidate->profile_visibility ?? 'employer';

        return match ($visibility) {
            'public' => true,
            'employer' => true,
            'applied' => $this->candidateAppliedToEmployer($candidate, $employer),
            'hidden' => false,
            default => false,
        };
    }

    private function employerCanViewContact(User $employer, User $candidate): bool
    {
        return $this->candidateAppliedToEmployer($candidate, $employer);
    }

    private function candidateAppliedToEmployer(User $candidate, User $employer): bool
    {
        return $candidate->jobApplications()
            ->whereHas('job', fn ($q) => $q->where('employer_id', $employer->id))
            ->exists();
    }
}
