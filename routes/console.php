<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app()->booted(function () {
    app(Schedule::class)
        ->command('app:auto-approve-goals')
        ->hourly()
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:expire-goals')
        ->dailyAt('00:10')
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:process-missed-tasks')
        ->dailyAt('00:20')
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:check-streaks')
        ->dailyAt('00:30')
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:send-streak-risk-notifications')
        ->dailyAt('18:00')
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:send-inactivity-notifications')
        ->dailyAt('18:10')
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:send-deadline-approaching-notifications')
        ->dailyAt('18:20')
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:award-leaderboard-bonus')
        ->dailyAt('23:50')
        ->withoutOverlapping();

    app(Schedule::class)
        ->command('app:update-discipline-scores')
        ->weeklyOn(1, '00:40')
        ->withoutOverlapping();
});
