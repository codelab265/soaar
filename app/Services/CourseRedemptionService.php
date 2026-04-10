<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use InvalidArgumentException;

class CourseRedemptionService
{
    /** 1,000 points = MWK 10,000 */
    public const POINTS_TO_MWK_RATE = 10;

    /**
     * Enroll a user by paying with money only.
     */
    public function enrollWithMoney(User $user, Course $course): CourseEnrollment
    {
        return CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_method' => PaymentMethod::Money,
            'points_used' => 0,
            'amount_paid' => $course->price_mwk,
            'enrolled_at' => now(),
        ]);
    }

    /**
     * Enroll a user by paying with points only.
     *
     * @throws InvalidArgumentException
     */
    public function enrollWithPoints(User $user, Course $course): CourseEnrollment
    {
        if ($user->total_points < $course->price_points) {
            throw new InvalidArgumentException(
                "Insufficient points. Required: {$course->price_points}, available: {$user->total_points}."
            );
        }

        $user->decrement('total_points', $course->price_points);

        return CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_method' => PaymentMethod::Points,
            'points_used' => $course->price_points,
            'amount_paid' => 0,
            'enrolled_at' => now(),
        ]);
    }

    /**
     * Enroll a user with a hybrid of points and money.
     *
     * @throws InvalidArgumentException
     */
    public function enrollHybrid(User $user, Course $course, int $pointsToUse): CourseEnrollment
    {
        if ($pointsToUse > $user->total_points) {
            throw new InvalidArgumentException(
                "Insufficient points. Requested: {$pointsToUse}, available: {$user->total_points}."
            );
        }

        if ($pointsToUse > $course->price_points) {
            throw new InvalidArgumentException(
                "Points to use ({$pointsToUse}) exceeds the course point price ({$course->price_points})."
            );
        }

        $pointsValueInMwk = $pointsToUse * self::POINTS_TO_MWK_RATE;
        $remainingMwk = max(0, $course->price_mwk - $pointsValueInMwk);

        $user->decrement('total_points', $pointsToUse);

        return CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_method' => PaymentMethod::Hybrid,
            'points_used' => $pointsToUse,
            'amount_paid' => $remainingMwk,
            'enrolled_at' => now(),
        ]);
    }
}
