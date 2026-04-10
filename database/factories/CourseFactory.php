<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priceMwk = fake()->randomElement([10000, 15000, 25000, 50000]);

        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'duration' => fake()->randomElement(['2 hours', '4 hours', '1 week', '2 weeks']),
            'price_mwk' => $priceMwk,
            'price_points' => (int) ($priceMwk / 10),
            'content_type' => fake()->randomElement(['video', 'audio', 'text']),
            'content_url' => fake()->url(),
            'is_active' => true,
        ];
    }
}
