<?php

namespace App\Services;

use App\Models\CandidatePersonalityProfile;
use App\Models\PersonalityAnswer;
use App\Models\User;

class PersonalityScoringService
{
    private const TEMPERAMENT_MAPPINGS = [
        'analytical' => 'Analytical',
        'energetic' => 'Energetic',
        'calm' => 'Calm',
        'decisive' => 'Decisive',
    ];

    public function generateProfile(User $candidate): CandidatePersonalityProfile
    {
        $answers = PersonalityAnswer::where('candidate_id', $candidate->id)
            ->with(['question', 'selectedOption'])
            ->get();

        $signals = $this->aggregateSignals($answers);
        $profile = $this->calculateProfile($signals);
        $summary = $this->generateSummary($profile);
        $strengths = $this->generateStrengths($profile);

        return CandidatePersonalityProfile::updateOrCreate(
            ['candidate_id' => $candidate->id],
            array_merge($profile, [
                'personality_summary' => $summary['personality'],
                'work_style_summary' => $summary['work_style'],
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

    private function aggregateSignals($answers): array
    {
        $signals = [];

        foreach ($answers as $answer) {
            $option = $answer->selectedOption;
            $category = $answer->question->category;

            if (! isset($signals[$category])) {
                $signals[$category] = [];
            }

            $key = $option->signal_key;
            $value = $option->signal_value;

            if (! isset($signals[$category][$key])) {
                $signals[$category][$key] = 0;
            }

            $signals[$category][$key] += $value;
        }

        return $signals;
    }

    private function calculateProfile(array $signals): array
    {
        return [
            'work_style' => $this->determineWorkStyle($signals['work_style'] ?? []),
            'communication_style' => $this->determineCommunicationStyle($signals['communication_style'] ?? []),
            'collaboration_style' => $this->determineCollaborationStyle($signals['team_dynamics'] ?? []),
            'leadership_style' => $this->determineLeadershipStyle($signals['leadership'] ?? []),
            'motivation_type' => $this->determineMotivationType($signals['motivation'] ?? []),
            'temperament_type' => $this->determineTemperamentType($signals),
            'organizational_fit' => $this->determineOrganizationalFit($signals['environment_preference'] ?? []),
        ];
    }

    private function determineWorkStyle(array $signals): string
    {
        $structured = ($signals['structured'] ?? 0) - ($signals['flexible'] ?? 0);
        $pace = ($signals['fast_paced'] ?? 0) - ($signals['steady_paced'] ?? 0);

        if ($structured > 2 && $pace > 0) {
            return 'Structured and Fast-Paced';
        }

        if ($structured > 2) {
            return 'Structured and Methodical';
        }

        if ($structured < -2) {
            return 'Flexible and Adaptive';
        }

        if ($pace > 2) {
            return 'Energetic and Fast-Paced';
        }

        if ($pace < -2) {
            return 'Steady and Consistent';
        }

        return 'Balanced and Versatile';
    }

    private function determineCommunicationStyle(array $signals): string
    {
        $directness = ($signals['direct'] ?? 0) - ($signals['diplomatic'] ?? 0);
        $expression = ($signals['expressive'] ?? 0) - ($signals['reserved'] ?? 0);

        if ($directness > 2 && $expression > 0) {
            return 'Direct and Expressive';
        }

        if ($directness > 2) {
            return 'Direct and Concise';
        }

        if ($directness < -2) {
            return 'Diplomatic and Tactful';
        }

        if ($expression > 2) {
            return 'Expressive and Engaging';
        }

        if ($expression < -2) {
            return 'Reserved and Thoughtful';
        }

        return 'Balanced Communicator';
    }

    private function determineCollaborationStyle(array $signals): string
    {
        $independence = ($signals['independent'] ?? 0) - ($signals['collaborative'] ?? 0);

        if ($independence > 2) {
            return 'Independent Contributor';
        }

        if ($independence < -2) {
            return 'Highly Collaborative';
        }

        if (($signals['supportive'] ?? 0) > ($signals['directive'] ?? 0)) {
            return 'Supportive Team Player';
        }

        return 'Balanced Collaborator';
    }

    private function determineLeadershipStyle(array $signals): string
    {
        $leading = ($signals['leader'] ?? 0) - ($signals['supporter'] ?? 0);

        if ($leading > 2) {
            return 'Takes Initiative and Leads';
        }

        if ($leading > 0) {
            return 'Emerging Leader';
        }

        if ($leading < -2) {
            return 'Supportive Contributor';
        }

        return 'Collaborative Participant';
    }

    private function determineMotivationType(array $signals): string
    {
        $types = [
            'growth' => ($signals['growth'] ?? 0) + ($signals['learning'] ?? 0),
            'impact' => ($signals['impact'] ?? 0) + ($signals['purpose'] ?? 0),
            'achievement' => ($signals['achievement'] ?? 0) + ($signals['challenge'] ?? 0),
            'stability' => ($signals['stability'] ?? 0) + ($signals['security'] ?? 0),
            'recognition' => ($signals['recognition'] ?? 0) + ($signals['advancement'] ?? 0),
        ];

        arsort($types);
        $top = array_key_first($types);

        return match ($top) {
            'growth' => 'Growth and Learning Driven',
            'impact' => 'Purpose and Impact Driven',
            'achievement' => 'Achievement and Challenge Driven',
            'stability' => 'Stability and Security Oriented',
            'recognition' => 'Recognition and Advancement Driven',
            default => 'Balanced Motivation',
        };
    }

    private function determineTemperamentType(array $signals): string
    {
        $scores = [
            'analytical' => ($signals['problem_solving']['analytical'] ?? 0) + ($signals['work_style']['structured'] ?? 0),
            'energetic' => ($signals['work_style']['fast_paced'] ?? 0) + ($signals['team_dynamics']['energizer'] ?? 0) + ($signals['communication_style']['expressive'] ?? 0),
            'calm' => ($signals['temperament']['calm'] ?? 0) + ($signals['work_style']['steady_paced'] ?? 0) + ($signals['communication_style']['diplomatic'] ?? 0),
            'decisive' => ($signals['leadership']['leader'] ?? 0) + ($signals['problem_solving']['decisive'] ?? 0) + ($signals['communication_style']['direct'] ?? 0),
        ];

        arsort($scores);
        $top = array_key_first($scores);

        return self::TEMPERAMENT_MAPPINGS[$top] ?? 'Balanced';
    }

    private function determineOrganizationalFit(array $signals): string
    {
        $startup = ($signals['startup'] ?? 0) + ($signals['autonomous'] ?? 0) + ($signals['fast_paced'] ?? 0);
        $corporate = ($signals['corporate'] ?? 0) + ($signals['structured_environment'] ?? 0) + ($signals['hierarchical'] ?? 0);
        $nonprofit = ($signals['mission_driven'] ?? 0) + ($signals['purpose_driven'] ?? 0);
        $remote = ($signals['remote_work'] ?? 0) + ($signals['independent_work'] ?? 0);

        $fits = compact('startup', 'corporate', 'nonprofit', 'remote');
        arsort($fits);
        $top = array_key_first($fits);

        return match ($top) {
            'startup' => 'Startup or Dynamic Environment',
            'corporate' => 'Structured Corporate Environment',
            'nonprofit' => 'Mission-Driven Organization',
            'remote' => 'Remote-First Organization',
            default => 'Adaptable to Various Environments',
        };
    }

    private function generateSummary(array $profile): array
    {
        $workStyleTemplates = [
            'Structured and Fast-Paced' => 'You thrive in environments with clear structure and momentum, where you can move quickly while maintaining organization.',
            'Structured and Methodical' => 'You prefer well-defined processes and methodical approaches, excelling in environments where precision and planning are valued.',
            'Flexible and Adaptive' => 'You adapt easily to changing circumstances and enjoy the freedom to pivot when new opportunities or challenges arise.',
            'Energetic and Fast-Paced' => 'You bring energy and enthusiasm to your work, flourishing in dynamic environments where things move quickly.',
            'Steady and Consistent' => 'You value consistency and reliability, providing a stable presence that teams can count on.',
            'Balanced and Versatile' => 'You balance structure with flexibility, adapting your approach based on what the situation demands.',
        ];

        $personalityTemplates = [
            'Structured and Fast-Paced' => 'You are a proactive professional who values both organization and momentum. You work best when there are clear goals and the autonomy to achieve them efficiently.',
            'Structured and Methodical' => 'You are a thoughtful and precise professional who values quality and thoroughness. Your methodical approach ensures reliable, high-quality results.',
            'Flexible and Adaptive' => 'You are a versatile professional who thrives on change and variety. Your adaptability makes you effective in evolving environments.',
            'Energetic and Fast-Paced' => 'You are a dynamic professional who brings positive energy and enthusiasm to every project. You excel in fast-moving, collaborative settings.',
            'Steady and Consistent' => 'You are a dependable professional who provides stability and consistency. Your steady approach builds trust and reliability within teams.',
            'Balanced and Versatile' => 'You are a well-rounded professional who adapts naturally to different situations. Your balanced approach makes you effective across diverse environments.',
        ];

        $style = $profile['work_style'];

        return [
            'personality' => $personalityTemplates[$style] ?? 'You are a well-rounded professional with a balanced approach to work.',
            'work_style' => $workStyleTemplates[$style] ?? 'You have a versatile work style that adapts to different environments.',
        ];
    }

    private function generateStrengths(array $profile): string
    {
        $strengths = [];

        $strengthMap = [
            'Structured and Fast-Paced' => ['Goal-oriented execution', 'Time management', 'Efficiency under pressure'],
            'Structured and Methodical' => ['Attention to detail', 'Strategic planning', 'Quality assurance'],
            'Flexible and Adaptive' => ['Adaptability', 'Creative problem-solving', 'Quick learning'],
            'Energetic and Fast-Paced' => ['High energy', 'Initiative', 'Team motivation'],
            'Steady and Consistent' => ['Reliability', 'Consistent quality', 'Long-term focus'],
            'Balanced and Versatile' => ['Versatility', 'Balanced judgment', 'Cross-functional collaboration'],
        ];

        $styleStrengths = $strengthMap[$profile['work_style']] ?? [];
        $strengths = array_merge($strengths, $styleStrengths);

        $commStrengths = [
            'Direct and Expressive' => ['Clear communication', 'Persuasive presentation', 'Stakeholder engagement'],
            'Direct and Concise' => ['Efficient communication', 'Clarity of thought', 'Focused delivery'],
            'Diplomatic and Tactful' => ['Active listening', 'Conflict resolution', 'Empathy'],
            'Expressive and Engaging' => ['Public speaking', 'Team communication', 'Relationship building'],
            'Reserved and Thoughtful' => ['Thoughtful analysis', 'Written communication', 'Deep listening'],
            'Balanced Communicator' => ['Effective dialogue', 'Audience awareness', 'Clear articulation'],
        ];

        $commResult = $commStrengths[$profile['communication_style']] ?? [];
        $strengths = array_merge($strengths, $commResult);

        return implode(', ', array_unique($strengths));
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

        return [
            'compatibility_score' => $profile->temperament_type ? $this->calculateBaseCompatibility($profile) : null,
            'work_style_summary' => $profile->work_style_summary,
            'communication_summary' => $profile->communication_style,
            'organizational_fit_summary' => $profile->organizational_fit,
        ];
    }

    private function calculateBaseCompatibility(CandidatePersonalityProfile $profile): string
    {
        return match ($profile->temperament_type) {
            'Analytical' => 'Detail-oriented and systematic',
            'Energetic' => 'Dynamic and engaging',
            'Calm' => 'Steady and composed',
            'Decisive' => 'Action-oriented and confident',
            default => 'Versatile and adaptive',
        };
    }
}
