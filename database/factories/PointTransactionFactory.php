<?php

namespace Database\Factories;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointTransaction>
 */
class PointTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(PointTransactionType::cases()),
            'points' => fake()->numberBetween(-75, 100),
            'description' => fake()->sentence(),
        ];
    }
}
