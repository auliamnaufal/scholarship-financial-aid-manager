<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScholarshipFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_public_listing_and_detail(): void
    {
        $program = Program::factory()->create([
            'application_deadline' => now()->addDays(30),
        ]);

        $this->get('/')->assertOk()->assertSee($program->name);
        $this->get("/scholarships/{$program->id}")->assertOk()->assertSee($program->name);
    }

    public function test_guest_applying_is_sent_to_login_then_back_to_the_apply_form(): void
    {
        $program = Program::factory()->create();
        $applyUrl = route('student.applications.create', $program, absolute: false);

        // Guest hits the auth-gated apply route; Laravel stores it as "intended".
        $this->get($applyUrl)->assertRedirect(route('login', absolute: false));

        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));

        $this->post('/login', ['email' => $student->email, 'password' => 'password'])
            ->assertRedirect($applyUrl);
    }

    public function test_registration_assigns_the_student_role(): void
    {
        $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'newstudent@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertTrue(User::where('email', 'newstudent@example.com')->first()->hasRole('student'));
    }

    public function test_coordinator_can_create_a_reviewer_account(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));
        Role::findOrCreate('reviewer');

        $this->actingAs($coordinator)->post('/coordinator/users', [
            'name' => 'New Reviewer',
            'email' => 'reviewer.new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => ['reviewer'],
        ])->assertRedirect(route('coordinator.users.index'));

        $this->assertTrue(User::where('email', 'reviewer.new@example.com')->first()->hasRole('reviewer'));
    }

    public function test_reviewer_can_view_every_submission_regardless_of_status(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole(Role::findOrCreate('reviewer'));

        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));

        $program = Program::factory()->create();
        $application = $student->applications()->create([
            'program_id' => $program->id,
            'semester' => '2026-1',
            'submission_date' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($reviewer)
            ->get(route('reviewer.applications.show', $application))
            ->assertOk();
    }
}
