<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'program_id' => Program::factory(),
            'semester' => '2026-1',
            'submission_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'status' => ApplicationStatus::Submitted,
        ];
    }
}
