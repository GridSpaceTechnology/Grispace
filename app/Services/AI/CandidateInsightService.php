<?php

namespace App\Services\AI;

use App\Models\CandidateAssessment;
use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CandidateInsightService
{
    protected ?string $openaiApiKey;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.key');
    }

    public function generateInsights(User $candidate): array
    {
        $profile = $candidate->candidateProfile;
        $assessment = $candidate->candidateAssessment;
        $skills = $candidate->candidateSkills->pluck('skill_name')->toArray();
        $experiences = $candidate->candidateExperiences;

        if ($this->openaiApiKey && config('services.openai.enabled')) {
            return $this->generateWithOpenAI($candidate, $profile, $assessment, $skills);
        }

        return $this->generateRuleBased($candidate, $profile, $assessment, $skills, $experiences);
    }

    protected function generateWithOpenAI(User $candidate, ?CandidateProfile $profile, ?CandidateAssessment $assessment, array $skills): array
    {
        $prompt = $this->buildPrompt($candidate, $profile, $assessment, $skills);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4-turbo-preview'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert HR recruitment analyst. Analyze candidate profiles and provide structured insights.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return $this->parseAIResponse($content);
            }
        } catch (\Exception $e) {
            Log::warning('OpenAI API failed, falling back to rule-based: '.$e->getMessage());
        }

        return $this->generateRuleBased($candidate, $profile, $assessment, $skills, $candidate->candidateExperiences);
    }

    protected function buildPrompt(User $candidate, ?CandidateProfile $profile, ?CandidateAssessment $assessment, array $skills): string
    {
        $data = [
            'name' => $candidate->name,
            'desired_role' => $profile?->desired_role ?? 'Not specified',
            'years_experience' => $profile?->years_of_experience ?? 0,
            'industry' => $profile?->industry ?? 'Not specified',
            'work_preference' => $profile?->work_preference ?? 'Not specified',
            'skills' => implode(', ', $skills),
            'assessment_score' => $assessment?->skill_score ?? 0,
            'temperament' => $assessment?->temperament_type ?? 'Unknown',
        ];

        return <<<TEXT
Analyze this candidate and provide insights in JSON format:

Candidate: {$data['name']}
Desired Role: {$data['desired_role']}
Experience: {$data['years_experience']} years
Industry: {$data['industry']}
Work Preference: {$data['work_preference']}
Skills: {$data['skills']}
Assessment Score: {$data['assessment_score']}%
Temperament: {$data['temperament']}

Provide a JSON response with these keys:
- summary (2-3 sentence overview)
- strengths (array of 3-5 strengths)
- risks (array of 2-4 potential risks)
- recommendation (2-3 sentence hiring recommendation)

Format as valid JSON only.
TEXT;
    }

    protected function parseAIResponse(string $content): array
    {
        $content = trim($content);

        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'summary' => $json['summary'] ?? '',
                    'strengths' => $json['strengths'] ?? [],
                    'risks' => $json['risks'] ?? [],
                    'recommendation' => $json['recommendation'] ?? '',
                ];
            }
        }

        return [
            'summary' => 'Analysis completed. Review the full profile for details.',
            'strengths' => ['Strong candidate profile'],
            'risks' => [],
            'recommendation' => 'Proceed with standard hiring process.',
        ];
    }

    protected function generateRuleBased(User $candidate, ?CandidateProfile $profile, ?CandidateAssessment $assessment, array $skills, $experiences): array
    {
        $yearsExp = $profile?->years_of_experience ?? 0;
        $desiredRole = $profile?->desired_role ?? 'General';
        $industry = $profile?->industry ?? '';
        $workPref = $profile?->work_preference ?? 'Not specified';
        $assessmentScore = $assessment?->skill_score ?? 0;
        $temperament = $assessment?->temperament_type ?? '';

        $strengths = [];
        $risks = [];
        $summaryParts = [];

        if ($yearsExp >= 5) {
            $strengths[] = 'Extensive industry experience ('.$yearsExp.' years)';
            $summaryParts[] = $yearsExp.'+ years of professional experience';
        } elseif ($yearsExp >= 2) {
            $strengths[] = 'Solid foundational experience ('.$yearsExp.' years)';
            $summaryParts[] = $yearsExp.' years of experience in the field';
        } else {
            $strengths[] = 'Early career candidate with growth potential';
            $summaryParts[] = 'Emerging professional';
        }

        if (! empty($skills)) {
            $techSkills = array_filter($skills, fn ($s) => in_array(strtolower($s), ['php', 'javascript', 'python', 'java', 'react', 'laravel', 'vue', 'node', 'typescript', 'sql']));
            if (! empty($techSkills)) {
                $strengths[] = 'Strong technical skillset: '.implode(', ', array_slice($techSkills, 0, 4));
                $summaryParts[] = 'proficient in '.implode(', ', array_slice($techSkills, 0, 3));
            }
        }

        if ($assessmentScore >= 80) {
            $strengths[] = 'Exceptional assessment performance ('.$assessmentScore.'%)';
            $summaryParts[] = 'demonstrating top-tier assessment scores';
        } elseif ($assessmentScore >= 60) {
            $strengths[] = 'Solid assessment results ('.$assessmentScore.'%)';
        }

        $temperamentMap = [
            'analytical' => 'Analytical thinking and problem-solving approach',
            'expressive' => 'Strong communication and interpersonal skills',
            'amiable' => 'Collaborative and team-oriented mindset',
            'driver' => 'Results-driven and self-motivated attitude',
        ];

        if (isset($temperamentMap[$temperament])) {
            $strengths[] = $temperamentMap[$temperament];
            $summaryParts[] = 'with a '.$temperament.' temperament profile';
        }

        if ($assessmentScore < 60) {
            $risks[] = 'Below average assessment score - may need additional evaluation';
        }

        if ($yearsExp < 2) {
            $risks[] = 'Limited professional experience - consider junior-level roles';
        }

        $salaryExpectation = $profile?->salary_expectation;
        if ($salaryExpectation && $salaryExpectation > 150000) {
            $risks[] = 'High salary expectations may exceed market rate';
        }

        if (empty($skills)) {
            $risks[] = 'Limited technical skills documented';
        }

        if ($workPref === 'remote' || $workPref === 'hybrid') {
            $summaryParts[] = 'open to '.$workPref.' work arrangements';
        }

        $roleFit = $this->determineRoleFit($yearsExp, $assessmentScore, $skills);
        $recommendation = $this->generateRecommendation($yearsExp, $assessmentScore, $risks, $roleFit);

        return [
            'summary' => ucfirst($desiredRole).' with '.implode('. ', array_filter($summaryParts)).'.',
            'strengths' => array_slice($strengths, 0, 5),
            'risks' => array_slice($risks, 0, 4),
            'recommendation' => $recommendation,
            'role_fit' => $roleFit,
        ];
    }

    protected function determineRoleFit(int $yearsExp, int $assessmentScore, array $skills): string
    {
        if ($yearsExp >= 7 && $assessmentScore >= 75) {
            return 'Best suited for senior or lead positions in established teams';
        }

        if ($yearsExp >= 4 && $assessmentScore >= 70) {
            return 'Ideal for mid-level product teams or growing startups';
        }

        if ($yearsExp >= 2 && $assessmentScore >= 60) {
            return 'Good fit for mid-level roles with mentorship opportunities';
        }

        if ($yearsExp < 2) {
            return 'Best for entry-level or junior positions with training support';
        }

        return 'Suitable for various roles - recommend further evaluation';
    }

    protected function generateRecommendation(int $yearsExp, int $assessmentScore, array $risks, string $roleFit): string
    {
        $score = 0;

        if ($yearsExp >= 4) {
            $score += 2;
        } elseif ($yearsExp >= 2) {
            $score += 1;
        }

        if ($assessmentScore >= 80) {
            $score += 3;
        } elseif ($assessmentScore >= 70) {
            $score += 2;
        } elseif ($assessmentScore >= 60) {
            $score += 1;
        }

        if (count($risks) <= 1) {
            $score += 1;
        }
        if (count($risks) >= 3) {
            $score -= 1;
        }

        if ($score >= 6) {
            return 'Strong recommendation to hire. '.$roleFit.' Recommend proceeding with interview process.';
        }

        if ($score >= 4) {
            return 'Positive recommendation with notes. '.$roleFit.' Consider scheduling initial interview.';
        }

        if ($score >= 2) {
            return 'Neutral recommendation. '.$roleFit.' Additional evaluation recommended before proceeding.';
        }

        return 'Proceed with caution. '.$roleFit.' Recommend thorough review of profile and skills.';
    }

    public function shouldRegenerate(?CandidateProfile $profile): bool
    {
        if (! $profile) {
            return true;
        }

        if (! $profile->ai_last_generated_at) {
            return true;
        }

        $daysSinceLastGeneration = now()->diffInDays($profile->ai_last_generated_at);

        return $daysSinceLastGeneration >= 30;
    }
}
