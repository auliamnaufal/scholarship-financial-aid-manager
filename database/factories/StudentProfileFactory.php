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
        $faculties = [
            'Fakultas Ilmu Komputer' => ['Teknik Informatika', 'Sistem Informasi'],
            'Fakultas Ekonomi dan Bisnis' => ['Akuntansi', 'Manajemen'],
            'Fakultas Teknik' => ['Teknik Sipil', 'Teknik Elektro'],
            'Fakultas Kedokteran' => ['Pendidikan Dokter', 'Keperawatan'],
        ];

        $faculty = fake()->randomElement(array_keys($faculties));

        return [
            'user_id' => User::factory(),
            'nim' => fake()->unique()->numerify('##########'),
            'faculty' => $faculty,
            'study_program' => fake()->randomElement($faculties[$faculty]),
            'gpa' => fake()->randomFloat(2, 2, 4),
            'year_enrolled' => fake()->numberBetween(now()->year - 4, now()->year),
            'phone' => '08'.fake()->numerify('##########'),
            'address' => fake()->address(),
            'family_income' => fake()->randomFloat(2, 15000, 120000),
            'parent_occupation' => fake()->randomElement(['Petani', 'Wiraswasta', 'Guru', 'Buruh', 'Pegawai Negeri', 'Pensiunan']),
            'dependents_count' => fake()->numberBetween(1, 5),
            'bank_name' => fake()->randomElement(['BCA', 'BNI', 'BRI', 'Mandiri', 'BSI']),
            'bank_account_number' => fake()->numerify('##########'),
            'bank_account_holder' => fake()->name(),
        ];
    }
}
