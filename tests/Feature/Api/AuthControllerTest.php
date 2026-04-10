<?php

use App\Models\User;

it('registers a new user and returns token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'username', 'email', 'total_points', 'current_streak'],
            'token',
        ]);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

it('fails registration with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Test',
        'username' => 'newuser',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('logs in with valid credentials and returns token', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['user', 'token']);
});

it('rejects login with invalid credentials', function () {
    User::factory()->create(['password' => bcrypt('secret123')]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'wrong@example.com',
        'password' => 'wrong',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns authenticated user profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertSuccessful()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('logs out and revokes token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout')
        ->assertSuccessful()
        ->assertJsonPath('message', 'Logged out successfully.');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('returns 401 for unauthenticated access to protected routes', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized();
});
