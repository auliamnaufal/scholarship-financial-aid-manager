<?php

namespace Database\Factories;

use App\Enums\ProgramType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(ProgramType::cases());

        return [
            'name' => fake()->words(3, true).' Scholarship',
            'type' => $type,
            'funding_source' => fake()->randomElement(['University Endowment', 'Government Grant', 'Alumni Fund', 'Corporate Sponsor']),
            'budget' => fake()->randomFloat(2, 10000, 200000),
            'application_deadline' => fake()->dateTimeBetween('-1 month', '+3 months'),
            'max_family_income' => $type === ProgramType::NeedBased ? fake()->randomFloat(2, 20000, 80000) : null,
            'min_gpa' => $type === ProgramType::MeritBased ? fake()->randomFloat(2, 3, 4) : null,
            'coordinator_id' => User::factory(),
            'description' => fake()->paragraph(),
        ];
    }
}
