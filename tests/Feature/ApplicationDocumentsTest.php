<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Program;
use App\Models\RequirementType;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RequirementTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private function student(array $profile = []): User
    {
        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));
        $student->studentProfile()->create(array_merge([
            'gpa' => 3.50,
            'year_enrolled' => 2022,
            'family_income' => 30000,
            'bank_name' => 'BNI',
            'bank_account_number' => '1234567890',
        ], $profile));

        return $student->refresh();
    }

    private function programAskingFor(array $slugs, array $attributes = []): Program
    {
        $this->seed(RequirementTypeSeeder::class);

        $program = Program::factory()->create(array_merge([
            'application_deadline' => now()->addMonth(),
            'min_gpa' => null,
            'max_family_income' => null,
        ], $attributes));

        foreach ($slugs as $slug => $isRequired) {
            $program->requirements()->create([
                'requirement_type_id' => RequirementType::where('slug', $slug)->value('id'),
                'is_required' => $isRequired,
            ]);
        }

        return $program->load('requirements.requirementType');
    }

    public function test_applying_stores_uploads_and_written_answers(): void
    {
        Storage::fake('local');

        $student = $this->student();
        $program = $this->programAskingFor(['cv' => true, 'essay' => true]);

        $cvId = RequirementType::where('slug', 'cv')->value('id');
        $essayId = RequirementType::where('slug', 'essay')->value('id');

        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
            'answers' => [
                $cvId => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
                $essayId => 'I am applying because I want to finish my degree without debt.',
            ],
        ])->assertSessionHasNoErrors();

        $application = Application::firstOrFail();
        $this->assertCount(2, $application->documents);

        $cv = $application->documents->firstWhere('requirement_type_id', $cvId);
        $this->assertSame('cv.pdf', $cv->original_name);
        Storage::disk('local')->assertExists($cv->file_path);

        $essay = $application->documents->firstWhere('requirement_type_id', $essayId);
        $this->assertNull($essay->file_path);
        $this->assertStringContainsString('finish my degree', $essay->body);
    }

    public function test_a_compulsory_requirement_cannot_be_skipped(): void
    {
        $student = $this->student();
        $program = $this->programAskingFor(['cv' => true]);

        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
        ])->assertSessionHasErrors('answers.'.RequirementType::where('slug', 'cv')->value('id'));

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_an_optional_requirement_may_be_left_out(): void
    {
        $student = $this->student();
        $program = $this->programAskingFor(['recommendation-letter' => false]);

        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('applications', 1);
        $this->assertCount(0, Application::firstOrFail()->documents);
    }

    public function test_an_upload_must_be_a_document_within_the_size_limit(): void
    {
        Storage::fake('local');

        $student = $this->student();
        $program = $this->programAskingFor(['cv' => true]);
        $cvId = RequirementType::where('slug', 'cv')->value('id');

        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
            'answers' => [$cvId => UploadedFile::fake()->image('holiday.jpg')],
        ])->assertSessionHasErrors("answers.{$cvId}");

        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
            // 5 MB is the ceiling.
            'answers' => [$cvId => UploadedFile::fake()->create('cv.pdf', 6000, 'application/pdf')],
        ])->assertSessionHasErrors("answers.{$cvId}");
    }

    public function test_eligibility_is_checked_against_the_students_biodata(): void
    {
        $student = $this->student(['gpa' => 2.80, 'family_income' => 90000]);

        $meritProgram = $this->programAskingFor([], ['min_gpa' => 3.50, 'type' => 'merit_based']);
        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $meritProgram->id,
            'semester' => '2026-1',
        ])->assertSessionHasErrors('program_id');

        $needProgram = $this->programAskingFor([], ['max_family_income' => 40000, 'type' => 'need_based']);
        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $needProgram->id,
            'semester' => '2026-1',
        ])->assertSessionHasErrors('program_id');

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_uploads_are_private_and_only_reachable_through_the_policy(): void
    {
        Storage::fake('local');

        $student = $this->student();
        $program = $this->programAskingFor(['cv' => true]);
        $cvId = RequirementType::where('slug', 'cv')->value('id');

        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
            'answers' => [$cvId => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')],
        ]);

        $document = Application::firstOrFail()->documents->first();

        $this->actingAs($student)->get("/documents/{$document->id}")->assertOk();

        // Another student has no business reading this transcript.
        $this->actingAs($this->student())
            ->get("/documents/{$document->id}")
            ->assertForbidden();
    }

    public function test_a_student_can_withdraw_until_a_decision_is_made(): void
    {
        $student = $this->student();
        $program = $this->programAskingFor([]);

        $application = Application::create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'semester' => '2026-1',
            'submission_date' => now(),
            'status' => ApplicationStatus::UnderReview,
        ]);

        $this->actingAs($student)
            ->post("/student/applications/{$application->id}/cancel")
            ->assertRedirect(route('student.dashboard'));

        $application->refresh();
        $this->assertSame(ApplicationStatus::Cancelled, $application->status);
        $this->assertNotNull($application->cancelled_at);

        // Withdrawing again, now that it is decided, is refused.
        $application->update(['status' => ApplicationStatus::Approved]);
        $this->actingAs($student)
            ->post("/student/applications/{$application->id}/cancel")
            ->assertForbidden();
    }

    public function test_a_reviewer_can_revise_their_score_until_the_coordinator_decides(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole(Role::findOrCreate('reviewer'));

        $student = $this->student();
        $program = $this->programAskingFor([]);

        $application = Application::create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'semester' => '2026-1',
            'submission_date' => now(),
            'status' => ApplicationStatus::UnderReview,
        ]);

        $review = Review::create([
            'reviewer_id' => $reviewer->id,
            'application_id' => $application->id,
            'score' => 60,
            'comments' => 'Borderline.',
        ]);

        $this->actingAs($reviewer)
            ->put("/reviewer/reviews/{$review->id}", ['score' => 85, 'comments' => 'Reread it; stronger than I thought.'])
            ->assertSessionHasNoErrors();

        $this->assertSame(85, $review->refresh()->score);

        // Once decided, the score the decision rested on is locked.
        $application->update(['status' => ApplicationStatus::Approved]);

        $this->actingAs($reviewer)
            ->put("/reviewer/reviews/{$review->id}", ['score' => 10])
            ->assertForbidden();

        $this->assertSame(85, $review->refresh()->score);
    }

    public function test_a_reviewer_cannot_edit_someone_elses_review(): void
    {
        $mine = User::factory()->create();
        $mine->assignRole(Role::findOrCreate('reviewer'));
        $theirs = User::factory()->create();
        $theirs->assignRole('reviewer');

        $student = $this->student();
        $program = $this->programAskingFor([]);

        $application = Application::create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'semester' => '2026-1',
            'submission_date' => now(),
            'status' => ApplicationStatus::UnderReview,
        ]);

        $review = Review::create([
            'reviewer_id' => $theirs->id,
            'application_id' => $application->id,
            'score' => 70,
        ]);

        $this->actingAs($mine)
            ->put("/reviewer/reviews/{$review->id}", ['score' => 0])
            ->assertForbidden();
    }
}
