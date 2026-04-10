<?php

use App\Enums\PaymentMethod;
use App\Models\Course;
use App\Models\User;
use App\Services\CourseRedemptionService;

beforeEach(function () {
    $this->service = new CourseRedemptionService;
    $this->user = User::factory()->create(['total_points' => 5000]);
    $this->course = Course::factory()->create([
        'price_mwk' => 25000,
        'price_points' => 2500,
    ]);
});

it('enrolls a user with money payment', function () {
    $enrollment = $this->service->enrollWithMoney($this->user, $this->course);

    expect($enrollment->payment_method)->toBe(PaymentMethod::Money)
        ->and($enrollment->amount_paid)->toBe(25000)
        ->and($enrollment->points_used)->toBe(0)
        ->and($this->user->fresh()->total_points)->toBe(5000);
});

it('enrolls a user with points payment', function () {
    $enrollment = $this->service->enrollWithPoints($this->user, $this->course);

    expect($enrollment->payment_method)->toBe(PaymentMethod::Points)
        ->and($enrollment->points_used)->toBe(2500)
        ->and($enrollment->amount_paid)->toBe(0)
        ->and($this->user->fresh()->total_points)->toBe(2500);
});

it('rejects points payment with insufficient balance', function () {
    $this->user->update(['total_points' => 1000]);

    $this->service->enrollWithPoints($this->user, $this->course);
})->throws(InvalidArgumentException::class, 'Insufficient points');

it('enrolls a user with hybrid payment', function () {
    $enrollment = $this->service->enrollHybrid($this->user, $this->course, pointsToUse: 1200);

    expect($enrollment->payment_method)->toBe(PaymentMethod::Hybrid)
        ->and($enrollment->points_used)->toBe(1200)
        ->and($enrollment->amount_paid)->toBe(13000)
        ->and($this->user->fresh()->total_points)->toBe(3800);
});

it('calculates hybrid remainder correctly', function () {
    $enrollment = $this->service->enrollHybrid($this->user, $this->course, pointsToUse: 2500);

    expect($enrollment->points_used)->toBe(2500)
        ->and($enrollment->amount_paid)->toBe(0)
        ->and($this->user->fresh()->total_points)->toBe(2500);
});

it('rejects hybrid payment with insufficient points', function () {
    $this->user->update(['total_points' => 500]);

    $this->service->enrollHybrid($this->user, $this->course, pointsToUse: 1000);
})->throws(InvalidArgumentException::class, 'Insufficient points');

it('rejects hybrid payment exceeding course point price', function () {
    $this->service->enrollHybrid($this->user, $this->course, pointsToUse: 3000);
})->throws(InvalidArgumentException::class, 'exceeds the course point price');
