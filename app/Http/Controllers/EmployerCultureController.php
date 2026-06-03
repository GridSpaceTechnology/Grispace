<?php

namespace App\Http\Controllers;

use App\Models\EmployerCultureProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerCultureController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $profile = $user->employerCultureProfile;

        $questions = [
            'work_environment' => [
                'question' => 'Which best describes your company environment?',
                'options' => [
                    'startup' => 'Fast-paced startup with rapid growth',
                    'corporate' => 'Structured corporate environment',
                    'agency' => 'Creative agency or consultancy',
                    'nonprofit' => 'Mission-driven nonprofit',
                    'remote' => 'Fully remote distributed team',
                    'hybrid' => 'Hybrid in-office and remote',
                ],
            ],
            'preferred_employee' => [
                'question' => 'What type of employee thrives at your company?',
                'options' => [
                    'self_starter' => 'Self-starters who take initiative',
                    'collaborative' => 'Collaborative team players',
                    'innovative' => 'Innovative creative thinkers',
                    'detail_oriented' => 'Detail-oriented and methodical',
                    'adaptable' => 'Adaptable generalists',
                    'specialist' => 'Deep specialist experts',
                ],
            ],
            'communication_style' => [
                'question' => 'How does your team prefer to communicate?',
                'options' => [
                    'direct' => 'Direct and to the point',
                    'collaborative' => 'Collaborative and consensus-driven',
                    'formal' => 'Formal and structured',
                    'casual' => 'Casual and informal',
                    'async' => 'Async written communication',
                    'sync' => 'Real-time synchronous communication',
                ],
            ],
            'leadership_style' => [
                'question' => 'How would you describe your leadership approach?',
                'options' => [
                    'empowering' => 'Empowering autonomous teams',
                    'directive' => 'Directive with clear guidance',
                    'coaching' => 'Coaching and mentorship focused',
                    'flat' => 'Flat decentralized leadership',
                    'visionary' => 'Visionary big-picture leadership',
                    'democratic' => 'Democratic participative decision-making',
                ],
            ],
            'company_pace' => [
                'question' => 'What is your company\'s typical work pace?',
                'options' => [
                    'fast' => 'Fast-paced with quick turnarounds',
                    'moderate' => 'Moderate balanced pace',
                    'steady' => 'Steady predictable workflow',
                    'seasonal' => 'Seasonal with peak periods',
                    'flexible' => 'Flexible self-directed pacing',
                ],
            ],
            'success_traits' => [
                'question' => 'Which traits lead to success at your company?',
                'options' => [
                    'ownership' => 'Ownership and accountability',
                    'teamwork' => 'Teamwork and collaboration',
                    'innovation' => 'Innovation and creativity',
                    'reliability' => 'Reliability and consistency',
                    'growth' => 'Growth mindset and learning',
                    'results' => 'Results-driven execution',
                ],
            ],
            'motivation_factors' => [
                'question' => 'What motivates your team most?',
                'options' => [
                    'impact' => 'Making a meaningful impact',
                    'growth' => 'Professional growth and development',
                    'recognition' => 'Recognition and achievement',
                    'stability' => 'Stability and work-life balance',
                    'mission' => 'The company mission and purpose',
                    'compensation' => 'Competitive compensation and rewards',
                ],
            ],
            'independence_level' => [
                'question' => 'How much independence do employees have?',
                'options' => [
                    'high' => 'High autonomy with minimal oversight',
                    'moderate' => 'Moderate independence with guidance',
                    'structured' => 'Structured with clear processes',
                    'collaborative' => 'Team-based with shared ownership',
                ],
            ],
        ];

        $selected = $profile ? $profile->toArray() : [];

        return view('employer.culture-assessment', compact('questions', 'profile', 'selected'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'work_environment' => 'required|string|max:255',
            'preferred_traits' => 'required|array',
            'preferred_traits.*' => 'string|max:255',
            'communication_style' => 'required|string|max:255',
            'leadership_style' => 'required|string|max:255',
            'company_pace' => 'required|string|max:255',
            'motivation_factors' => 'required|array',
            'motivation_factors.*' => 'string|max:255',
            'independence_level' => 'required|string|max:255',
        ]);

        $validated['user_id'] = $user->id;

        $preferredTraits = $request->input('preferred_traits', []);
        $motivationFactors = $request->input('motivation_factors', []);

        $cultureSummary = $this->generateCultureSummary($validated);

        EmployerCultureProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'work_environment' => $validated['work_environment'],
                'communication_style' => $validated['communication_style'],
                'leadership_style' => $validated['leadership_style'],
                'company_pace' => $validated['company_pace'],
                'preferred_traits' => $preferredTraits,
                'motivation_factors' => $motivationFactors,
                'independence_level' => $validated['independence_level'],
                'culture_summary' => $cultureSummary,
            ]
        );

        return redirect()->route('employer.dashboard')
            ->with('success', 'Culture profile saved successfully!');
    }

    private function generateCultureSummary(array $data): string
    {
        $envLabels = [
            'startup' => 'fast-paced startup environment',
            'corporate' => 'structured corporate environment',
            'agency' => 'creative agency environment',
            'nonprofit' => 'mission-driven nonprofit environment',
            'remote' => 'fully remote distributed environment',
            'hybrid' => 'hybrid work environment',
        ];

        $leadershipLabels = [
            'empowering' => 'empowers autonomous teams',
            'directive' => 'provides clear direction',
            'coaching' => 'focuses on coaching and growth',
            'flat' => 'operates with flat leadership',
            'visionary' => 'leads with vision',
            'democratic' => 'values participative decision-making',
        ];

        $env = $envLabels[$data['work_environment']] ?? $data['work_environment'];
        $lead = $leadershipLabels[$data['leadership_style']] ?? $data['leadership_style'];

        return "A {$env} that {$lead}. "
            ."Communication is {$data['communication_style']}, "
            ."with a {$data['company_pace']} pace "
            ."and {$data['independence_level']} independence for team members.";
    }
}
