<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('updates the authenticated user profile', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'oldusername',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/users/me', [
            'name' => 'New Name',
            'username' => 'newusername',
            'email' => 'new@example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.username', 'newusername')
        ->assertJsonPath('data.email', 'new@example.com');

    $user->refresh();
    expect($user->name)->toBe('New Name')
        ->and($user->username)->toBe('newusername')
        ->and($user->email)->toBe('new@example.com');
});

it('uploads a profile picture for the authenticated user', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'profile_picture' => null,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/users/me/profile-picture', [
            'profile_picture' => UploadedFile::fake()->image('avatar.jpg', 512, 512),
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.id', $user->id);

    $user->refresh();
    expect($user->profile_picture)->not->toBeNull();

    Storage::disk('public')->assertExists($user->profile_picture);
});

it('updates the authenticated user push token', function () {
    $user = User::factory()->create([
        'fcm_token' => null,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/users/me/push-token', [
            'fcm_token' => 'test-fcm-token-123',
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'Push token updated.');

    expect($user->fresh()->fcm_token)->toBe('test-fcm-token-123');
});
