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
                'name' => 'Curriculum vitae',
                'kind' => RequirementKind::File,
                'description' => 'A short CV listing education, activities and any work experience.',
            ],
            [
                'slug' => 'transcript',
                'name' => 'Academic transcript (KHS)',
                'kind' => RequirementKind::File,
                'description' => 'The most recent semester transcript, showing the current GPA.',
            ],
            [
                'slug' => 'recommendation-letter',
                'name' => 'Recommendation letter',
                'kind' => RequirementKind::File,
                'description' => 'A signed letter from an academic supervisor or lecturer.',
            ],
            [
                'slug' => 'essay',
                'name' => 'Essay / personal statement',
                'kind' => RequirementKind::Text,
                'description' => 'Why you are applying, and what you intend to do with the award.',
            ],
            [
                'slug' => 'achievement-certificate',
                'name' => 'Achievement certificate',
                'kind' => RequirementKind::File,
                'description' => 'Evidence of a competition, publication or other achievement.',
            ],
            [
                'slug' => 'income-statement',
                'name' => 'Proof of family income',
                'kind' => RequirementKind::File,
                'description' => 'A payslip or a statement of income from the local authority.',
            ],
        ];

        foreach ($types as $type) {
            RequirementType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
