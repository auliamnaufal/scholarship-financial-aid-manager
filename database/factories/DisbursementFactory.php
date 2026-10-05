<?php

namespace Database\Factories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Disbursement>
 */
class DisbursementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'seq_no' => 1,
            'amount' => fake()->numberBetween(5, 50) * 100000,
            'disbursement_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'semester' => '2026-1',
        ];
    }
}
