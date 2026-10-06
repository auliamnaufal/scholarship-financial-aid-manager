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

class ApplyRulesTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));
        $student->studentProfile()->create([
            'gpa' => 3.50,
            'year_enrolled' => 2022,
            'family_income' => 3000000,
            'bank_name' => 'BNI',
            'bank_account_number' => '1234567890',
        ]);

        return $student->refresh();
    }

    /** A scholarship with no thresholds and no documents, so only the application rules are in play. */
    private function program(bool $allowsOthers = true, array $attributes = []): Program
    {
        return Program::factory()->create(array_merge([
            'min_gpa' => null,
            'max_family_income' => null,
            'allows_other_scholarships' => $allowsOthers,
            'application_deadline' => now()->addDays(30),
        ], $attributes));
    }

    private function applicationFor(User $student, Program $program, ApplicationStatus $status): Application
    {
        return Application::create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'semester' => '2026-1',
            'submission_date' => now(),
            'status' => $status,
        ]);
    }

    private function apply(User $student, Program $program)
    {
        return $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
        ]);
    }

    // ---- at most two applications waiting at once ----

    public function test_a_student_can_have_two_applications_waiting_at_once(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);

        $this->apply($student, $this->program())->assertSessionHasNoErrors();

        $this->assertSame(2, Application::count());
    }

    public function test_a_third_waiting_application_is_refused(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);
        $this->applicationFor($student, $this->program(), ApplicationStatus::UnderReview);

        $this->apply($student, $this->program())->assertSessionHasErrors('program_id');

        $this->assertSame(2, Application::count());
    }

    public function test_a_decision_frees_a_place(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(), ApplicationStatus::Rejected);
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);

        $this->apply($student, $this->program())->assertSessionHasNoErrors();

        $this->assertSame(3, Application::count());
    }

    public function test_a_withdrawal_frees_a_place(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(), ApplicationStatus::Cancelled);
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);
        $this->assertSame(3, Application::count());

        $this->apply($student, $this->program())->assertSessionHasErrors('program_id');

        // Two are waiting; pulling one out makes room for another.
        Application::where('status', ApplicationStatus::Submitted)->first()->update(['status' => ApplicationStatus::Cancelled]);

        $this->apply($student, $this->program())->assertSessionHasNoErrors();
    }

    public function test_the_limit_belongs_to_each_student(): void
    {
        $busy = $this->student();
        $this->applicationFor($busy, $this->program(), ApplicationStatus::Submitted);
        $this->applicationFor($busy, $this->program(), ApplicationStatus::Submitted);

        $this->apply($this->student(), $this->program())->assertSessionHasNoErrors();
    }

    // ---- scholarships that do not allow another one ----

    public function test_holding_an_exclusive_scholarship_blocks_applying_to_others(): void
    {
        $student = $this->student();
        $exclusive = $this->program(false, ['name' => 'Beasiswa Eksklusif']);
        $this->applicationFor($student, $exclusive, ApplicationStatus::Approved);

        $this->apply($student, $this->program())
            ->assertSessionHasErrors(['program_id' => 'Anda sudah menerima Beasiswa Eksklusif, yang tidak membolehkan penerimanya menerima beasiswa lain.']);
    }

    public function test_an_exclusive_scholarship_cannot_be_applied_for_by_someone_who_already_receives_another(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(true, ['name' => 'Beasiswa Pertama']), ApplicationStatus::Approved);

        $this->apply($student, $this->program(false))->assertSessionHasErrors('program_id');

        $this->assertSame(1, Application::count());
    }

    public function test_two_scholarships_that_allow_it_can_be_held_together(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(true), ApplicationStatus::Approved);

        $this->apply($student, $this->program(true))->assertSessionHasNoErrors();
    }

    public function test_a_pending_application_to_an_exclusive_scholarship_does_not_block_others(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(false), ApplicationStatus::Submitted);

        $this->apply($student, $this->program(true))->assertSessionHasNoErrors();
    }

    public function test_a_coordinator_cannot_approve_what_would_break_an_exclusive_award(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));
        $student = $this->student();

        // The student already holds an award that allows no other.
        $this->applicationFor($student, $this->program(false, ['name' => 'Beasiswa Eksklusif']), ApplicationStatus::Approved);

        $program = $this->program(true, ['coordinator_id' => $coordinator->id]);
        $application = $this->applicationFor($student, $program, ApplicationStatus::UnderReview);
        Review::factory()->create(['application_id' => $application->id]);

        $this->actingAs($coordinator)
            ->post("/coordinator/applications/{$application->id}/approve")
            ->assertSessionHasErrors('approval');

        $this->assertSame(ApplicationStatus::UnderReview, $application->refresh()->status);
    }

    public function test_a_coordinator_can_approve_when_nothing_excludes_it(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));
        $student = $this->student();

        $program = $this->program(true, ['coordinator_id' => $coordinator->id]);
        $application = $this->applicationFor($student, $program, ApplicationStatus::UnderReview);
        Review::factory()->create(['application_id' => $application->id]);

        $this->actingAs($coordinator)
            ->post("/coordinator/applications/{$application->id}/approve")
            ->assertSessionHasNoErrors();

        $this->assertSame(ApplicationStatus::Approved, $application->refresh()->status);
    }

    // ---- where the rule shows up ----

    public function test_a_coordinator_can_set_the_rule_when_creating_a_scholarship(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));

        $this->actingAs($coordinator)->post('/coordinator/programs', [
            'name' => 'Beasiswa Uji',
            'type' => 'merit_based',
            'funding_source' => 'Dana Alumni',
            'budget' => '100000000',
            'quota' => '10',
            'application_deadline' => now()->addMonth()->toDateString(),
            'review_deadline' => now()->addMonths(2)->toDateString(),
            'announcement_date' => now()->addMonths(2)->addWeek()->toDateString(),
            'min_gpa' => '3.25',
            'allows_other_scholarships' => '0',
        ])->assertRedirect('/coordinator/programs');

        $this->assertFalse(Program::where('name', 'Beasiswa Uji')->firstOrFail()->allows_other_scholarships);
    }

    private function ruleCheckbox(string $html): string
    {
        preg_match('/<input type="checkbox" name="allows_other_scholarships"[^>]*>/s', $html, $match);

        return $match[0] ?? '';
    }

    public function test_the_programme_form_offers_the_rule_unticked_by_default(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));

        $html = $this->actingAs($coordinator)->get('/coordinator/programs/create')
            ->assertOk()
            ->assertSee('Penerima boleh menerima beasiswa lain')
            ->getContent();

        $this->assertNotSame('', $this->ruleCheckbox($html));
        $this->assertStringNotContainsString('checked', $this->ruleCheckbox($html));
    }

    public function test_the_edit_form_shows_an_exclusive_scholarship_unticked(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));
        $program = $this->program(false, ['coordinator_id' => $coordinator->id]);

        $html = $this->actingAs($coordinator)->get("/coordinator/programs/{$program->id}/edit")
            ->assertOk()
            ->getContent();

        $this->assertNotSame('', $this->ruleCheckbox($html));
        $this->assertStringNotContainsString('checked', $this->ruleCheckbox($html));
    }

    public function test_editing_a_scholarship_can_turn_the_rule_off(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));
        $program = $this->program(true, ['coordinator_id' => $coordinator->id, 'type' => 'merit_based', 'min_gpa' => 3.25]);

        $this->actingAs($coordinator)->put("/coordinator/programs/{$program->id}", [
            'name' => $program->name,
            'type' => 'merit_based',
            'funding_source' => $program->funding_source,
            'budget' => '100000000',
            'quota' => '10',
            'application_deadline' => now()->addMonth()->toDateString(),
            'review_deadline' => now()->addMonths(2)->toDateString(),
            'announcement_date' => now()->addMonths(2)->addWeek()->toDateString(),
            'min_gpa' => '3.25',
            'allows_other_scholarships' => '0',
        ])->assertRedirect('/coordinator/programs');

        $this->assertFalse($program->refresh()->allows_other_scholarships);
    }

    public function test_a_scholarship_does_not_allow_others_unless_told_otherwise(): void
    {
        $program = Program::create([
            'name' => 'Beasiswa Biasa',
            'type' => 'merit_based',
            'funding_source' => 'Dana Alumni',
            'budget' => '1000000',
            'application_deadline' => now()->addMonth(),
            'coordinator_id' => User::factory()->create()->id,
        ]);

        $this->assertFalse($program->refresh()->allows_other_scholarships);
    }

    public function test_the_detail_page_states_the_rule(): void
    {
        $exclusive = $this->program(false);
        $open = $this->program(true);

        $this->get("/scholarships/{$exclusive->id}")->assertSee('Tidak boleh bersamaan');
        $this->get("/scholarships/{$open->id}")->assertSee('Boleh diterima bersamaan');
    }

    public function test_the_detail_page_explains_why_a_student_cannot_apply_yet(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);
        $program = $this->program();

        $this->actingAs($student)
            ->get("/scholarships/{$program->id}")
            ->assertSee('Belum bisa mendaftar')
            ->assertSee('Anda sudah memiliki 2 pendaftaran yang sedang diproses');
    }

    public function test_the_apply_form_warns_and_disables_submitting(): void
    {
        $student = $this->student();
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);
        $this->applicationFor($student, $this->program(), ApplicationStatus::Submitted);
        $program = $this->program();

        $html = $this->actingAs($student)
            ->get("/student/programs/{$program->id}/apply")
            ->assertOk()
            ->assertSee('Anda sudah memiliki 2 pendaftaran yang sedang diproses')
            ->getContent();

        $this->assertMatchesRegularExpression('/<button[^>]*\sdisabled(=|\s|>)/s', $html);
    }

    public function test_the_apply_form_keeps_submitting_enabled_when_nothing_blocks(): void
    {
        $program = $this->program();

        $html = $this->actingAs($this->student())
            ->get("/student/programs/{$program->id}/apply")
            ->assertOk()
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/<button[^>]*\sdisabled(=|\s|>)/s', $html);
    }
}
