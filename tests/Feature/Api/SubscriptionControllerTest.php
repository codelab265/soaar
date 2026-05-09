<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('returns null when no active subscription', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/subscription')
        ->assertSuccessful()
        ->assertJsonPath('data', null);
});

it('subscribes to premium tier', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/subscription', ['tier' => 'premium'])
        ->assertCreated()
        ->assertJsonPath('data.tier', 'premium')
        ->assertJsonPath('data.is_premium', true);
});

it('shows active subscription', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/subscription', ['tier' => 'premium']);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/subscription')
        ->assertSuccessful()
        ->assertJsonPath('data.tier', 'premium');
});

it('cancels a subscription', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/subscription', ['tier' => 'premium']);

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/subscription/cancel')
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'cancelled');
});

it('returns subscription history', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/subscription', ['tier' => 'premium']);

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/subscription/cancel');

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/subscription', ['tier' => 'premium']);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/subscriptions')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.tier', 'premium');
});
