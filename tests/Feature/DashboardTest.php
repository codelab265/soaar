<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // This project resolves pages from `resources/js/pages`, but Inertia's default test
    // view-finder assumes `resources/js/Pages` unless configured.
    config(['inertia.testing.ensure_pages_exist' => false]);
});

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('home redirects guests to login', function () {
    $this->get('/')->assertRedirect('/login');
});

test('home redirects authenticated users to dashboard', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/')->assertRedirect('/dashboard');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('isAdmin', false)
            ->has('stats', fn (Assert $stats) => $stats
                ->where('totalPoints', 0)
                ->hasAll([
                    'disciplineScore',
                    'currentStreak',
                    'longestStreak',
                    'activeGoals',
                    'tasksDueToday',
                    'pendingTasks',
                    'completedTasksThisWeek',
                    'dailyStreakCurrent',
                    'dailyStreakLongest',
                    'dailyStreakLastActivityDate',
                ])
            )
            ->has('filters', fn (Assert $filters) => $filters->where('days', 7))
            ->has('activity', fn (Assert $activity) => $activity
                ->has('activityDays')
                ->where('adminActivityDays', null)
            )
            ->has('recentTasks')
            ->where('adminStats', null)
        );
});

test('authenticated admin users get admin stats', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('isAdmin', true)
            ->has('filters', fn (Assert $filters) => $filters->where('days', 7))
            ->has('activity', fn (Assert $activity) => $activity
                ->has('activityDays')
                ->has('adminActivityDays')
            )
            ->has('adminStats', fn (Assert $adminStats) => $adminStats
                ->hasAll([
                    'totalUsers',
                    'activeUsers',
                    'suspendedUsers',
                    'activeGoals',
                    'pendingVerificationGoals',
                    'completedGoals',
                    'pendingTasks',
                    'tasksDueToday',
                    'completedTasksThisWeek',
                ])
            )
        );
});
