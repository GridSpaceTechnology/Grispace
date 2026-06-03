<?php

namespace Database\Factories;

use App\Models\CandidateVerification;
use App\Models\User;
use App\Models\VerificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CandidateVerificationFactory extends Factory
{
    protected $model = CandidateVerification::class;

    public function definition(): array
    {
        return [
            'candidate_id' => User::factory(),
            'verification_type_id' => VerificationType::inRandomOrder()->first()?->id ?? 1,
            'status' => 'pending',
            'submitted_at' => now(),
            'notes' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'verified_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
        ]);
    }
}
