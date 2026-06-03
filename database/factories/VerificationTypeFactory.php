<?php

namespace Database\Factories;

use App\Models\VerificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VerificationTypeFactory extends Factory
{
    protected $model = VerificationType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->unique()->slug(1),
            'description' => fake()->sentence(),
            'active' => true,
        ];
    }
}
