<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('returns analytics summary', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/analytics/summary')
        ->assertSuccessful()
        ->assertJsonStructure(['completion_rate', 'weekly_consistency', 'total_points', 'current_streak']);
});

it('returns completion rate', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/analytics/completion-rate')
        ->assertSuccessful()
        ->assertJsonStructure(['completion_rate']);
});

it('returns weekly consistency', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/analytics/weekly-consistency')
        ->assertSuccessful()
        ->assertJsonStructure(['weekly_consistency']);
});

it('returns points history', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/analytics/points-history')
        ->assertSuccessful()
        ->assertJsonStructure(['points_history']);
});

it('returns discipline trend', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/analytics/discipline-trend')
        ->assertSuccessful()
        ->assertJsonStructure(['discipline_trend']);
});
