<?php

namespace App\Services;

use App\Models\TrustScore;
use App\Models\User;
use App\Models\VerificationType;

class TrustScoreService
{
    const SCORE_EMAIL = 10;

    const SCORE_PHONE = 10;

    const SCORE_IDENTITY = 25;

    const SCORE_EDUCATION = 20;

    const SCORE_EMPLOYMENT = 20;

    const SCORE_CERTIFICATION = 15;

    const MAX_SCORE = 100;

    public function calculate(User $candidate): int
    {
        $score = 0;

        if ($candidate->email_verified_at) {
            $score += self::SCORE_EMAIL;
        }

        if ($candidate->phone_verified_at) {
            $score += self::SCORE_PHONE;
        }

        $approvedVerifications = $candidate->candidateVerifications()
            ->approved()
            ->with('verificationType')
            ->get()
            ->keyBy('verification_type_id');

        $identityType = VerificationType::where('slug', 'identity')->value('id');
        $educationType = VerificationType::where('slug', 'education')->value('id');
        $employmentType = VerificationType::where('slug', 'employment')->value('id');
        $certificationType = VerificationType::where('slug', 'certification')->value('id');

        if ($identityType && $approvedVerifications->has($identityType)) {
            $score += self::SCORE_IDENTITY;
        }

        if ($educationType && $approvedVerifications->has($educationType)) {
            $score += self::SCORE_EDUCATION;
        }

        if ($employmentType && $approvedVerifications->has($employmentType)) {
            $score += self::SCORE_EMPLOYMENT;
        }

        if ($certificationType && $approvedVerifications->has($certificationType)) {
            $score += self::SCORE_CERTIFICATION;
        }

        return min($score, self::MAX_SCORE);
    }

    public function getLevel(int $score): string
    {
        return match (true) {
            $score >= 76 => 'Verified Professional',
            $score >= 51 => 'Highly Trusted',
            $score >= 26 => 'Trusted',
            default => 'Beginner',
        };
    }

    public function getLevelBadgeVariant(string $level): string
    {
        return match ($level) {
            'Verified Professional' => 'success',
            'Highly Trusted' => 'primary',
            'Trusted' => 'warning',
            default => 'default',
        };
    }

    public function getScoreColor(int $score): string
    {
        return match (true) {
            $score >= 76 => 'text-green-600',
            $score >= 51 => 'text-blue-600',
            $score >= 26 => 'text-amber-600',
            default => 'text-gray-500',
        };
    }

    public function getProgressColor(int $score): string
    {
        return match (true) {
            $score >= 76 => 'bg-green-500',
            $score >= 51 => 'bg-blue-500',
            $score >= 26 => 'bg-amber-500',
            default => 'bg-gray-400',
        };
    }

    public function getOrCreate(User $candidate): TrustScore
    {
        $score = $this->calculate($candidate);

        return TrustScore::updateOrCreate(
            ['candidate_id' => $candidate->id],
            [
                'score' => $score,
                'level' => $this->getLevel($score),
            ]
        );
    }

    public function recalculateForCandidate(User $candidate): TrustScore
    {
        return $this->getOrCreate($candidate);
    }
}
