<?php

use App\Models\User;

it('searches users by username prefix', function () {
    $me = User::factory()->create(['username' => 'meuser']);

    User::factory()->create(['username' => 'johnny', 'name' => 'Johnny']);
    User::factory()->create(['username' => 'john', 'name' => 'John']);
    User::factory()->create(['username' => 'alice', 'name' => 'Alice']);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/users/search?username=joh')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.username', 'john')
        ->assertJsonPath('data.1.username', 'johnny');
});

it('requires a username query', function () {
    $me = User::factory()->create();

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/users/search')
        ->assertUnprocessable();
});

it('does not include the authenticated user in results', function () {
    $me = User::factory()->create(['username' => 'john']);

    User::factory()->create(['username' => 'johnny']);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/users/search?username=joh')
        ->assertSuccessful()
        ->assertJsonMissing(['username' => 'john']);
});
