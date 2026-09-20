<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'gpa' => fake()->randomFloat(2, 2, 4),
            'year_enrolled' => fake()->numberBetween(now()->year - 4, now()->year),
        ];
    }
}
