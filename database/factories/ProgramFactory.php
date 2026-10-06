<?php

namespace Database\Factories;

use App\Enums\ProgramType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    private const NEED_NAMES = [
        'Beasiswa Peduli Pendidikan',
        'Beasiswa Harapan Bangsa',
        'Beasiswa Pelita Ilmu',
        'Beasiswa Bakti Negeri',
        'Beasiswa Kemandirian Belajar',
        'Beasiswa Cita Bangsa',
        'Beasiswa Sinar Pendidikan',
        'Beasiswa Alumni Peduli',
        'Beasiswa Talenta Daerah',
        'Beasiswa Pendidikan Tinggi Mandiri',
        'Beasiswa Bangun Negeri',
        'Beasiswa Mitra Pendidikan',
        'Beasiswa Cahaya Ilmu',
        'Beasiswa Langkah Awal',
    ];

    private const MERIT_NAMES = [
        'Beasiswa Prestasi Akademik',
        'Beasiswa Unggulan Mahasiswa',
        'Beasiswa Garuda Muda',
        'Beasiswa Generasi Emas',
        'Beasiswa Mahasiswa Berprestasi',
        'Beasiswa Pemimpin Muda',
        'Beasiswa Cendekia Nusantara',
        'Beasiswa Inovasi dan Riset',
        'Beasiswa Studi Lanjut',
        'Beasiswa Prestasi Olahraga dan Seni',
        'Beasiswa Bintang Akademik',
        'Beasiswa Duta Kampus',
        'Beasiswa Juara Nusantara',
        'Beasiswa Puncak Prestasi',
    ];

    private const NEED_DESCRIPTIONS = [
        'Program bantuan biaya pendidikan bagi mahasiswa dari keluarga kurang mampu agar tetap dapat melanjutkan kuliah tanpa terbebani biaya.',
        'Beasiswa untuk meringankan biaya kuliah mahasiswa dengan penghasilan keluarga terbatas. Dana dicairkan secara bertahap setiap semester.',
        'Beasiswa untuk membantu mahasiswa dari keluarga sederhana menyelesaikan studi tepat waktu.',
    ];

    private const MERIT_DESCRIPTIONS = [
        'Beasiswa ini diberikan kepada mahasiswa aktif yang menunjukkan prestasi akademik yang konsisten dan berkomitmen menyelesaikan studi tepat waktu.',
        'Dukungan dana pendidikan untuk mahasiswa berprestasi yang aktif dalam kegiatan organisasi, penelitian, maupun kegiatan sosial di kampus.',
        'Beasiswa untuk mencetak generasi muda yang unggul dan berintegritas serta siap berkontribusi bagi masyarakat setelah lulus.',
    ];

    public function definition(): array
    {
        $type = fake()->randomElement(ProgramType::cases());

        // The budget is a whole number of shares, one for each recipient.
        $quota = fake()->numberBetween(5, 25);
        $deadline = Carbon::instance(fake()->dateTimeBetween('+2 weeks', '+3 months'))->startOfDay();

        return [
            'name' => fake()->randomElement($type === ProgramType::NeedBased ? self::NEED_NAMES : self::MERIT_NAMES),
            'type' => $type,
            'funding_source' => fake()->randomElement(['Dana Abadi Universitas', 'Hibah Pemerintah', 'Dana Alumni', 'Sponsor Perusahaan']),
            'budget' => $quota * fake()->randomElement([3000000, 4000000, 5000000, 6000000, 7500000, 10000000]),
            'quota' => $quota,
            'application_deadline' => $deadline,
            'review_deadline' => $deadline->copy()->addDays(14),
            'announcement_date' => $deadline->copy()->addDays(21),
            'max_family_income' => $type === ProgramType::NeedBased ? fake()->randomElement([3000000, 3500000, 4000000, 4500000, 5000000]) : null,
            'min_gpa' => $type === ProgramType::MeritBased ? fake()->randomElement([3.00, 3.25, 3.50, 3.75]) : null,
            'allows_other_scholarships' => fake()->boolean(25),
            'coordinator_id' => User::factory(),
            'description' => fake()->randomElement($type === ProgramType::NeedBased ? self::NEED_DESCRIPTIONS : self::MERIT_DESCRIPTIONS),
        ];
    }

    /**
     * Name and description that fit the given programme type, for callers that
     * set `type` themselves and so cannot rely on the random one above.
     */
    public function forType(string $type): static
    {
        $needBased = $type === ProgramType::NeedBased->value;

        return $this->state(fn () => [
            'name' => fake()->unique()->randomElement($needBased ? self::NEED_NAMES : self::MERIT_NAMES),
            'description' => fake()->randomElement($needBased ? self::NEED_DESCRIPTIONS : self::MERIT_DESCRIPTIONS),
        ]);
    }
}
