<?php

namespace Database\Seeders;

use App\Models\PersonalityQuestion;
use App\Models\PersonalityQuestionOption;
use Illuminate\Database\Seeder;

class GridSpacePersonalityAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        PersonalityQuestionOption::query()->delete();
        PersonalityQuestion::query()->delete();

        $questions = $this->getQuestions();

        foreach ($questions as $index => $questionData) {
            $displayOrder = $index + 1;

            $question = PersonalityQuestion::create([
                'category' => $questionData['category'],
                'question_text' => $questionData['question_text'],
                'question_type' => 'single_choice',
                'is_active' => true,
                'display_order' => $displayOrder,
            ]);

            foreach ($questionData['options'] as $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['option_text'],
                    'option_value' => $optionData['option_value'] ?? 1,
                    'personality_dimension' => $optionData['personality_dimension'],
                    'weight' => $optionData['weight'],
                    'signal_key' => $optionData['personality_dimension'],
                    'signal_value' => $optionData['weight'],
                ]);
            }
        }

        $this->command->info('Seeded 40 GridSpace Personality Assessment V2 questions with options.');
    }

    private function getQuestions(): array
    {
        return [
            // WORK STYLE (Questions 1–5)
            [
                'category' => 'work_style',
                'question_text' => 'When starting a new project, what do you usually prefer?',
                'options' => [
                    ['option_text' => 'A detailed plan before beginning', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'A rough direction and flexibility', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Learning while doing', 'personality_dimension' => 'Initiative', 'weight' => 2],
                    ['option_text' => 'Team brainstorming first', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],
            [
                'category' => 'work_style',
                'question_text' => 'Which work environment feels most comfortable to you?',
                'options' => [
                    ['option_text' => 'Highly organized and structured', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Flexible and creative', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Fast-moving and energetic', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'Calm and predictable', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'work_style',
                'question_text' => 'How do you usually handle deadlines?',
                'options' => [
                    ['option_text' => 'Finish early whenever possible', 'personality_dimension' => 'Initiative', 'weight' => 3],
                    ['option_text' => 'Work steadily until completion', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Perform best under pressure', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Need reminders and checkpoints', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'work_style',
                'question_text' => 'What describes your ideal work pace?',
                'options' => [
                    ['option_text' => 'Fast and dynamic', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Balanced and steady', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Deep focus with fewer interruptions', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                    ['option_text' => 'Depends on the project', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'work_style',
                'question_text' => 'When facing a difficult task, what do you do first?',
                'options' => [
                    ['option_text' => 'Break it into steps', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Ask for ideas from others', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Start immediately and adjust later', 'personality_dimension' => 'Initiative', 'weight' => 2],
                    ['option_text' => 'Research thoroughly first', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],

            // COMMUNICATION STYLE (Questions 6–10)
            [
                'category' => 'communication_style',
                'question_text' => 'How would people describe your communication style?',
                'options' => [
                    ['option_text' => 'Direct and straightforward', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Friendly and expressive', 'personality_dimension' => 'Communication', 'weight' => 2],
                    ['option_text' => 'Calm and thoughtful', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Diplomatic and careful', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],
            [
                'category' => 'communication_style',
                'question_text' => 'During disagreements, you usually:',
                'options' => [
                    ['option_text' => 'Address issues immediately', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Try to keep peace', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Listen first before responding', 'personality_dimension' => 'Communication', 'weight' => 2],
                    ['option_text' => 'Avoid confrontation if possible', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],
            [
                'category' => 'communication_style',
                'question_text' => 'In meetings, you are more likely to:',
                'options' => [
                    ['option_text' => 'Lead the discussion', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Contribute actively', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Speak when necessary', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                    ['option_text' => 'Observe and analyze first', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],
            [
                'category' => 'communication_style',
                'question_text' => 'When explaining ideas, you prefer:',
                'options' => [
                    ['option_text' => 'Short and direct explanations', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Stories and examples', 'personality_dimension' => 'Communication', 'weight' => 2],
                    ['option_text' => 'Detailed breakdowns', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Visual demonstrations', 'personality_dimension' => 'Communication', 'weight' => 2],
                ],
            ],
            [
                'category' => 'communication_style',
                'question_text' => 'How comfortable are you giving feedback?',
                'options' => [
                    ['option_text' => 'Very comfortable', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Comfortable if necessary', 'personality_dimension' => 'Communication', 'weight' => 2],
                    ['option_text' => 'Prefer indirect feedback', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Prefer written feedback', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],

            // TEAM DYNAMICS (Questions 11–15)
            [
                'category' => 'team_dynamics',
                'question_text' => 'Which role do you naturally take in teams?',
                'options' => [
                    ['option_text' => 'Organizer', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Motivator', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Problem Solver', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                    ['option_text' => 'Supportive Contributor', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],
            [
                'category' => 'team_dynamics',
                'question_text' => 'What frustrates you most in team projects?',
                'options' => [
                    ['option_text' => 'Lack of accountability', 'personality_dimension' => 'Leadership', 'weight' => 2],
                    ['option_text' => 'Poor communication', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Slow decision-making', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'Unclear direction', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'team_dynamics',
                'question_text' => 'How do you react when a teammate underperforms?',
                'options' => [
                    ['option_text' => 'Address it directly', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Offer support first', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Inform leadership', 'personality_dimension' => 'Leadership', 'weight' => 2],
                    ['option_text' => 'Adjust and compensate quietly', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'team_dynamics',
                'question_text' => 'Which statement sounds most like you?',
                'options' => [
                    ['option_text' => 'I enjoy working independently', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'I enjoy strong collaboration', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'I like a balance of both', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'It depends on the project', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'team_dynamics',
                'question_text' => 'How important is team culture to you?',
                'options' => [
                    ['option_text' => 'Extremely important', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Important', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Somewhat important', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Not a major factor', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],

            // PROBLEM SOLVING (Questions 16–20)
            [
                'category' => 'problem_solving',
                'question_text' => 'When solving problems, you usually:',
                'options' => [
                    ['option_text' => 'Analyze deeply before acting', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Act quickly and adapt later', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Seek multiple opinions first', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Follow proven systems', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],
            [
                'category' => 'problem_solving',
                'question_text' => 'How do you handle unexpected changes?',
                'options' => [
                    ['option_text' => 'Adapt quickly', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Need time to adjust', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Prefer advance notice', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Depends on the situation', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'problem_solving',
                'question_text' => 'Which best describes your decision-making style?',
                'options' => [
                    ['option_text' => 'Logical and analytical', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Intuitive and instinctive', 'personality_dimension' => 'Initiative', 'weight' => 2],
                    ['option_text' => 'Collaborative', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Data-driven', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],
            [
                'category' => 'problem_solving',
                'question_text' => 'When pressure increases, you tend to:',
                'options' => [
                    ['option_text' => 'Become more focused', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Feel energized', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'Become cautious', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Seek support from others', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],
            [
                'category' => 'problem_solving',
                'question_text' => 'How comfortable are you with risk?',
                'options' => [
                    ['option_text' => 'Very comfortable', 'personality_dimension' => 'Initiative', 'weight' => 3],
                    ['option_text' => 'Moderately comfortable', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'Only calculated risks', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Prefer stability', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],

            // LEADERSHIP & INITIATIVE (Questions 21–25)
            [
                'category' => 'leadership_initiative',
                'question_text' => 'When you notice a problem at work, you usually:',
                'options' => [
                    ['option_text' => 'Take initiative immediately', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Inform others first', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Wait for direction', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Observe before acting', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],
            [
                'category' => 'leadership_initiative',
                'question_text' => 'How do you feel about leadership responsibilities?',
                'options' => [
                    ['option_text' => 'I naturally enjoy leading', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'I can lead when needed', 'personality_dimension' => 'Leadership', 'weight' => 2],
                    ['option_text' => 'I prefer supporting roles', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Depends on the team', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'leadership_initiative',
                'question_text' => 'What motivates you most in projects?',
                'options' => [
                    ['option_text' => 'Achieving goals', 'personality_dimension' => 'Initiative', 'weight' => 3],
                    ['option_text' => 'Recognition', 'personality_dimension' => 'Communication', 'weight' => 2],
                    ['option_text' => 'Learning and growth', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Team success', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],
            [
                'category' => 'leadership_initiative',
                'question_text' => 'When working without supervision, you:',
                'options' => [
                    ['option_text' => 'Stay highly productive', 'personality_dimension' => 'Initiative', 'weight' => 3],
                    ['option_text' => 'Need occasional check-ins', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Prefer structure and monitoring', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Work best with deadlines', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'leadership_initiative',
                'question_text' => 'Which sounds most like you?',
                'options' => [
                    ['option_text' => 'I enjoy making decisions', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'I prefer collaboration before decisions', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'I prefer clear instructions', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'I avoid unnecessary responsibility', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],

            // WORK ENVIRONMENT PREFERENCE (Questions 26–30)
            [
                'category' => 'environment_preference',
                'question_text' => 'What type of company environment excites you most?',
                'options' => [
                    ['option_text' => 'Startup', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Corporate organization', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Mission-driven company', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Flexible small team', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'environment_preference',
                'question_text' => 'What work setup do you prefer?',
                'options' => [
                    ['option_text' => 'Fully remote', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'Hybrid', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Onsite', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Flexible depending on role', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'environment_preference',
                'question_text' => 'What type of management style do you prefer?',
                'options' => [
                    ['option_text' => 'Hands-on guidance', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Freedom and independence', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Collaborative leadership', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Structured supervision', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'environment_preference',
                'question_text' => 'Which environment helps you perform best?',
                'options' => [
                    ['option_text' => 'Fast-paced', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Highly organized', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Creative and flexible', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'Calm and predictable', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'environment_preference',
                'question_text' => 'How important is career growth opportunity?',
                'options' => [
                    ['option_text' => 'Extremely important', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Important', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Somewhat important', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Less important than stability', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],

            // MOTIVATION DRIVERS (Questions 31–35)
            [
                'category' => 'motivation',
                'question_text' => 'What motivates you most professionally?',
                'options' => [
                    ['option_text' => 'Financial growth', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Career advancement', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Meaningful impact', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Work-life balance', 'personality_dimension' => 'Stability', 'weight' => 3],
                ],
            ],
            [
                'category' => 'motivation',
                'question_text' => 'What makes you stay long-term at a company?',
                'options' => [
                    ['option_text' => 'Good compensation', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Great leadership', 'personality_dimension' => 'Leadership', 'weight' => 2],
                    ['option_text' => 'Growth opportunities', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Positive culture', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                ],
            ],
            [
                'category' => 'motivation',
                'question_text' => 'Which achievement would make you happiest?',
                'options' => [
                    ['option_text' => 'Promotion', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Building something impactful', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Recognition from peers', 'personality_dimension' => 'Communication', 'weight' => 2],
                    ['option_text' => 'Personal growth', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                ],
            ],
            [
                'category' => 'motivation',
                'question_text' => 'What matters most when choosing a new role?',
                'options' => [
                    ['option_text' => 'Salary', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Growth potential', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Team culture', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Flexibility', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'motivation',
                'question_text' => 'Which statement sounds most like you?',
                'options' => [
                    ['option_text' => 'I enjoy competition', 'personality_dimension' => 'Initiative', 'weight' => 3],
                    ['option_text' => 'I enjoy collaboration', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'I value stability', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'I value freedom', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],

            // TEMPERAMENT INDICATORS (Questions 36–40)
            [
                'category' => 'temperament',
                'question_text' => 'How do you usually respond under pressure?',
                'options' => [
                    ['option_text' => 'Take charge quickly', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Stay calm and steady', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Become highly analytical', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Energize and motivate others', 'personality_dimension' => 'Communication', 'weight' => 2],
                ],
            ],
            [
                'category' => 'temperament',
                'question_text' => 'What describes your natural energy level at work?',
                'options' => [
                    ['option_text' => 'High-energy and expressive', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Calm and controlled', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Focused and analytical', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Driven and intense', 'personality_dimension' => 'Initiative', 'weight' => 2],
                ],
            ],
            [
                'category' => 'temperament',
                'question_text' => 'How do you usually make decisions?',
                'options' => [
                    ['option_text' => 'Quickly and confidently', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Carefully and logically', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Based on relationships', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Based on experience', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],
            [
                'category' => 'temperament',
                'question_text' => 'What role do you naturally play during stressful situations?',
                'options' => [
                    ['option_text' => 'Leader', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Mediator', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Planner', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                    ['option_text' => 'Supporter', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],
            [
                'category' => 'temperament',
                'question_text' => 'Which best describes your personality at work?',
                'options' => [
                    ['option_text' => 'Energetic', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Calm', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Analytical', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Ambitious', 'personality_dimension' => 'Initiative', 'weight' => 2],
                ],
            ],
        ];
    }
}
