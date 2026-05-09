<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('clears all notifications across all users', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    foreach (range(1, 2) as $index) {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'manual.test',
            'notifiable_type' => User::class,
            'notifiable_id' => $firstUser->id,
            'data' => json_encode(['title' => "First {$index}"], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    foreach (range(1, 3) as $index) {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'manual.test',
            'notifiable_type' => User::class,
            'notifiable_id' => $secondUser->id,
            'data' => json_encode(['title' => "Second {$index}"], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    expect(DB::table('notifications')->count())->toBe(5);

    $this->artisan('app:clear-all-notifications')
        ->expectsOutputToContain('Cleared 5 notification(s) across all users.')
        ->assertSuccessful();

    expect(DB::table('notifications')->count())->toBe(0);
});
