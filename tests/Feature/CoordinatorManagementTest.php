<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\RequirementType;
use App\Models\User;
use Database\Seeders\RequirementTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CoordinatorManagementTest extends TestCase
{
    use RefreshDatabase;

    private function coordinator(): User
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));

        return $coordinator;
    }

    /** A programme's own columns, for posting to store/update. */
    private function programAttributes(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Scholarship',
            'type' => 'merit_based',
            'funding_source' => 'Alumni Fund',
            'budget' => '50000',
            'application_deadline' => now()->addMonth()->format('Y-m-d'),
            'min_gpa' => '3.00',
            'description' => 'A scholarship for testing.',
        ], $overrides);
    }

    public function test_coordinator_sees_only_their_own_programmes(): void
    {
        $coordinator = $this->coordinator();

        $mine = Program::factory()->create(['coordinator_id' => $coordinator->id]);
        $theirs = Program::factory()->create(['coordinator_id' => $this->coordinator()->id]);

        $this->actingAs($coordinator)
            ->get('/coordinator/programs')
            ->assertOk()
            ->assertSee($mine->name)
            ->assertDontSee($theirs->name);
    }

    public function test_creating_a_programme_stores_the_requirements_that_were_ticked(): void
    {
        $this->seed(RequirementTypeSeeder::class);

        $coordinator = $this->coordinator();
        $cv = RequirementType::where('slug', 'cv')->first();
        $letter = RequirementType::where('slug', 'recommendation-letter')->first();
        $essay = RequirementType::where('slug', 'essay')->first();

        $this->actingAs($coordinator)
            ->post('/coordinator/programs', $this->programAttributes([
                'requirements' => [
                    $cv->id => ['enabled' => '1', 'is_required' => '1'],
                    $essay->id => ['enabled' => '1', 'is_required' => '0', 'instructions' => 'Max 500 words.'],
                    // Left unticked: this scholarship wants no recommendation letter.
                    $letter->id => ['enabled' => '0', 'is_required' => '1'],
                ],
            ]))
            ->assertRedirect('/coordinator/programs');

        $program = Program::where('name', 'Test Scholarship')->firstOrFail();

        $this->assertCount(2, $program->requirements);
        $this->assertTrue($program->requirements->firstWhere('requirement_type_id', $cv->id)->is_required);
        $this->assertFalse($program->requirements->firstWhere('requirement_type_id', $essay->id)->is_required);
        $this->assertSame('Max 500 words.', $program->requirements->firstWhere('requirement_type_id', $essay->id)->instructions);
        $this->assertNull($program->requirements->firstWhere('requirement_type_id', $letter->id));
    }

    public function test_updating_a_programme_replaces_its_requirements(): void
    {
        $this->seed(RequirementTypeSeeder::class);

        $coordinator = $this->coordinator();
        $program = Program::factory()->create(['coordinator_id' => $coordinator->id]);

        $cv = RequirementType::where('slug', 'cv')->first();
        $transcript = RequirementType::where('slug', 'transcript')->first();

        $program->requirements()->create(['requirement_type_id' => $cv->id, 'is_required' => true]);

        $this->actingAs($coordinator)
            ->put("/coordinator/programs/{$program->id}", $this->programAttributes([
                'requirements' => [
                    // CV dropped, transcript added.
                    $transcript->id => ['enabled' => '1', 'is_required' => '1'],
                ],
            ]))
            ->assertRedirect('/coordinator/programs');

        $program->refresh()->load('requirements');

        $this->assertCount(1, $program->requirements);
        $this->assertSame($transcript->id, $program->requirements->first()->requirement_type_id);
    }

    public function test_archiving_a_programme_hides_it_but_keeps_its_applications(): void
    {
        $coordinator = $this->coordinator();
        $program = Program::factory()->create([
            'coordinator_id' => $coordinator->id,
            'application_deadline' => now()->addMonth(),
        ]);

        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));

        $application = Application::create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'semester' => '2026-1',
            'submission_date' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($coordinator)
            ->delete("/coordinator/programs/{$program->id}")
            ->assertRedirect('/coordinator/programs');

        $this->assertSoftDeleted($program);

        // The application survives; erasing the programme would have cascaded it away.
        $this->assertDatabaseHas('applications', ['id' => $application->id]);

        // And the scholarship is gone from the public listing.
        $this->get('/')->assertOk()->assertDontSee($program->name);
    }

    public function test_a_coordinator_cannot_archive_someone_elses_programme(): void
    {
        $program = Program::factory()->create(['coordinator_id' => $this->coordinator()->id]);

        $this->actingAs($this->coordinator())
            ->delete("/coordinator/programs/{$program->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted($program);
    }

    public function test_an_archived_programme_can_be_restored(): void
    {
        $coordinator = $this->coordinator();
        $program = Program::factory()->create(['coordinator_id' => $coordinator->id]);
        $program->delete();

        $this->actingAs($coordinator)
            ->post("/coordinator/programs/{$program->id}/restore")
            ->assertRedirect('/coordinator/programs');

        $this->assertNotSoftDeleted($program);
    }

    public function test_coordinator_can_create_a_student_with_biodata(): void
    {
        $coordinator = $this->coordinator();
        Role::findOrCreate('student');

        $this->actingAs($coordinator)
            ->post('/coordinator/students', [
                'name' => 'Ani Wijaya',
                'email' => 'ani@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'nim' => '2211001234',
                'faculty' => 'Fakultas Ilmu Komputer',
                'study_program' => 'Sistem Informasi',
                'gpa' => '3.55',
                'year_enrolled' => '2022',
                'phone' => '081234567890',
                'family_income' => '36000',
                'bank_name' => 'BNI',
                'bank_account_number' => '1234567890',
                'bank_account_holder' => 'Ani Wijaya',
            ])
            ->assertRedirect('/coordinator/students');

        $student = User::where('email', 'ani@example.com')->firstOrFail();

        $this->assertTrue($student->hasRole('student'));
        $this->assertSame('2211001234', $student->studentProfile->nim);
        $this->assertTrue($student->studentProfile->hasBankAccount());
    }

    public function test_editing_a_student_without_a_password_leaves_it_alone(): void
    {
        $coordinator = $this->coordinator();
        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));
        $student->studentProfile()->create(['gpa' => 3.0, 'year_enrolled' => 2022]);

        $original = $student->password;

        $this->actingAs($coordinator)
            ->put("/coordinator/students/{$student->id}", [
                'name' => 'Renamed Student',
                'email' => $student->email,
                'password' => '',
                'password_confirmation' => '',
                'gpa' => '3.75',
                'year_enrolled' => '2022',
            ])
            ->assertRedirect('/coordinator/students');

        $student->refresh();

        $this->assertSame('Renamed Student', $student->name);
        $this->assertSame($original, $student->password);
        $this->assertSame('3.75', $student->studentProfile->gpa);
    }

    public function test_archiving_a_student_keeps_their_records_and_blocks_sign_in(): void
    {
        $coordinator = $this->coordinator();
        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));

        $program = Program::factory()->create(['coordinator_id' => $coordinator->id]);
        $application = Application::create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'semester' => '2026-1',
            'submission_date' => now(),
            'status' => 'approved',
            'awarded_amount' => '5000',
        ]);
        $application->disbursements()->create([
            'seq_no' => 1,
            'amount' => '2500',
            'disbursement_date' => now(),
            'semester' => '2026-1',
        ]);

        $this->actingAs($coordinator)
            ->delete("/coordinator/students/{$student->id}")
            ->assertRedirect('/coordinator/students');

        $this->assertSoftDeleted($student);

        // Money already paid stays on the record.
        $this->assertDatabaseHas('disbursements', ['application_id' => $application->id, 'amount' => '2500.00']);

        // Step out of the coordinator's session, or the guest middleware on
        // /login would just bounce the request back.
        $this->post('/logout');

        $this->post('/login', ['email' => $student->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
