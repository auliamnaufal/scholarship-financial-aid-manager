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
            'comments' => fake()->randomElement([
                'Berkas lengkap dan prestasi akademik pemohon sangat baik.',
                'Motivasi pemohon kuat namun beberapa dokumen pendukung perlu dilengkapi.',
                'Kondisi ekonomi keluarga sesuai dengan kriteria beasiswa ini.',
                'Esai ditulis dengan jelas dan menunjukkan rencana studi yang matang.',
                'Pemohon layak dipertimbangkan tetapi pencapaian di luar bidang akademik masih terbatas.',
                'Nilai IPK memenuhi syarat tetapi surat pendukung kurang meyakinkan.',
            ]),
        ];
    }
}
