<?php

use App\Enums\GoalStatus;
use App\Enums\StreakType;
use App\Models\Goal;
use App\Models\Streak;
use App\Models\User;
use App\Notifications\DeadlineApproachingNotification;
use App\Notifications\InactivityNotification;
use App\Notifications\StreakAtRiskNotification;
use Illuminate\Support\Facades\Notification;

it('sends streak risk notifications to users whose streak is at risk', function () {
    Notification::fake();

    $atRiskUser = User::factory()->create();
    Streak::create([
        'user_id' => $atRiskUser->id,
        'type' => StreakType::Daily->value,
        'current_count' => 5,
        'longest_count' => 5,
        'last_activity_date' => now()->subDay(),
    ]);

    $safeUser = User::factory()->create();
    Streak::create([
        'user_id' => $safeUser->id,
        'type' => StreakType::Daily->value,
        'current_count' => 3,
        'longest_count' => 3,
        'last_activity_date' => now(),
    ]);

    $this->artisan('app:send-streak-risk-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($atRiskUser, StreakAtRiskNotification::class);
    Notification::assertNotSentTo($safeUser, StreakAtRiskNotification::class);
});

it('sends inactivity notifications to users inactive for 2+ days', function () {
    Notification::fake();

    $inactiveUser = User::factory()->create();
    Streak::create([
        'user_id' => $inactiveUser->id,
        'type' => StreakType::Daily->value,
        'current_count' => 0,
        'longest_count' => 0,
        'last_activity_date' => now()->subDays(3),
    ]);

    $activeUser = User::factory()->create();
    Streak::create([
        'user_id' => $activeUser->id,
        'type' => StreakType::Daily->value,
        'current_count' => 5,
        'longest_count' => 5,
        'last_activity_date' => now()->subDay(),
    ]);

    $this->artisan('app:send-inactivity-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($inactiveUser, InactivityNotification::class);
    Notification::assertNotSentTo($activeUser, InactivityNotification::class);
});

it('streak at risk notification contains correct data', function () {
    $notification = new StreakAtRiskNotification(10);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data['current_streak'])->toBe(10)
        ->and($data['message'])->toContain('10-day streak');
});

it('inactivity notification contains correct data', function () {
    $notification = new InactivityNotification(3);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data['inactive_days'])->toBe(3)
        ->and($data['message'])->toContain('3 day(s)');
});

it('sends deadline approaching notifications for goals due in two days', function () {
    Notification::fake();

    $dueSoonUser = User::factory()->create();
    Goal::factory()->for($dueSoonUser)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->addDays(2)->toDateString(),
    ]);

    $laterUser = User::factory()->create();
    Goal::factory()->for($laterUser)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->addDays(5)->toDateString(),
    ]);

    $this->artisan('app:send-deadline-approaching-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($dueSoonUser, DeadlineApproachingNotification::class);
    Notification::assertNotSentTo($laterUser, DeadlineApproachingNotification::class);
});
