<?php

use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('registration sends an email verification link without blocking signup', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'candidate',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);

    Notification::assertSentTo($user, QueuedVerifyEmail::class);
});

test('an unverified user within the grace period can access their dashboard', function () {
    $user = User::factory()->unverified()->create(['role' => 'candidate']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('candidate.dashboard'));
});

test('unverified accounts are deactivated after the grace period', function () {
    $user = User::factory()->unverified()->create([
        'created_at' => now()->subDays(15),
    ]);

    $this->artisan('users:suspend-unverified')->assertSuccessful();

    expect($user->fresh()->is_suspended)->toBeTrue()
        ->and($user->fresh()->suspension_reason)->toBe(User::SUSPENSION_REASON_UNVERIFIED_EMAIL);
});

test('unverified accounts within the grace period are not deactivated', function () {
    $user = User::factory()->unverified()->create([
        'created_at' => now()->subDays(5),
    ]);

    $this->artisan('users:suspend-unverified')->assertSuccessful();

    expect($user->fresh()->is_suspended)->toBeFalse()
        ->and($user->fresh()->suspension_reason)->toBeNull();
});

test('verified accounts are never deactivated by the command', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'created_at' => now()->subDays(30),
    ]);

    $this->artisan('users:suspend-unverified')->assertSuccessful();

    expect($user->fresh()->is_suspended)->toBeFalse();
});

test('the command only deactivates each account once', function () {
    $user = User::factory()->unverified()->create([
        'created_at' => now()->subDays(20),
        'is_suspended' => true,
        'suspension_reason' => User::SUSPENSION_REASON_UNVERIFIED_EMAIL,
    ]);

    $this->artisan('users:suspend-unverified')->assertSuccessful();

    expect($user->fresh()->is_suspended)->toBeTrue();
});

test('verifying email reactivates a deactivated account', function () {
    Event::fake();

    $user = User::factory()->unverified()->create([
        'is_suspended' => true,
        'suspension_reason' => User::SUSPENSION_REASON_UNVERIFIED_EMAIL,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and($user->fresh()->is_suspended)->toBeFalse()
        ->and($user->fresh()->suspension_reason)->toBeNull();
});

test('verifying email does not lift a manual suspension', function () {
    $user = User::factory()->unverified()->create([
        'is_suspended' => true,
        'suspension_reason' => null,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and($user->fresh()->is_suspended)->toBeTrue();
});

test('a deactivated user is sent to the verification screen on login', function () {
    $user = User::factory()->unverified()->create([
        'role' => 'candidate',
        'onboarding_completed' => true,
        'is_suspended' => true,
        'suspension_reason' => User::SUSPENSION_REASON_UNVERIFIED_EMAIL,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticatedAs($user);
});

test('the dashboard shows a deactivation warning for unverified users', function () {
    $user = User::factory()->unverified()->create([
        'role' => 'employer',
        'onboarding_completed' => true,
    ]);

    $this->actingAs($user)
        ->get(route('employer.dashboard'))
        ->assertSuccessful()
        ->assertSee('Verify your email');
});
