<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+234 801 234-5678',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertSame('+2348012345678', $user->phone_number);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('multiple users can share a blank phone number', function () {
    $first = User::factory()->create(['phone_number' => null]);
    $second = User::factory()->create();

    $response = $this
        ->actingAs($second)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $second->name,
            'email' => $second->email,
            'phone_number' => '',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNull($first->fresh()->phone_number);
    $this->assertNull($second->fresh()->phone_number);
});

test('a phone number already used by another user is rejected', function () {
    User::factory()->create(['phone_number' => '+12345678901']);
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => '+12345678901',
        ]);

    $response
        ->assertSessionHasErrors('phone_number')
        ->assertRedirect('/profile');

    $this->assertNull($user->fresh()->phone_number);
});

test('profile photo can be uploaded', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('photo.jpg', 150, 150),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertNotNull($user->profile_photo_path);
    Storage::disk('public')->assertExists($user->profile_photo_path);
    $this->assertNotNull($user->profile_photo_url);
});

test('uploading a new profile photo replaces the old one', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('first.jpg', 150, 150),
        ]);

    $oldPath = $user->refresh()->profile_photo_path;

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('second.jpg', 150, 150),
        ]);

    $newPath = $user->refresh()->profile_photo_path;

    Storage::disk('public')->assertExists($newPath);
    Storage::disk('public')->assertMissing($oldPath);
});

test('profile photo must be a valid image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['phone_number' => '+2348012345678']);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->create('document.pdf', 100),
        ]);

    $response
        ->assertSessionHasErrors('profile_photo')
        ->assertRedirect('/profile');

    $this->assertNull($user->refresh()->profile_photo_path);
    Storage::disk('public')->assertDirectoryEmpty('profile-photos');
});

test('profile photo must not exceed the size limit', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('photo.jpg', 150, 150)->size(3000),
        ]);

    $response
        ->assertSessionHasErrors('profile_photo')
        ->assertRedirect('/profile');

    $this->assertNull($user->fresh()->profile_photo_path);
});

test('profile photo can be removed', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('photo.jpg', 150, 150),
        ]);

    $path = $user->refresh()->profile_photo_path;

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile/photo');

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNull($user->refresh()->profile_photo_path);
    Storage::disk('public')->assertMissing($path);
});

test('dashboard shows the profile picture when the user has one', function () {
    Storage::fake('public');
    $user = User::factory()->create([
        'role' => 'employer',
        'onboarding_completed' => true,
    ]);

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('photo.jpg', 150, 150),
        ]);

    $response = $this
        ->actingAs($user->refresh())
        ->get(route('employer.dashboard'));

    $response
        ->assertOk()
        ->assertSee('dashboard-user-avatar', false)
        ->assertSee('/storage/profile-photos/');
});

test('dashboard falls back to an initial placeholder when the user has no profile picture', function () {
    $user = User::factory()->create([
        'role' => 'employer',
        'name' => 'alice johnson',
        'onboarding_completed' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('employer.dashboard'));

    $response
        ->assertOk()
        ->assertSee('dashboard-user-avatar', false)
        ->assertSee('>A<', false)
        ->assertDontSee('M11.049 2.927');
});

test('candidate dashboard shows the profile picture in the welcome banner', function () {
    Storage::fake('public');
    $user = User::factory()->create([
        'role' => 'candidate',
        'onboarding_completed' => true,
    ]);

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('photo.jpg', 150, 150),
        ]);

    $response = $this
        ->actingAs($user->refresh())
        ->get(route('candidate.dashboard'));

    $response
        ->assertOk()
        ->assertSee('dashboard-user-avatar', false)
        ->assertSee('/storage/profile-photos/');
});

test('candidate settings page shows personal information and password & security sections', function () {
    $user = User::factory()->create(['role' => 'candidate']);

    $response = $this
        ->actingAs($user)
        ->get(route('candidate.profile.edit'));

    $response
        ->assertOk()
        ->assertSee('Personal Information')
        ->assertSee('Password & Security')
        ->assertSee('name="profile_photo"', false)
        ->assertSee('id="personal_name"', false);
});

test('employer settings page shows personal information and password & security sections', function () {
    $user = User::factory()->create(['role' => 'employer']);

    $response = $this
        ->actingAs($user)
        ->get(route('employer.profile.edit'));

    $response
        ->assertOk()
        ->assertSee('Personal Information')
        ->assertSee('Password & Security')
        ->assertSee('name="profile_photo"', false)
        ->assertSee('id="personal_name"', false);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
