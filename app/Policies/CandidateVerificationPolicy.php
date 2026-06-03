<?php

namespace App\Policies;

use App\Models\CandidateVerification;
use App\Models\User;

class CandidateVerificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCandidate() || $user->role === 'admin';
    }

    public function view(User $user, CandidateVerification $candidateVerification): bool
    {
        return $user->id === $candidateVerification->candidate_id || $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->isCandidate();
    }

    public function update(User $user, CandidateVerification $candidateVerification): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, CandidateVerification $candidateVerification): bool
    {
        return $user->id === $candidateVerification->candidate_id || $user->role === 'admin';
    }

    public function uploadDocument(User $user, CandidateVerification $candidateVerification): bool
    {
        return $user->id === $candidateVerification->candidate_id;
    }

    public function review(User $user): bool
    {
        return $user->role === 'admin';
    }
}
