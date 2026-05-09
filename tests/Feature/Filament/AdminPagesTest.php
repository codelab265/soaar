<?php

use App\Filament\Pages\AdminLeaderboard;
use App\Filament\Pages\SendGlobalNotification;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can view admin leaderboard', function () {
    $users = User::factory()->count(3)->create();

    Livewire::test(AdminLeaderboard::class)
        ->assertSee($users->first()->name);
});

it('can send a global notification to all users', function () {
    Notification::fake();

    $recipients = User::factory()->count(2)->create();

    Livewire::test(SendGlobalNotification::class)
        ->fillForm([
            'audience' => 'all',
            'title' => 'Maintenance',
            'body' => 'We will be down for 5 minutes.',
        ])
        ->callAction('send')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($recipients, AdminBroadcastNotification::class);
});
