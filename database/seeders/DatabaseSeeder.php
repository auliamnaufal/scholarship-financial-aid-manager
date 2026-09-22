<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\RequirementKind;
use App\Models\Application;
use App\Models\GuardianPhone;
use App\Models\Program;
use App\Models\RequirementType;
use App\Models\Review;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Fixed semester used across all seeded applications/disbursements.
     */
    private const SEMESTER = '2026-1';

    public function run(): void
    {
        // 0. The menu of things a scholarship can ask an applicant for.
        $this->call(RequirementTypeSeeder::class);

        // 1. Roles
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $reviewerRole = Role::firstOrCreate(['name' => 'reviewer']);
        $coordinatorRole = Role::firstOrCreate(['name' => 'coordinator']);

        // 2. Students (~15) with profile + 0-2 guardian phones.
        // The first one gets a fixed, known email for manual login testing.
        $students = collect();

        $students->push(User::factory()->create([
            'name' => 'Siti Rahayu',
            'email' => 'student1@example.com',
        ]));

        $students = $students->concat(User::factory()->count(14)->create());

        $students->each(function (User $student) use ($studentRole) {
            $student->assignRole($studentRole);

            StudentProfile::factory()->create(['user_id' => $student->id]);

            GuardianPhone::factory()
                ->count(fake()->numberBetween(0, 2))
                ->create(['user_id' => $student->id]);
        });

        // 3. Staff: 3 dedicated coordinators + 4 reviewers (2 of whom also coordinate).
        // First of each group gets a fixed, known email.
        $dedicatedCoordinators = collect([
            User::factory()->create(['name' => 'Budi Santoso', 'email' => 'coordinator1@example.com']),
        ])->concat(User::factory()->count(2)->create());
        $dedicatedCoordinators->each(fn (User $user) => $user->assignRole($coordinatorRole));

        $reviewers = collect([
            User::factory()->create(['name' => 'Dewi Lestari', 'email' => 'reviewer1@example.com']),
            User::factory()->create(['name' => 'Agus Wijaya', 'email' => 'staff1@example.com']),
        ])->concat(User::factory()->count(2)->create());
        $reviewers->each(fn (User $user) => $user->assignRole($reviewerRole));

        // Overlap: staff1 plus one other reviewer also become coordinators.
        // reviewer1 (index 0) is kept as a pure reviewer for a clean demo login.
        $overlapReviewers = $reviewers->skip(1)->take(2);
        $overlapReviewers->each(fn (User $user) => $user->assignRole($coordinatorRole));

        $allCoordinators = $dedicatedCoordinators->concat($overlapReviewers)->values();

        // 4. Programs (5), one per coordinator, alternating type.
        $programs = collect();
        foreach ($allCoordinators as $index => $coordinator) {
            $type = $index % 2 === 0 ? 'need_based' : 'merit_based';

            $programs->push(Program::factory()->create([
                'coordinator_id' => $coordinator->id,
                'type' => $type,
                'max_family_income' => $type === 'need_based' ? fake()->randomFloat(2, 20000, 80000) : null,
                'min_gpa' => $type === 'merit_based' ? fake()->randomFloat(2, 3, 4) : null,
            ]));
        }

        // 4b. Requirements per programme. Deliberately uneven: only some
        // scholarships want a recommendation letter, which is the whole point
        // of keeping requirements in their own table.
        $requirementTypes = RequirementType::all()->keyBy('slug');

        // slug => is it compulsory
        $requirementSets = [
            ['cv' => true, 'transcript' => true],
            ['cv' => true, 'transcript' => true, 'essay' => true],
            ['cv' => true, 'transcript' => true, 'recommendation-letter' => true, 'essay' => true],
            ['transcript' => true, 'income-statement' => true, 'essay' => true, 'cv' => false],
            ['cv' => true, 'transcript' => true, 'achievement-certificate' => false],
        ];

        foreach ($programs as $index => $program) {
            $set = $requirementSets[$index % count($requirementSets)];

            foreach ($set as $slug => $isRequired) {
                $program->requirements()->create([
                    'requirement_type_id' => $requirementTypes[$slug]->id,
                    'is_required' => $isRequired,
                    'instructions' => null,
                ]);
            }
        }

        // 5. Applications (~20) across students/programs, unique (student, program, semester).
        $pairs = collect();
        foreach ($students as $student) {
            foreach ($programs as $program) {
                $pairs->push([$student, $program]);
            }
        }
        $pairs = $pairs->shuffle()->take(20)->values();

        $statusCycle = [
            ApplicationStatus::Submitted,
            ApplicationStatus::UnderReview,
            ApplicationStatus::Approved,
            ApplicationStatus::Rejected,
        ];

        $applications = collect();
        foreach ($pairs as $index => [$student, $program]) {
            $status = $statusCycle[$index % count($statusCycle)];

            $applications->push(Application::create([
                'student_id' => $student->id,
                'program_id' => $program->id,
                'semester' => self::SEMESTER,
                'submission_date' => now()->subDays(fake()->numberBetween(5, 60)),
                'status' => $status,
            ]));
        }

        // 5b. What each applicant supplied. Text answers get real prose; file
        // requirements get a one-page placeholder PDF written to the private
        // disk, so the reviewer's checklist and its download links behave in
        // the demo exactly as they do for a real upload.
        // A fresh seed starts from an empty upload directory; migrate:fresh
        // clears the tables but leaves whatever a previous run wrote to disk.
        Storage::disk('local')->deleteDirectory('applications');

        // $applications is a plain Collection, so the requirements are gathered
        // once here and keyed by programme rather than eager-loaded.
        $requirementsByProgram = Program::with('requirements.requirementType')
            ->get()
            ->keyBy('id')
            ->map(fn (Program $program) => $program->requirements);

        foreach ($applications as $application) {
            foreach ($requirementsByProgram[$application->program_id] as $requirement) {
                // Leave roughly one optional item in four unanswered, so the
                // reviewer's checklist has something to actually show.
                if (! $requirement->is_required && fake()->boolean(25)) {
                    continue;
                }

                $type = $requirement->requirementType;

                if ($type->kind === RequirementKind::Text) {
                    $application->documents()->create([
                        'requirement_type_id' => $type->id,
                        'body' => fake()->paragraphs(2, true),
                    ]);

                    continue;
                }

                $name = $type->slug.'-'.$application->student_id.'.pdf';
                $path = "applications/{$application->id}/{$name}";
                $pdf = $this->placeholderPdf($type->name, $application->student->name);

                Storage::disk('local')->put($path, $pdf);

                $application->documents()->create([
                    'requirement_type_id' => $type->id,
                    'file_path' => $path,
                    'original_name' => $name,
                    'mime_type' => 'application/pdf',
                    'size_bytes' => strlen($pdf),
                ]);
            }
        }

        // 5c. One cancelled application, so the withdrawn state is visible in
        // the demo without having to click through the flow.
        $cancelled = $applications->firstWhere('status', ApplicationStatus::Submitted);

        if ($cancelled) {
            $cancelled->update([
                'status' => ApplicationStatus::Cancelled,
                'cancelled_at' => now()->subDays(2),
            ]);
        }

        // 6. Reviews for under_review / approved / rejected applications.
        foreach ($applications as $application) {
            // Nothing to review on an application still waiting to be claimed,
            // or on one the student has withdrawn.
            if (in_array($application->status, [ApplicationStatus::Submitted, ApplicationStatus::Cancelled], true)) {
                continue;
            }

            $score = match ($application->status) {
                ApplicationStatus::Approved => fake()->numberBetween(75, 100),
                ApplicationStatus::Rejected => fake()->numberBetween(0, 50),
                default => fake()->numberBetween(50, 85),
            };

            Review::factory()->create([
                'reviewer_id' => $reviewers->random()->id,
                'application_id' => $application->id,
                'score' => $score,
            ]);
        }

        // 7. Awards and disbursements for approved applications. Each award is
        // paid in instalments and left part-paid, and the running total below
        // keeps the awards within the programme's budget.
        $budgetLeft = $programs->mapWithKeys(fn (Program $program) => [$program->id => (float) $program->budget]);

        foreach ($applications->where('status', ApplicationStatus::Approved) as $application) {
            $award = round(min(fake()->randomFloat(2, 4000, 12000), $budgetLeft[$application->program_id]), 2);

            if ($award <= 0) {
                continue;
            }

            $budgetLeft[$application->program_id] -= $award;
            // Decimal columns are given strings: brick/math, behind the
            // `decimal:2` cast, deprecates being handed floats.
            $application->update(['awarded_amount' => (string) $award]);

            // An instalment is a third of the award, so one or two of them
            // always leave a balance outstanding — which is exactly what the
            // "still to disburse" figures exist to show.
            $instalments = fake()->numberBetween(1, 2);
            $amount = round($award / 3, 2);

            for ($seq = 1; $seq <= $instalments; $seq++) {
                $application->disbursements()->create([
                    'seq_no' => $seq,
                    'amount' => (string) $amount,
                    'disbursement_date' => now()->subDays(fake()->numberBetween(1, 30)),
                    'semester' => self::SEMESTER,
                ]);
            }
        }

        // Plain registered user with no role, for exercising the "no role assigned" dashboard state.
        User::factory()->create([
            'name' => 'Plain User',
            'email' => 'user@example.com',
        ]);
    }

    /**
     * A one-page PDF standing in for an applicant's upload, written out by
     * hand so the seeder needs no PDF library and the demo's download links
     * open something real.
     */
    private function placeholderPdf(string $title, string $student): string
    {
        $line = str_replace(['(', ')', '\\'], '', "{$title} — {$student} (sample document)");

        $objects = [
            "<< /Type /Catalog /Pages 2 0 R >>",
            "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>",
            "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        ];

        $stream = "BT /F1 14 Tf 60 760 Td ({$line}) Tj ET";
        $objects[] = "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $startxref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$startxref}\n%%EOF";

        return $pdf;
    }
}
