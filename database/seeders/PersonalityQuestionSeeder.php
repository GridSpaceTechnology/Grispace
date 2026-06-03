<?php

namespace Database\Seeders;

use App\Models\PersonalityQuestion;
use Illuminate\Database\Seeder;

class PersonalityQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'work_style' => [
                ['How do you prefer to approach your work?', [
                    ['I like following clear processes and structured plans', 'structured', 3],
                    ['I prefer flexibility and adapting as I go', 'flexible', 3],
                    ['I balance structure with flexibility', 'balanced', 2],
                ]],
                ['What pace do you typically work at?', [
                    ['Fast-paced — I thrive on quick turnarounds', 'fast_paced', 3],
                    ['Steady and consistent — I prefer quality over speed', 'steady_paced', 3],
                    ['Variable — I adjust my pace based on priorities', 'balanced', 2],
                ]],
                ['How do you handle multiple tasks or deadlines?', [
                    ['I prioritize and organize systematically', 'structured', 3],
                    ['I juggle them as they come', 'flexible', 2],
                    ['I focus on one thing at a time', 'steady_paced', 2],
                ]],
                ['Do you prefer detailed instructions or autonomy?', [
                    ['Clear detailed instructions help me excel', 'structured', 3],
                    ['I prefer autonomy and figuring things out', 'autonomous', 3],
                    ['A mix of guidance and independence', 'balanced', 2],
                ]],
            ],
            'communication_style' => [
                ['How do you prefer to communicate at work?', [
                    ['Direct and to the point', 'direct', 3],
                    ['Diplomatic and considerate', 'diplomatic', 3],
                    ['It depends on the situation', 'balanced', 2],
                ]],
                ['How expressive are you when sharing ideas?', [
                    ['Very expressive — I love sharing my thoughts', 'expressive', 3],
                    ['Reserved — I share when I have something valuable', 'reserved', 3],
                    ['Moderately expressive', 'balanced', 2],
                ]],
                ['How do you handle disagreements at work?', [
                    ['Address them directly and openly', 'direct', 3],
                    ['Listen first, then find common ground', 'diplomatic', 3],
                    ['Seek compromise and consensus', 'balanced', 2],
                ]],
                ['What type of feedback do you prefer?', [
                    ['Honest and direct feedback', 'direct', 3],
                    ['Constructive and encouraging feedback', 'diplomatic', 3],
                    ['A balance of both', 'balanced', 2],
                ]],
            ],
            'team_dynamics' => [
                ['How do you prefer to work with others?', [
                    ['I enjoy collaborating closely with a team', 'collaborative', 3],
                    ['I prefer working independently', 'independent', 3],
                    ['A mix of both', 'balanced', 2],
                ]],
                ['What role do you typically take in a team?', [
                    ['I naturally take the lead', 'leader', 3],
                    ['I support and enable others', 'supportive', 3],
                    ['I contribute as needed', 'balanced', 2],
                ]],
                ['How do you contribute to team energy?', [
                    ['I bring energy and enthusiasm', 'energizer', 3],
                    ['I bring calm and stability', 'calm', 2],
                    ['I adapt to the team mood', 'balanced', 2],
                ]],
                ['How important is team culture to you?', [
                    ['Very important — I need a strong cultural fit', 'collaborative', 3],
                    ['Somewhat important — I can adapt', 'balanced', 2],
                    ['I focus more on the work itself', 'independent', 2],
                ]],
            ],
            'problem_solving' => [
                ['How do you approach solving problems?', [
                    ['I analyze data and weigh options carefully', 'analytical', 3],
                    ['I trust my instincts and decide quickly', 'decisive', 3],
                    ['I combine analysis with intuition', 'balanced', 2],
                ]],
                ['When facing a complex challenge, you...', [
                    ['Break it down step by step', 'analytical', 3],
                    ['Brainstorm creative solutions', 'creative', 3],
                    ['Seek input from others', 'collaborative', 2],
                ]],
                ['How do you handle unexpected changes?', [
                    ['I adapt quickly and find new solutions', 'flexible', 3],
                    ['I stick to the plan unless necessary', 'structured', 2],
                    ['I assess the impact before adjusting', 'analytical', 3],
                ]],
                ['What type of problems excite you most?', [
                    ['Complex analytical challenges', 'analytical', 3],
                    ['Creative and open-ended problems', 'creative', 3],
                    ['Practical problems with clear solutions', 'decisive', 2],
                ]],
            ],
            'leadership' => [
                ['How do you approach taking initiative?', [
                    ['I naturally take charge and lead', 'leader', 3],
                    ['I step up when needed', 'emerging', 2],
                    ['I prefer to support others\' initiatives', 'supporter', 3],
                ]],
                ['How do you influence others?', [
                    ['Through clear direction and vision', 'leader', 3],
                    ['Through collaboration and consensus', 'collaborative', 2],
                    ['Through expertise and knowledge', 'analytical', 2],
                ]],
                ['When leading a project, you prefer...', [
                    ['Setting the vision and delegating', 'leader', 3],
                    ['Working alongside the team', 'collaborative', 3],
                    ['Providing guidance and support', 'supportive', 2],
                ]],
                ['How comfortable are you with making decisions?', [
                    ['Very comfortable — I decide and move forward', 'decisive', 3],
                    ['I involve others in the decision process', 'collaborative', 3],
                    ['I take time to analyze before deciding', 'analytical', 2],
                ]],
            ],
            'environment_preference' => [
                ['What type of work environment suits you best?', [
                    ['Fast-paced startup or dynamic team', 'startup', 3],
                    ['Structured corporate environment', 'corporate', 3],
                    ['Mission-driven organization', 'mission_driven', 3],
                ]],
                ['Where do you work best?', [
                    ['In an office with a team', 'structured_environment', 2],
                    ['Remotely from anywhere', 'remote_work', 3],
                    ['A hybrid mix of both', 'flexible', 2],
                ]],
                ['How much structure do you need?', [
                    ['Clear hierarchy and defined roles', 'hierarchical', 3],
                    ['Flat organization with autonomy', 'autonomous', 3],
                    ['A balanced approach', 'balanced', 2],
                ]],
                ['What is most important in your workplace?', [
                    ['Innovation and creativity', 'creative', 3],
                    ['Stability and predictability', 'stability', 3],
                    ['Growth and learning opportunities', 'growth', 2],
                ]],
            ],
            'motivation' => [
                ['What drives you most at work?', [
                    ['Learning and professional growth', 'growth', 3],
                    ['Making a meaningful impact', 'impact', 3],
                    ['Achieving challenging goals', 'achievement', 3],
                ]],
                ['What kind of recognition matters to you?', [
                    ['Career advancement and promotions', 'advancement', 3],
                    ['Public recognition of my work', 'recognition', 2],
                    ['Personal satisfaction', 'achievement', 2],
                ]],
                ['What keeps you engaged at work?', [
                    ['Constant learning and development', 'growth', 3],
                    ['A sense of purpose and mission', 'purpose', 3],
                    ['Clear goals and rewards', 'achievement', 2],
                ]],
                ['How important is work-life balance?', [
                    ['Very important — I prioritize balance', 'stability', 3],
                    ['Important — but I go above when needed', 'balanced', 2],
                    ['I\'m fully committed to my career', 'achievement', 2],
                ]],
            ],
            'temperament' => [
                ['How do you typically respond to pressure?', [
                    ['I stay calm and methodical', 'calm', 3],
                    ['I rise to the challenge with energy', 'energetic', 3],
                    ['I focus and push through', 'decisive', 2],
                ]],
                ['What describes your natural energy at work?', [
                    ['Focused and analytical', 'analytical', 3],
                    ['Energetic and enthusiastic', 'energetic', 3],
                    ['Calm and composed', 'calm', 3],
                ]],
                ['How do you prefer to make decisions?', [
                    ['After thorough analysis', 'analytical', 3],
                    ['Quickly and confidently', 'decisive', 3],
                    ['With input from the team', 'collaborative', 2],
                ]],
                ['What is your natural working style?', [
                    ['Systematic and detail-oriented', 'analytical', 3],
                    ['Fast and action-oriented', 'decisive', 3],
                    ['Collaborative and people-focused', 'collaborative', 2],
                ]],
            ],
        ];

        $displayOrder = 0;

        foreach ($categories as $category => $questions) {
            foreach ($questions as [$questionText, $options]) {
                $displayOrder++;

                $question = PersonalityQuestion::create([
                    'category' => $category,
                    'question_text' => $questionText,
                    'question_type' => 'single_choice',
                    'active_status' => true,
                    'display_order' => $displayOrder,
                ]);

                foreach ($options as [$optionText, $signalKey, $signalValue]) {
                    $question->options()->create([
                        'option_text' => $optionText,
                        'signal_key' => $signalKey,
                        'signal_value' => $signalValue,
                    ]);
                }
            }
        }

        $this->command->info('Seeded '.$displayOrder.' personality questions with options.');
    }
}
