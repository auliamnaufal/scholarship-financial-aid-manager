<?php

namespace Database\Seeders;

use App\Enums\RequirementKind;
use App\Models\RequirementType;
use Illuminate\Database\Seeder;

/**
 * The global menu coordinators choose from when setting a scholarship's
 * requirements. Idempotent, so it can be re-run without duplicating rows.
 */
class RequirementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'slug' => 'cv',
                'name' => 'Curriculum vitae (CV)',
                'kind' => RequirementKind::File,
                'description' => 'CV singkat yang memuat riwayat pendidikan, kegiatan, dan pengalaman kerja (jika ada).',
            ],
            [
                'slug' => 'transcript',
                'name' => 'Transkrip nilai (KHS)',
                'kind' => RequirementKind::File,
                'description' => 'Transkrip semester terakhir yang menunjukkan IPK saat ini.',
            ],
            [
                'slug' => 'recommendation-letter',
                'name' => 'Surat rekomendasi',
                'kind' => RequirementKind::File,
                'description' => 'Surat bertanda tangan dari dosen pembimbing atau dosen pengajar.',
            ],
            [
                'slug' => 'essay',
                'name' => 'Esai / pernyataan pribadi',
                'kind' => RequirementKind::File,
                'description' => 'Unggah esai Anda berisi alasan mendaftar dan rencana menggunakan beasiswa ini.',
            ],
            [
                'slug' => 'achievement-certificate',
                'name' => 'Sertifikat prestasi',
                'kind' => RequirementKind::File,
                'description' => 'Bukti lomba, publikasi, atau prestasi lainnya.',
            ],
            [
                'slug' => 'income-statement',
                'name' => 'Bukti penghasilan keluarga',
                'kind' => RequirementKind::File,
                'description' => 'Slip gaji atau surat keterangan penghasilan dari kelurahan/desa.',
            ],
        ];

        foreach ($types as $type) {
            RequirementType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
