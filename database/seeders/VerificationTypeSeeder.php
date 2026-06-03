<?php

namespace Database\Seeders;

use App\Models\VerificationType;
use Illuminate\Database\Seeder;

class VerificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Email Verification', 'slug' => 'email', 'description' => 'Verify your email address', 'icon' => 'envelope', 'active' => true],
            ['name' => 'Phone Verification', 'slug' => 'phone', 'description' => 'Verify your phone number', 'icon' => 'phone', 'active' => true],
            ['name' => 'Identity Verification', 'slug' => 'identity', 'description' => 'Verify your identity with a government-issued ID', 'icon' => 'identification', 'active' => true],
            ['name' => 'Education Verification', 'slug' => 'education', 'description' => 'Verify your educational qualifications', 'icon' => 'academic-cap', 'active' => true],
            ['name' => 'Employment Verification', 'slug' => 'employment', 'description' => 'Verify your employment history', 'icon' => 'briefcase', 'active' => true],
            ['name' => 'Certification Verification', 'slug' => 'certification', 'description' => 'Verify your professional certifications', 'icon' => 'certificate', 'active' => true],
        ];

        foreach ($types as $type) {
            VerificationType::create($type);
        }
    }
}
