<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseEnrollment>
 */
class CourseEnrollmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'payment_method' => PaymentMethod::Money,
            'points_used' => 0,
            'amount_paid' => 25000,
            'enrolled_at' => now(),
        ];
    }
}
