<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reviewer_id' => User::factory(),
            'application_id' => Application::factory(),
            'score' => fake()->numberBetween(40, 100),
            'comments' => fake()->sentence(),
        ];
    }
}
