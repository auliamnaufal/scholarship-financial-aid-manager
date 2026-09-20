<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\GuardianPhone;
use App\Models\Program;
use App\Models\Review;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Fixed semester used across all seeded applications/disbursements.
     */
    private const SEMESTER = '2026-1';

    public function run(): void
    {
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

        // 6. Reviews for under_review / approved / rejected applications.
        foreach ($applications as $application) {
            if ($application->status === ApplicationStatus::Submitted) {
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

        // 7. Disbursements for approved applications.
        foreach ($applications->where('status', ApplicationStatus::Approved) as $application) {
            $count = fake()->numberBetween(1, 2);

            for ($seq = 1; $seq <= $count; $seq++) {
                $application->disbursements()->create([
                    'seq_no' => $seq,
                    'amount' => fake()->randomFloat(2, 500, 5000),
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
}
