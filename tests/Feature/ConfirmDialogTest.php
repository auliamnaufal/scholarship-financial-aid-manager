<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Program;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The dialog itself is Alpine in the browser; what the server owes it is the
 * markup: one dialog in the layout, and a data-confirm-* description on every
 * form that changes a status.
 */
class ConfirmDialogTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    private function application(User $coordinator, ApplicationStatus $status, ?User $student = null): Application
    {
        return Application::factory()->create([
            'student_id' => ($student ?? $this->user('student'))->id,
            'program_id' => Program::factory()->create(['coordinator_id' => $coordinator->id])->id,
            'status' => $status,
        ]);
    }

    public function test_the_app_layout_carries_the_dialog_and_no_native_confirm(): void
    {
        $html = $this->actingAs($this->user('coordinator'))->get('/coordinator/programs')->assertOk()->getContent();

        $this->assertStringContainsString('@open-confirm.window', $html);
        $this->assertStringNotContainsString('onsubmit="return confirm', $html);
    }

    public function test_withdrawing_is_a_button_that_asks_first(): void
    {
        $student = $this->user('student');
        $application = $this->application($this->user('coordinator'), ApplicationStatus::Submitted, $student);

        $this->actingAs($student)
            ->get(route('student.applications.show', $application))
            ->assertOk()
            ->assertSee('data-confirm-title="Tarik pendaftaran?"', false)
            ->assertSee('data-confirm-label="Ya, tarik pendaftaran"', false)
            ->assertSee('data-confirm-tone="danger"', false)
            ->assertSee('Tarik pendaftaran ini');
    }

    public function test_a_decided_application_offers_no_withdraw_button(): void
    {
        $student = $this->user('student');
        $application = $this->application($this->user('coordinator'), ApplicationStatus::Rejected, $student);

        $this->actingAs($student)
            ->get(route('student.applications.show', $application))
            ->assertOk()
            ->assertDontSee('data-confirm-title="Tarik pendaftaran?"', false);
    }

    public function test_approving_and_rejecting_ask_first(): void
    {
        $coordinator = $this->user('coordinator');
        $application = $this->application($coordinator, ApplicationStatus::UnderReview);
        Review::factory()->create(['application_id' => $application->id]);

        $this->actingAs($coordinator)
            ->get(route('coordinator.applications.show', $application))
            ->assertOk()
            ->assertSee('data-confirm-title="Setujui pendaftaran ini?"', false)
            ->assertSee('sama seperti setiap penerima beasiswa ini')
            ->assertSee('data-confirm-tone="success"', false)
            ->assertSee('data-confirm-title="Tolak pendaftaran ini?"', false)
            ->assertSee('data-confirm-tone="danger"', false);
    }

    public function test_claiming_asks_first(): void
    {
        $this->application($this->user('coordinator'), ApplicationStatus::Submitted);

        $this->actingAs($this->user('reviewer'))
            ->get('/reviewer/dashboard')
            ->assertOk()
            ->assertSee('data-confirm-title="Ambil pendaftaran ini?"', false);
    }

    public function test_archiving_a_programme_asks_first(): void
    {
        $coordinator = $this->user('coordinator');
        Program::factory()->create(['coordinator_id' => $coordinator->id]);

        $this->actingAs($coordinator)
            ->get('/coordinator/programs')
            ->assertOk()
            ->assertSee('data-confirm-title="Arsipkan program ini?"', false);
    }

    public function test_archiving_a_student_asks_first(): void
    {
        $coordinator = $this->user('coordinator');
        $student = $this->user('student');
        $student->studentProfile()->create(['gpa' => 3.2, 'year_enrolled' => 2022]);

        $this->actingAs($coordinator)
            ->get('/coordinator/students')
            ->assertOk()
            ->assertSee('data-confirm-title="Arsipkan mahasiswa ini?"', false);
    }

    public function test_confirming_still_runs_the_real_action(): void
    {
        $student = $this->user('student');
        $application = $this->application($this->user('coordinator'), ApplicationStatus::Submitted, $student);

        $this->actingAs($student)
            ->post(route('student.applications.cancel', $application))
            ->assertRedirect(route('student.dashboard'));

        $this->assertSame(ApplicationStatus::Cancelled, $application->refresh()->status);
    }
}
