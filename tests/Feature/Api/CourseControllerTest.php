<?php

use App\Models\Course;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['total_points' => 500]);
});

it('lists active courses', function () {
    Course::factory()->count(3)->create(['is_active' => true]);
    Course::factory()->create(['is_active' => false]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/courses')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('shows a course', function () {
    $course = Course::factory()->create();

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/courses/{$course->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $course->id);
});

it('enrolls with money', function () {
    $course = Course::factory()->create(['price_mwk' => 5000]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/courses/{$course->id}/enroll", [
            'payment_method' => 'money',
        ])
        ->assertCreated()
        ->assertJsonPath('payment_method', 'money');
});

it('enrolls with points', function () {
    $course = Course::factory()->create(['price_points' => 100]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/courses/{$course->id}/enroll", [
            'payment_method' => 'points',
        ])
        ->assertCreated()
        ->assertJsonPath('points_used', 100);

    expect($this->user->fresh()->total_points)->toBe(400);
});

it('rejects enrollment with insufficient points', function () {
    $this->user->update(['total_points' => 10]);
    $course = Course::factory()->create(['price_points' => 500]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/courses/{$course->id}/enroll", [
            'payment_method' => 'points',
        ])
        ->assertUnprocessable()
        ->assertJsonStructure(['message']);
});
