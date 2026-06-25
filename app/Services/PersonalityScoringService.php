<?php

namespace App\Services;

use App\Models\CandidatePersonalityProfile;
use App\Models\PersonalityAnswer;
use App\Models\PersonalityQuestion;
use App\Models\User;

class PersonalityScoringService
{
    public const DIMENSIONS = [
        'Leadership',
        'Collaboration',
        'Adaptability',
        'Analytical Thinking',
        'Communication',
        'Initiative',
        'Stability',
        'Growth Orientation',
    ];

    public function generateProfile(User $candidate): CandidatePersonalityProfile
    {
        $answers = PersonalityAnswer::where('candidate_id', $candidate->id)
            ->with(['selectedOption'])
            ->get();

        $rawScores = $this->calculateRawDimensionScores($answers);
        $maxScores = $this->getMaxDimensionScores();
        $dimensionScores = $this->normalizeScores($rawScores, $maxScores);
        $dominantTraits = $this->determineDominantTraits($dimensionScores);
        $workplaceCompatibility = $this->generateWorkplaceCompatibility($dimensionScores, $dominantTraits);
        $summaries = $this->generateSummaries($dimensionScores, $dominantTraits);
        $strengths = $this->generateStrengths($dominantTraits, $dimensionScores);

        $legacyProfile = $this->mapToLegacyProfile($dimensionScores, $dominantTraits);

        return CandidatePersonalityProfile::updateOrCreate(
            ['candidate_id' => $candidate->id],
            array_merge($legacyProfile, [
                'dimension_scores' => $dimensionScores,
                'dominant_traits' => $dominantTraits,
                'workplace_compatibility' => $workplaceCompatibility,
                'personality_summary' => $summaries['personality'],
                'work_style_summary' => $summaries['work_style'],
                'strengths_summary' => $strengths,
                'assessment_completed' => true,
                'completed_at' => now(),
            ])
        );
    }

    public function calculateProfileScores(User $candidate): array
    {
        $profile = $candidate->personalityProfile;

        if (! $profile) {
            return [
                'work_style' => 'Not assessed',
                'communication_style' => 'Not assessed',
                'collaboration_style' => 'Not assessed',
                'leadership_style' => 'Not assessed',
                'organizational_fit' => 'Not assessed',
            ];
        }

        return $profile->only([
            'work_style',
            'communication_style',
            'collaboration_style',
            'leadership_style',
            'organizational_fit',
        ]);
    }

    public function getProfileSummaryForEmployer(User $candidate): array
    {
        $profile = $candidate->personalityProfile;

        if (! $profile || ! $profile->assessment_completed) {
            return [
                'compatibility_score' => null,
                'work_style_summary' => 'Assessment not yet completed.',
                'communication_summary' => null,
                'organizational_fit_summary' => null,
            ];
        }

        $dominant = $profile->dominant_traits;
        $primaryTrait = ! empty($dominant) ? $dominant[0] : 'Versatile';

        return [
            'compatibility_score' => $this->calculateBaseCompatibility($primaryTrait),
            'work_style_summary' => $profile->work_style_summary,
            'communication_summary' => $profile->communication_style,
            'organizational_fit_summary' => $profile->organizational_fit,
        ];
    }

    private function calculateRawDimensionScores($answers): array
    {
        $scores = [];

        foreach (self::DIMENSIONS as $dimension) {
            $scores[$dimension] = 0;
        }

        foreach ($answers as $answer) {
            $option = $answer->selectedOption;

            if (! $option || ! $option->personality_dimension) {
                continue;
            }

            $dimension = $option->personality_dimension;
            $weight = $option->weight ?? 1;

            if (isset($scores[$dimension])) {
                $scores[$dimension] += $weight;
            }
        }

        return $scores;
    }

    private function getMaxDimensionScores(): array
    {
        $maxScores = [];

        foreach (self::DIMENSIONS as $dimension) {
            $maxScores[$dimension] = PersonalityQuestion::active()
                ->whereHas('options', function ($q) use ($dimension) {
                    $q->where('personality_dimension', $dimension);
                })
                ->with(['options' => function ($q) use ($dimension) {
                    $q->where('personality_dimension', $dimension);
                }])
                ->get()
                ->sum(function ($question) {
                    return $question->options->max('weight') ?? 1;
                });
        }

        return $maxScores;
    }

    private function normalizeScores(array $rawScores, array $maxScores): array
    {
        $normalized = [];

        foreach (self::DIMENSIONS as $dimension) {
            $max = $maxScores[$dimension] ?? 1;
            $normalized[$dimension] = (int) round(($rawScores[$dimension] / $max) * 100);
            $normalized[$dimension] = min(100, max(0, $normalized[$dimension]));
        }

        return $normalized;
    }

    private function determineDominantTraits(array $dimensionScores): array
    {
        $threshold = 70;
        $traits = [];

        arsort($dimensionScores);

        foreach ($dimensionScores as $dimension => $score) {
            if ($score >= $threshold) {
                $traits[] = $dimension;
            }
        }

        if (empty($traits)) {
            $traits[] = array_key_first($dimensionScores);
        }

        return $traits;
    }

    private function generateWorkplaceCompatibility(array $dimensionScores, array $dominantTraits): string
    {
        $compatibilities = [];

        if (in_array('Leadership', $dominantTraits)) {
            $compatibilities[] = 'Thrives in roles requiring decision-making and team direction.';
        }

        if (in_array('Collaboration', $dominantTraits)) {
            $compatibilities[] = 'Excels in team-based environments with open communication.';
        }

        if (in_array('Adaptability', $dominantTraits)) {
            $compatibilities[] = 'Performs well in dynamic, fast-changing environments.';
        }

        if (in_array('Analytical Thinking', $dominantTraits)) {
            $compatibilities[] = 'Ideal for data-driven roles requiring deep analysis and structured problem-solving.';
        }

        if (in_array('Communication', $dominantTraits)) {
            $compatibilities[] = 'Strong fit for roles requiring clear and frequent communication.';
        }

        if (in_array('Initiative', $dominantTraits)) {
            $compatibilities[] = 'Self-starter who excels with autonomy and ownership.';
        }

        if (in_array('Stability', $dominantTraits)) {
            $compatibilities[] = 'Provides reliable, consistent performance in structured settings.';
        }

        if (in_array('Growth Orientation', $dominantTraits)) {
            $compatibilities[] = 'Driven by learning opportunities and career progression.';
        }

        if (empty($compatibilities)) {
            return 'Versatile professional adaptable to various workplace environments.';
        }

        return implode(' ', $compatibilities);
    }

    private function generateSummaries(array $dimensionScores, array $dominantTraits): array
    {
        $primary = $dominantTraits[0] ?? 'Balanced';
        $secondary = $dominantTraits[1] ?? null;

        $personalityTemplates = [
            'Leadership' => 'You are a natural leader who takes charge, makes decisions confidently, and inspires others to achieve shared goals.',
            'Collaboration' => 'You are a collaborative professional who values teamwork, builds strong relationships, and thrives in inclusive environments.',
            'Adaptability' => 'You are a highly adaptable professional who embraces change, navigates uncertainty with ease, and finds creative solutions in dynamic settings.',
            'Analytical Thinking' => 'You are a thoughtful, analytical thinker who approaches problems systematically, values data-driven decisions, and delivers thorough solutions.',
            'Communication' => 'You are an expressive communicator who articulates ideas clearly, engages others effectively, and fosters open dialogue.',
            'Initiative' => 'You are a proactive self-starter who takes ownership, drives results, and excels in autonomous environments.',
            'Stability' => 'You are a steady, reliable professional who brings consistency, dependability, and composure to every team.',
            'Growth Orientation' => 'You are a growth-oriented professional driven by continuous learning, career development, and meaningful impact.',
        ];

        $workStyleTemplates = [
            'Leadership' => 'You prefer environments where you can take initiative, lead projects, and drive decision-making.',
            'Collaboration' => 'You work best in collaborative settings where teamwork, open communication, and shared success are prioritized.',
            'Adaptability' => 'You thrive in flexible, fast-paced environments that require adaptability and creative problem-solving.',
            'Analytical Thinking' => 'You excel in structured environments that value thorough analysis, strategic planning, and methodical execution.',
            'Communication' => 'You flourish in communicative environments where ideas are shared freely and collaboration is encouraged.',
            'Initiative' => 'You perform best when given autonomy, ownership, and the freedom to drive your own projects.',
            'Stability' => 'You prefer stable, well-defined environments with clear expectations and consistent routines.',
            'Growth Orientation' => 'You are most engaged in environments that offer learning opportunities, career growth, and meaningful challenges.',
        ];

        $personality = $personalityTemplates[$primary]
            ?? 'You are a well-rounded professional with a balanced approach to work.';

        if ($secondary && isset($personalityTemplates[$secondary])) {
            $personality .= ' '.$personalityTemplates[$secondary];
        }

        return [
            'personality' => $personality,
            'work_style' => $workStyleTemplates[$primary]
                ?? 'You have a versatile work style that adapts to different environments.',
        ];
    }

    private function generateStrengths(array $dominantTraits, array $dimensionScores): string
    {
        $strengthMap = [
            'Leadership' => ['Strategic decision-making', 'Team direction', 'Conflict resolution', 'Vision setting'],
            'Collaboration' => ['Team collaboration', 'Relationship building', 'Active listening', 'Empathy'],
            'Adaptability' => ['Flexibility', 'Creative problem-solving', 'Quick learning', 'Resilience'],
            'Analytical Thinking' => ['Critical thinking', 'Data analysis', 'Strategic planning', 'Attention to detail'],
            'Communication' => ['Clear articulation', 'Presentation skills', 'Persuasive writing', 'Stakeholder engagement'],
            'Initiative' => ['Proactivity', 'Self-motivation', 'Goal orientation', 'Ownership'],
            'Stability' => ['Reliability', 'Consistency', 'Composure under pressure', 'Dependability'],
            'Growth Orientation' => ['Continuous learning', 'Ambition', 'Adaptability to feedback', 'Career focus'],
        ];

        $strengths = [];

        arsort($dimensionScores);

        foreach ($dimensionScores as $dimension => $score) {
            if ($score >= 60 && isset($strengthMap[$dimension])) {
                $top = $strengthMap[$dimension][0] ?? null;
                if ($top && ! in_array($top, $strengths)) {
                    $strengths[] = $top;
                }
            }
            if (count($strengths) >= 5) {
                break;
            }
        }

        if (empty($strengths)) {
            return 'Versatility, Adaptability, Professionalism';
        }

        return implode(', ', array_slice($strengths, 0, 5));
    }

    private function mapToLegacyProfile(array $dimensionScores, array $dominantTraits): array
    {
        $primary = $dominantTraits[0] ?? 'Balanced';

        $workStyleMap = [
            'Leadership' => 'Takes Initiative and Leads',
            'Adaptability' => 'Flexible and Adaptive',
            'Analytical Thinking' => 'Structured and Methodical',
            'Communication' => 'Expressive and Engaging',
            'Initiative' => 'Energetic and Fast-Paced',
            'Stability' => 'Steady and Consistent',
            'Collaboration' => 'Balanced and Versatile',
            'Growth Orientation' => 'Balanced and Versatile',
        ];

        $communicationMap = [
            'Communication' => 'Direct and Expressive',
            'Collaboration' => 'Diplomatic and Tactful',
            'Analytical Thinking' => 'Reserved and Thoughtful',
            'Leadership' => 'Direct and Concise',
        ];

        $collaborationMap = [
            'Collaboration' => 'Highly Collaborative',
            'Leadership' => 'Balanced Collaborator',
            'Stability' => 'Independent Contributor',
            'Initiative' => 'Independent Contributor',
        ];

        $leadershipMap = [
            'Leadership' => 'Takes Initiative and Leads',
            'Initiative' => 'Emerging Leader',
            'Growth Orientation' => 'Emerging Leader',
            'Adaptability' => 'Collaborative Participant',
        ];

        $motivationMap = [
            'Growth Orientation' => 'Growth and Learning Driven',
            'Leadership' => 'Achievement and Challenge Driven',
            'Initiative' => 'Achievement and Challenge Driven',
            'Collaboration' => 'Purpose and Impact Driven',
            'Stability' => 'Stability and Security Oriented',
        ];

        $temperamentMap = [
            'Leadership' => 'Decisive',
            'Analytical Thinking' => 'Analytical',
            'Communication' => 'Energetic',
            'Stability' => 'Calm',
        ];

        $fitMap = [
            'Adaptability' => 'Startup or Dynamic Environment',
            'Stability' => 'Structured Corporate Environment',
            'Growth Orientation' => 'Mission-Driven Organization',
            'Collaboration' => 'Remote-First Organization',
            'Initiative' => 'Startup or Dynamic Environment',
        ];

        return [
            'work_style' => $workStyleMap[$primary] ?? 'Balanced and Versatile',
            'communication_style' => $communicationMap[$primary] ?? 'Balanced Communicator',
            'collaboration_style' => $collaborationMap[$primary] ?? 'Supportive Team Player',
            'leadership_style' => $leadershipMap[$primary] ?? 'Collaborative Participant',
            'motivation_type' => $motivationMap[$primary] ?? 'Balanced Motivation',
            'temperament_type' => $temperamentMap[$primary] ?? 'Balanced',
            'organizational_fit' => $fitMap[$primary] ?? 'Adaptable to Various Environments',
        ];
    }

    private function calculateBaseCompatibility(string $primaryTrait): string
    {
        return match ($primaryTrait) {
            'Analytical Thinking' => 'Detail-oriented and systematic',
            'Communication' => 'Dynamic and engaging',
            'Stability' => 'Steady and composed',
            'Leadership' => 'Action-oriented and confident',
            default => 'Versatile and adaptive',
        };
    }
}
