<?php

namespace Database\Seeders;

use App\Models\CandidateEducation;
use App\Models\CandidatePersonalityProfile;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Local development demo data for visually testing the matching engine.
 * Refuses to run outside local/testing environments.
 */
class MatchingDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('MatchingDemoSeeder only runs in local environments.');

            return;
        }

        $skillMap = [];

        foreach (['PHP', 'Laravel', 'MySQL', 'Docker', 'AWS', 'JavaScript', 'React', 'Vue', 'Python', 'Figma', 'REST API'] as $name) {
            $skillMap[$name] = Skill::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'type' => 'technical']
            );
        }

        // ------------------------------------------------------------------
        // Employers
        // ------------------------------------------------------------------

        $fintech = User::factory()->create([
            'name' => 'Ada Fintech HR',
            'email' => 'ada@paystackdemo.test',
            'role' => 'employer',
            'onboarding_completed' => true,
        ]);

        $fintechCompany = Company::create([
            'user_id' => $fintech->id,
            'name' => 'PayFlow NG',
            'slug' => 'payflow-ng',
            'industry' => 'Fintech',
            'work_environment' => 'Startup or dynamic environment',
            'company_pace' => 'Fast-paced',
            'independence_level' => 'Independent work encouraged',
            'allow_candidate_messages' => true,
        ]);

        $agency = User::factory()->create([
            'name' => 'Bode Agency Lead',
            'email' => 'bode@crazycatdemo.test',
            'role' => 'employer',
            'onboarding_completed' => true,
        ]);

        Company::create([
            'user_id' => $agency->id,
            'name' => 'CrazyCat Studio',
            'slug' => 'crazycat-studio',
            'industry' => 'Design',
            'allow_candidate_messages' => true,
        ]);

        $backendJob = Job::create([
            'employer_id' => $fintech->id,
            'company_id' => $fintechCompany->id,
            'title' => 'Senior Backend Engineer',
            'role' => 'Backend Developer',
            'industry' => 'Fintech',
            'slug' => 'senior-backend-engineer-payflow',
            'description' => 'Build payment infrastructure powering thousands of Nigerian businesses.',
            'employment_type' => 'full_time',
            'work_preference' => 'hybrid',
            'location' => 'Lagos',
            'location_country' => 'Nigeria',
            'salary_min' => 800000,
            'salary_max' => 1400000,
            'salary_currency' => 'NGN',
            'salary_period' => 'Month',
            'minimum_experience' => 4,
            'experience_level' => 'senior',
            'temperament_preference' => 'analytical',
            'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'REST API'],
            'status' => 'open',
            'published_at' => now(),
        ]);

        foreach (['PHP' => 3, 'Laravel' => 2] as $name => $proficiency) {
            $backendJob->jobSkills()->create([
                'skill_id' => $skillMap[$name]->id,
                'is_required' => true,
                'min_proficiency' => $proficiency,
            ]);
        }

        $designJob = Job::create([
            'employer_id' => $agency->id,
            'title' => 'Product Designer',
            'role' => 'Graphic Designer',
            'industry' => 'Design',
            'slug' => 'product-designer-crazycat',
            'description' => 'Own end-to-end product design for client projects.',
            'employment_type' => 'full_time',
            'work_preference' => 'remote',
            'salary_min' => 450000,
            'salary_max' => 700000,
            'salary_currency' => 'NGN',
            'salary_period' => 'Month',
            'temperament_preference' => 'expressive',
            'required_skills_json' => ['Figma', 'JavaScript'],
            'status' => 'open',
            'published_at' => now(),
        ]);

        // ------------------------------------------------------------------
        // Candidates with deliberately different profiles
        // ------------------------------------------------------------------

        $this->seedCandidate('Chinedu Backend', 'chinedu@demo.test', [
            'desired_role' => 'Backend Developer',
            'years_of_experience' => 6,
            'salary_expectation' => 1000000,
            'work_preference' => 'hybrid',
            'location_country' => 'Nigeria',
        ], ['PHP', 'Laravel', 'MySQL', 'Docker', 'AWS'], [
            'temperament_type' => 'Analytical',
            'work_style' => 'Structured and Methodical',
            'organizational_fit' => 'Startup or Dynamic Environment',
        ], "Bachelor's Degree");

        $this->seedCandidate('Amaka Fullstack', 'amaka@demo.test', [
            'desired_role' => 'Fullstack Developer',
            'years_of_experience' => 2,
            'salary_expectation' => 600000,
            'work_preference' => 'remote',
            'location_country' => 'Nigeria',
        ], ['PHP', 'JavaScript', 'Vue'], [
            'temperament_type' => 'Energetic',
        ], null);

        $this->seedCandidate('Tola Designer', 'tola@demo.test', [
            'desired_role' => 'Product Designer',
            'years_of_experience' => 5,
            'salary_expectation' => 550000,
            'work_preference' => 'remote',
            'location_country' => 'Nigeria',
        ], ['Figma', 'React'], [
            'temperament_type' => 'Calm',
        ], null);

        $backendJob->refresh();
        $designJob->refresh();
    }

    private function seedCandidate(
        string $name,
        string $email,
        array $profileAttributes,
        array $skills,
        array $personality = [],
        ?string $qualification = null
    ): void {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'role' => 'candidate',
            'onboarding_completed' => true,
        ]);

        CandidateProfile::create(array_merge([
            'user_id' => $user->id,
        ], $profileAttributes));

        foreach ($skills as $skill) {
            CandidateSkill::create([
                'user_id' => $user->id,
                'skill_name' => $skill,
                'proficiency_level' => rand(2, 4),
            ]);
        }

        if ($personality !== []) {
            CandidatePersonalityProfile::updateOrCreate(
                ['candidate_id' => $user->id],
                array_merge($personality, ['assessment_completed' => true, 'completed_at' => now()])
            );
        }

        if ($qualification !== null) {
            CandidateEducation::create([
                'user_id' => $user->id,
                'institution' => 'University of Lagos',
                'qualification' => $qualification,
                'year_completed' => 2018,
            ]);
        }
    }
}
