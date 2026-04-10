<?php

use App\Models\User;
use App\Notifications\PointsChangedNotification;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists notifications', function () {
    $this->user->notify(new PointsChangedNotification(10, 'Test award'));

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/notifications')
        ->assertSuccessful()
        ->assertJsonPath('total', 1);
});

it('marks a notification as read', function () {
    $this->user->notify(new PointsChangedNotification(10, 'Test'));
    $notification = $this->user->notifications()->first();

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/notifications/{$notification->id}/read")
        ->assertSuccessful();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('marks all notifications as read', function () {
    $this->user->notify(new PointsChangedNotification(10, 'One'));
    $this->user->notify(new PointsChangedNotification(20, 'Two'));

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/notifications/read-all')
        ->assertSuccessful();

    expect($this->user->unreadNotifications()->count())->toBe(0);
});

it('returns unread count', function () {
    $this->user->notify(new PointsChangedNotification(10, 'Test'));

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/notifications/unread-count')
        ->assertSuccessful()
        ->assertJsonPath('unread_count', 1);
});
