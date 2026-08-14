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

        $this->command->info('Seeded 27 GridSpace Concise Personality & Matching Assessment questions with options.');
    }

    private function getQuestions(): array
    {
        return [
            // WORK STYLE (Questions 1–3)
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
                'question_text' => 'When facing a difficult task, what do you do first?',
                'options' => [
                    ['option_text' => 'Break it into steps', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Ask for ideas from others', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Start immediately and adjust later', 'personality_dimension' => 'Initiative', 'weight' => 2],
                    ['option_text' => 'Research thoroughly first', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],

            // COMMUNICATION STYLE (Questions 4–6)
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
                'question_text' => 'How comfortable are you giving feedback?',
                'options' => [
                    ['option_text' => 'Very comfortable', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Comfortable if necessary', 'personality_dimension' => 'Communication', 'weight' => 2],
                    ['option_text' => 'Prefer indirect feedback', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Prefer written feedback', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                ],
            ],

            // TEAM DYNAMICS (Questions 7–9)
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

            // PROBLEM SOLVING (Questions 10–12)
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

            // LEADERSHIP & INITIATIVE (Questions 13–15)
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
                'question_text' => 'When working without supervision, you:',
                'options' => [
                    ['option_text' => 'Stay highly productive', 'personality_dimension' => 'Initiative', 'weight' => 3],
                    ['option_text' => 'Need occasional check-ins', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Prefer structure and monitoring', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Work best with deadlines', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],

            // WORK ENVIRONMENT PREFERENCE (Questions 16–18)
            [
                'category' => 'work_environment_preference',
                'question_text' => 'What type of company environment excites you most?',
                'options' => [
                    ['option_text' => 'Startup', 'personality_dimension' => 'Adaptability', 'weight' => 3],
                    ['option_text' => 'Corporate organization', 'personality_dimension' => 'Stability', 'weight' => 2],
                    ['option_text' => 'Mission-driven company', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Flexible small team', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'work_environment_preference',
                'question_text' => 'What work setup do you prefer?',
                'options' => [
                    ['option_text' => 'Fully remote', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                    ['option_text' => 'Hybrid', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Onsite', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Flexible depending on role', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],
            [
                'category' => 'work_environment_preference',
                'question_text' => 'What type of management style do you prefer?',
                'options' => [
                    ['option_text' => 'Hands-on guidance', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Freedom and independence', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Collaborative leadership', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Structured supervision', 'personality_dimension' => 'Stability', 'weight' => 2],
                ],
            ],

            // MOTIVATION DRIVERS (Questions 19–21)
            [
                'category' => 'motivation_drivers',
                'question_text' => 'What motivates you most professionally?',
                'options' => [
                    ['option_text' => 'Financial growth', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Career advancement', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Meaningful impact', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Work-life balance', 'personality_dimension' => 'Stability', 'weight' => 3],
                ],
            ],
            [
                'category' => 'motivation_drivers',
                'question_text' => 'What makes you stay long-term at a company?',
                'options' => [
                    ['option_text' => 'Good compensation', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Great leadership', 'personality_dimension' => 'Leadership', 'weight' => 2],
                    ['option_text' => 'Growth opportunities', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Positive culture', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                ],
            ],
            [
                'category' => 'motivation_drivers',
                'question_text' => 'What matters most when choosing a new role?',
                'options' => [
                    ['option_text' => 'Salary', 'personality_dimension' => 'Growth Orientation', 'weight' => 3],
                    ['option_text' => 'Growth potential', 'personality_dimension' => 'Growth Orientation', 'weight' => 2],
                    ['option_text' => 'Team culture', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                    ['option_text' => 'Flexibility', 'personality_dimension' => 'Adaptability', 'weight' => 2],
                ],
            ],

            // TEMPERAMENT INDICATORS (Questions 22–24)
            [
                'category' => 'temperament_indicators',
                'question_text' => 'How do you usually respond under pressure?',
                'options' => [
                    ['option_text' => 'Take charge quickly', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Stay calm and steady', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Become highly analytical', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Energize and motivate others', 'personality_dimension' => 'Communication', 'weight' => 2],
                ],
            ],
            [
                'category' => 'temperament_indicators',
                'question_text' => 'What describes your natural energy level at work?',
                'options' => [
                    ['option_text' => 'High-energy and expressive', 'personality_dimension' => 'Communication', 'weight' => 3],
                    ['option_text' => 'Calm and controlled', 'personality_dimension' => 'Stability', 'weight' => 3],
                    ['option_text' => 'Focused and analytical', 'personality_dimension' => 'Analytical Thinking', 'weight' => 3],
                    ['option_text' => 'Driven and intense', 'personality_dimension' => 'Initiative', 'weight' => 2],
                ],
            ],
            [
                'category' => 'temperament_indicators',
                'question_text' => 'What role do you naturally play during stressful situations?',
                'options' => [
                    ['option_text' => 'Leader', 'personality_dimension' => 'Leadership', 'weight' => 3],
                    ['option_text' => 'Mediator', 'personality_dimension' => 'Collaboration', 'weight' => 3],
                    ['option_text' => 'Planner', 'personality_dimension' => 'Analytical Thinking', 'weight' => 2],
                    ['option_text' => 'Supporter', 'personality_dimension' => 'Collaboration', 'weight' => 2],
                ],
            ],

            // ORGANIZATIONAL CULTURE (Employer Questions 25–27)
            [
                'category' => 'organizational_culture',
                'question_text' => 'What kind of work environment best describes your company?',
                'options' => [
                    ['option_text' => 'Fast-paced startup', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Structured corporate', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Creative and flexible', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Mission-driven collaborative', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                ],
            ],
            [
                'category' => 'organizational_culture',
                'question_text' => 'What type of employees perform best in your organization?',
                'options' => [
                    ['option_text' => 'Independent self-starters', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Team-oriented collaborators', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Highly analytical thinkers', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Process-driven professionals', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                ],
            ],
            [
                'category' => 'organizational_culture',
                'question_text' => 'What communication style works best in your workplace?',
                'options' => [
                    ['option_text' => 'Direct and fast', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Diplomatic and thoughtful', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Collaborative and open', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                    ['option_text' => 'Structured and professional', 'personality_dimension' => 'Organizational Culture', 'weight' => 3],
                ],
            ],
        ];
    }
}
