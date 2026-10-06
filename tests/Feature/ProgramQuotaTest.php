<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ProgramPhase;
use App\Models\Application;
use App\Models\Program;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProgramQuotaTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    private function program(User $coordinator, array $attributes = []): Program
    {
        return Program::factory()->create(array_merge([
            'coordinator_id' => $coordinator->id,
            'budget' => '60000000',
            'quota' => 12,
            'allows_other_scholarships' => true,
        ], $attributes));
    }

    private function underReview(Program $program): Application
    {
        $application = Application::factory()->create([
            'student_id' => $this->user('student')->id,
            'program_id' => $program->id,
            'status' => ApplicationStatus::UnderReview,
        ]);
        Review::factory()->create(['application_id' => $application->id]);

        return $application;
    }

    // ---- the share each recipient gets ----

    public function test_the_budget_is_shared_equally_among_the_quota(): void
    {
        $program = $this->program($this->user('coordinator'));

        $this->assertSame(5000000.0, $program->awardPerRecipient());
    }

    public function test_the_share_is_rounded_down_to_a_whole_rupiah(): void
    {
        $program = $this->program($this->user('coordinator'), ['budget' => '100000000', 'quota' => 3]);

        $this->assertSame(33333333.0, $program->awardPerRecipient());
    }

    public function test_approving_gives_the_share_without_anyone_typing_an_amount(): void
    {
        $coordinator = $this->user('coordinator');
        $application = $this->underReview($this->program($coordinator));

        $this->actingAs($coordinator)
            ->post("/coordinator/applications/{$application->id}/approve")
            ->assertRedirect(route('coordinator.applications.show', $application));

        $application->refresh();
        $this->assertSame(ApplicationStatus::Approved, $application->status);
        $this->assertSame('5000000.00', $application->awarded_amount);
    }

    public function test_every_recipient_receives_the_same_amount(): void
    {
        $coordinator = $this->user('coordinator');
        $program = $this->program($coordinator);
        $first = $this->underReview($program);
        $second = $this->underReview($program);

        $this->actingAs($coordinator)->post("/coordinator/applications/{$first->id}/approve");
        $this->actingAs($coordinator)->post("/coordinator/applications/{$second->id}/approve");

        $this->assertSame($first->refresh()->awarded_amount, $second->refresh()->awarded_amount);
    }

    // ---- the quota ----

    public function test_a_full_scholarship_approves_no_one_else(): void
    {
        $coordinator = $this->user('coordinator');
        $program = $this->program($coordinator, ['quota' => 2]);

        foreach ([1, 2] as $_) {
            $application = $this->underReview($program);
            $this->actingAs($coordinator)->post("/coordinator/applications/{$application->id}/approve");
        }

        $third = $this->underReview($program);

        $this->actingAs($coordinator)
            ->post("/coordinator/applications/{$third->id}/approve")
            ->assertSessionHasErrors('approval');

        $this->assertSame(ApplicationStatus::UnderReview, $third->refresh()->status);
        $this->assertSame(0, $program->slotsLeft());
    }

    public function test_a_rejection_does_not_use_up_a_place(): void
    {
        $coordinator = $this->user('coordinator');
        $program = $this->program($coordinator, ['quota' => 1]);
        $rejected = $this->underReview($program);
        $next = $this->underReview($program);

        $this->actingAs($coordinator)->post("/coordinator/applications/{$rejected->id}/reject");
        $this->assertSame(1, $program->slotsLeft());

        $this->actingAs($coordinator)->post("/coordinator/applications/{$next->id}/approve")->assertSessionHasNoErrors();
        $this->assertSame(ApplicationStatus::Approved, $next->refresh()->status);
    }

    public function test_the_application_page_shows_the_share_and_the_places_taken(): void
    {
        $coordinator = $this->user('coordinator');
        $program = $this->program($coordinator, ['quota' => 4, 'budget' => '20000000']);
        $application = $this->underReview($program);

        $this->actingAs($coordinator)
            ->get(route('coordinator.applications.show', $application))
            ->assertOk()
            ->assertSee('Rp 5.000.000')
            ->assertSee('0 / 4');
    }

    public function test_the_approve_button_is_disabled_when_the_scholarship_is_full(): void
    {
        $coordinator = $this->user('coordinator');
        $program = $this->program($coordinator, ['quota' => 1]);
        $this->underReview($program)->update(['status' => ApplicationStatus::Approved, 'awarded_amount' => '60000000']);
        $waiting = $this->underReview($program);

        $html = $this->actingAs($coordinator)
            ->get(route('coordinator.applications.show', $waiting))
            ->assertOk()
            ->assertSee('sudah memiliki 1 penerima')
            ->getContent();

        $this->assertMatchesRegularExpression('/<button[^>]*\sdisabled(=|\s|>)/s', $html);
    }

    // ---- the coordinator's form ----

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Beasiswa Uji',
            'type' => 'merit_based',
            'funding_source' => 'Dana Alumni',
            'budget' => '60000000',
            'quota' => '12',
            'application_deadline' => '2026-11-01',
            'review_deadline' => '2026-11-15',
            'announcement_date' => '2026-11-22',
            'min_gpa' => '3.25',
        ], $overrides);
    }

    public function test_a_coordinator_sets_the_quota_and_the_timeline(): void
    {
        $this->actingAs($this->user('coordinator'))
            ->post('/coordinator/programs', $this->payload())
            ->assertRedirect('/coordinator/programs');

        $program = Program::where('name', 'Beasiswa Uji')->firstOrFail();
        $this->assertSame(12, $program->quota);
        $this->assertSame('2026-11-15', $program->review_deadline->format('Y-m-d'));
        $this->assertSame('2026-11-22', $program->announcement_date->format('Y-m-d'));
    }

    public function test_the_quota_must_be_at_least_one(): void
    {
        $this->actingAs($this->user('coordinator'))
            ->post('/coordinator/programs', $this->payload(['quota' => '0']))
            ->assertSessionHasErrors('quota');
    }

    public function test_review_must_end_after_registration_closes(): void
    {
        $this->actingAs($this->user('coordinator'))
            ->post('/coordinator/programs', $this->payload(['review_deadline' => '2026-11-01']))
            ->assertSessionHasErrors('review_deadline');
    }

    public function test_recipients_cannot_be_announced_before_review_ends(): void
    {
        $this->actingAs($this->user('coordinator'))
            ->post('/coordinator/programs', $this->payload(['announcement_date' => '2026-11-10']))
            ->assertSessionHasErrors('announcement_date');
    }

    public function test_the_form_explains_the_share_each_recipient_gets(): void
    {
        $this->actingAs($this->user('coordinator'))
            ->get('/coordinator/programs/create')
            ->assertOk()
            ->assertSee('Jumlah penerima')
            ->assertSee('Anggaran dibagi rata: tiap penerima mendapat');
    }

    // ---- the timeline ----

    private function timeline(): Program
    {
        return Program::factory()->make([
            'application_deadline' => '2026-11-01',
            'review_deadline' => '2026-11-15',
            'announcement_date' => '2026-11-22',
        ]);
    }

    public function test_the_phase_follows_the_dates(): void
    {
        $program = $this->timeline();

        foreach ([
            '2026-10-20' => ProgramPhase::Registration,
            '2026-11-01' => ProgramPhase::Registration,
            '2026-11-02' => ProgramPhase::Review,
            '2026-11-15' => ProgramPhase::Review,
            '2026-11-16' => ProgramPhase::Acceptance,
            '2026-11-22' => ProgramPhase::Acceptance,
            '2026-11-23' => ProgramPhase::Completed,
        ] as $day => $expected) {
            $this->travelTo($day);
            $this->assertSame($expected, $program->phase(), "on {$day}");
        }
    }

    public function test_a_programme_without_a_timeline_still_has_a_phase(): void
    {
        $program = Program::factory()->make([
            'application_deadline' => '2026-11-01',
            'review_deadline' => null,
            'announcement_date' => null,
        ]);

        $this->travelTo('2026-11-02');

        $this->assertSame(ProgramPhase::Completed, $program->phase());
    }

    public function test_the_detail_page_shows_the_quota_the_share_and_the_schedule(): void
    {
        $program = $this->program($this->user('coordinator'), [
            'application_deadline' => now()->addDays(10),
            'review_deadline' => now()->addDays(20),
            'announcement_date' => now()->addDays(30),
        ]);

        $this->get("/scholarships/{$program->id}")
            ->assertOk()
            ->assertSee('Kuota penerima')
            ->assertSee('12 orang')
            ->assertSee('Rp 5.000.000')
            ->assertSee('Jadwal')
            ->assertSee('Sedang berjalan');
    }

    public function test_the_listing_shows_the_share_per_recipient(): void
    {
        $this->program($this->user('coordinator'), ['application_deadline' => now()->addDays(10)]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Per penerima')
            ->assertSee('12 orang');
    }

    public function test_the_coordinator_list_shows_the_stage_and_the_places_taken(): void
    {
        $coordinator = $this->user('coordinator');
        $program = $this->program($coordinator, ['application_deadline' => now()->addDays(10)]);
        $this->underReview($program)->update(['status' => ApplicationStatus::Approved, 'awarded_amount' => '5000000']);

        $this->actingAs($coordinator)
            ->get('/coordinator/programs')
            ->assertOk()
            ->assertSee('Pendaftaran')
            ->assertSee('1 / 12');
    }
}
