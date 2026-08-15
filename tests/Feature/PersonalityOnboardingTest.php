<?php

use App\Models\PersonalityAnswer;
use App\Models\PersonalityQuestion;
use App\Models\User;
use Database\Seeders\GridSpacePersonalityAssessmentSeeder;

beforeEach(function () {
    $this->seed(GridSpacePersonalityAssessmentSeeder::class);
});

test('candidate step 4 redirects to the personality assessment flow', function () {
    $user = User::factory()->create(['role' => 'candidate', 'onboarding_completed' => false]);

    $this->actingAs($user)
        ->get(route('candidate.onboarding.step', ['step' => 4]))
        ->assertRedirect(route('candidate.onboarding.assessment', ['step' => 4]));
});

test('candidate step 5 redirects to the personality assessment flow', function () {
    $user = User::factory()->create(['role' => 'candidate', 'onboarding_completed' => false]);

    $this->actingAs($user)
        ->get(route('candidate.onboarding.step', ['step' => 5]))
        ->assertRedirect(route('candidate.onboarding.assessment', ['step' => 5]));
});

test('candidate completes step 4 assessment and moves to step 5', function () {
    $user = User::factory()->create(['role' => 'candidate', 'onboarding_completed' => false]);

    $categories = [
        'work_style',
        'communication_style',
        'team_dynamics',
        'problem_solving',
    ];

    $questions = PersonalityQuestion::active()->ordered()->whereIn('category', $categories)->get();
    expect($questions)->toHaveCount(12);

    $this->actingAs($user);

    foreach ($questions as $index => $question) {
        $option = $question->options()->first();

        $response = $this->post(
            route('candidate.onboarding.assessment.answer', ['step' => 4, 'question' => $question]),
            ['option_id' => $option->id]
        );

        if ($index === $questions->count() - 1) {
            $response->assertRedirect(route('candidate.onboarding.assessment', ['step' => 5]));
        } else {
            $next = $questions[$index + 1];
            $response->assertRedirect(route('candidate.onboarding.assessment.question', ['step' => 4, 'question' => $next]));
        }
    }

    expect(PersonalityAnswer::where('candidate_id', $user->id)->count())->toBe(12);
    expect(PersonalityAnswer::where('candidate_id', $user->id)->where('section', 'work_style')->count())->toBe(3);
    expect(PersonalityAnswer::where('candidate_id', $user->id)->where('section', 'organizational_culture')->count())->toBe(0);
});

test('candidate completes step 5 assessment and generates a personality profile', function () {
    $user = User::factory()->create(['role' => 'candidate', 'onboarding_completed' => false]);

    $stepFourCategories = [
        'work_style',
        'communication_style',
        'team_dynamics',
        'problem_solving',
    ];

    $stepFiveCategories = [
        'leadership_initiative',
        'work_environment_preference',
        'motivation_drivers',
        'temperament_indicators',
    ];

    $this->actingAs($user);

    foreach ([4 => $stepFourCategories, 5 => $stepFiveCategories] as $step => $categories) {
        $questions = PersonalityQuestion::active()->ordered()->whereIn('category', $categories)->get();
        expect($questions)->toHaveCount(12);

        foreach ($questions as $index => $question) {
            $option = $question->options()->first();

            $response = $this->post(
                route('candidate.onboarding.assessment.answer', ['step' => $step, 'question' => $question]),
                ['option_id' => $option->id]
            );

            if ($step === 5 && $index === $questions->count() - 1) {
                $response->assertRedirect(route('candidate.onboarding.step', ['step' => 6]));
            }
        }
    }

    $profile = $user->personalityProfile()->first();

    expect($profile)->not->toBeNull();
    expect($profile->assessment_completed)->toBeTrue();
    expect($user->candidateAssessment()->first()->temperament_type)
        ->toBeIn(['analytical', 'driver', 'expressive', 'amiable']);
});

test('candidate can save and continue later from the personality assessment', function () {
    $user = User::factory()->create(['role' => 'candidate', 'onboarding_completed' => false]);

    $this->actingAs($user)
        ->post(route('candidate.personality.skip'))
        ->assertRedirect(route('candidate.dashboard'));

    expect($user->fresh()->personalityProfile)->not->toBeNull();
    expect($user->fresh()->personalityProfile->assessment_completed)->toBeFalse();
});

test('candidate cannot reach the skip endpoint via a GET request', function () {
    $user = User::factory()->create(['role' => 'candidate']);

    $this->actingAs($user)
        ->get(route('candidate.personality.skip'))
        ->assertNotFound();
});

test('candidate assessment answers are tagged with their section', function () {
    $user = User::factory()->create(['role' => 'candidate', 'onboarding_completed' => false]);

    $question = PersonalityQuestion::active()->ordered()->where('category', 'temperament_indicators')->first();
    $option = $question->options()->first();

    $this->actingAs($user)
        ->post(
            route('candidate.onboarding.assessment.answer', ['step' => 5, 'question' => $question]),
            ['option_id' => $option->id]
        );

    expect(PersonalityAnswer::where('candidate_id', $user->id)->first()->section)->toBe('temperament_indicators');
});

test('employer setup redirects to the culture onboarding flow', function () {
    $user = User::factory()->create(['role' => 'employer', 'onboarding_completed' => false]);

    $response = $this->actingAs($user)->post('/employer/setup', [
        'name' => 'Acme Corporation',
        'industry' => 'Technology',
        'company_size' => '1-10',
        'location' => 'Lagos',
        'location_country' => 'Nigeria',
        'website' => 'https://acme.test',
        'work_model' => 'hybrid',
    ]);

    $response->assertRedirect(route('employer.onboarding.culture'));

    expect($user->company)->not->toBeNull();
    expect($user->fresh()->onboarding_completed)->toBeFalse();
});

test('employer completes the culture onboarding and finishes setup', function () {
    $user = User::factory()->create(['role' => 'employer', 'onboarding_completed' => false]);

    $questions = PersonalityQuestion::active()->ordered()->employer()->get();
    expect($questions)->toHaveCount(3);

    $this->actingAs($user);

    foreach ($questions as $index => $question) {
        $option = $question->options()->first();

        $response = $this->post(
            route('employer.onboarding.culture.answer', $question),
            ['option_id' => $option->id]
        );

        if ($index === $questions->count() - 1) {
            $response->assertRedirect(route('employer.dashboard'));
        }
    }

    expect($user->fresh()->onboarding_completed)->toBeTrue();
    expect(PersonalityAnswer::where('candidate_id', $user->id)->count())->toBe(3);
    expect($user->employerCultureProfile)->not->toBeNull();
    expect($user->employerCultureProfile->work_environment)->not->toBeNull();
    expect($user->employerCultureProfile->culture_summary)->not->toBeEmpty();
});
