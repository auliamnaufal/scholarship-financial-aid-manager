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

class CoordinatorReviewerTest extends TestCase
{
    use RefreshDatabase;

    /** A coordinator who was never given the reviewer role. */
    private function coordinator(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('coordinator'));

        return $user;
    }

    private function reviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('reviewer'));

        return $user;
    }

    private function student(): User
    {
        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));

        return $student;
    }

    private function applicationTo(User $coordinator, ApplicationStatus $status = ApplicationStatus::Submitted): Application
    {
        $program = Program::factory()->create(['coordinator_id' => $coordinator->id]);

        return Application::factory()->create([
            'student_id' => $this->student()->id,
            'program_id' => $program->id,
            'status' => $status,
        ]);
    }

    public function test_every_coordinator_is_a_reviewer_but_not_every_reviewer_is_a_coordinator(): void
    {
        $this->assertTrue($this->coordinator()->isReviewer());
        $this->assertTrue($this->reviewer()->isReviewer());
        $this->assertFalse($this->student()->isReviewer());
        $this->assertFalse($this->reviewer()->hasRole('coordinator'));
    }

    public function test_a_coordinator_can_open_the_reviewer_dashboard(): void
    {
        $this->actingAs($this->coordinator())->get('/reviewer/dashboard')->assertOk();
    }

    public function test_a_reviewer_still_cannot_open_the_coordinator_area(): void
    {
        $this->actingAs($this->reviewer())->get('/coordinator/dashboard')->assertForbidden();
    }

    public function test_a_student_cannot_open_the_reviewer_dashboard(): void
    {
        $this->actingAs($this->student())->get('/reviewer/dashboard')->assertForbidden();
    }

    public function test_a_coordinator_can_claim_and_review_another_coordinators_application(): void
    {
        $other = $this->coordinator();
        $me = $this->coordinator();
        $application = $this->applicationTo($other);

        $this->actingAs($me)->post("/reviewer/applications/{$application->id}/claim")->assertRedirect();
        $this->assertSame(ApplicationStatus::UnderReview, $application->refresh()->status);

        $this->actingAs($me)->post("/reviewer/applications/{$application->id}/reviews", [
            'score' => 80,
            'comments' => 'Berkas lengkap.',
        ])->assertRedirect();

        $this->assertSame(1, Review::where('application_id', $application->id)->where('reviewer_id', $me->id)->count());
    }

    public function test_a_coordinator_cannot_claim_an_application_to_their_own_scholarship(): void
    {
        $me = $this->coordinator();
        $application = $this->applicationTo($me);

        $this->actingAs($me)->post("/reviewer/applications/{$application->id}/claim")->assertForbidden();

        $this->assertSame(ApplicationStatus::Submitted, $application->refresh()->status);
    }

    public function test_a_coordinator_cannot_score_their_own_scholarship(): void
    {
        $me = $this->coordinator();
        $application = $this->applicationTo($me, ApplicationStatus::UnderReview);

        $this->actingAs($me)->post("/reviewer/applications/{$application->id}/reviews", ['score' => 90])
            ->assertForbidden();

        $this->assertSame(0, Review::count());
    }

    public function test_a_plain_reviewer_may_review_any_scholarship(): void
    {
        $application = $this->applicationTo($this->coordinator(), ApplicationStatus::UnderReview);

        $this->actingAs($this->reviewer())->post("/reviewer/applications/{$application->id}/reviews", ['score' => 70])
            ->assertRedirect();

        $this->assertSame(1, Review::count());
    }

    public function test_the_work_queue_leaves_out_a_coordinators_own_scholarships(): void
    {
        $me = $this->coordinator();
        $mine = $this->applicationTo($me);
        $theirs = $this->applicationTo($this->coordinator());

        $claimable = $this->actingAs($me)->get('/reviewer/dashboard')->assertOk()->viewData('claimable');

        $this->assertTrue($claimable->contains('id', $theirs->id));
        $this->assertFalse($claimable->contains('id', $mine->id));
    }

    public function test_a_plain_reviewer_sees_every_application_in_the_queue(): void
    {
        $first = $this->applicationTo($this->coordinator());
        $second = $this->applicationTo($this->coordinator());

        $claimable = $this->actingAs($this->reviewer())->get('/reviewer/dashboard')->viewData('claimable');

        $this->assertTrue($claimable->contains('id', $first->id));
        $this->assertTrue($claimable->contains('id', $second->id));
    }

    public function test_a_coordinator_can_open_any_submission_as_a_reviewer(): void
    {
        $application = $this->applicationTo($this->coordinator());

        $this->actingAs($this->coordinator())
            ->get("/reviewer/applications/{$application->id}")
            ->assertOk();
    }

    public function test_a_coordinator_is_offered_both_dashboards(): void
    {
        $this->actingAs($this->coordinator())
            ->get('/dashboard')
            ->assertOk()
            ->assertViewIs('dashboard-picker');
    }

    public function test_a_plain_reviewer_goes_straight_to_the_reviewer_dashboard(): void
    {
        $this->actingAs($this->reviewer())
            ->get('/dashboard')
            ->assertRedirect(route('reviewer.dashboard'));
    }

    public function test_the_coordinator_sidebar_links_to_reviewing(): void
    {
        $this->actingAs($this->coordinator())
            ->get('/coordinator/programs')
            ->assertOk()
            ->assertSee('Nilai Pendaftaran');
    }

    public function test_a_student_sidebar_does_not_offer_reviewing(): void
    {
        $this->actingAs($this->student())
            ->get('/student/dashboard')
            ->assertOk()
            ->assertDontSee('Nilai Pendaftaran');
    }

    public function test_the_staff_list_shows_a_coordinator_as_a_reviewer_too(): void
    {
        // The staff list looks the reviewer role up by name, so it has to exist.
        Role::findOrCreate('reviewer');
        $viewer = $this->coordinator();
        $staff = $this->coordinator();

        $this->actingAs($viewer)
            ->get('/coordinator/users')
            ->assertOk()
            ->assertSee($staff->name)
            ->assertSee('Moderator otomatis juga menjadi reviewer.');
    }

    public function test_the_staff_form_tells_the_coordinator_about_the_rule(): void
    {
        $this->actingAs($this->coordinator())
            ->get('/coordinator/users/create')
            ->assertSee('Moderator otomatis juga menjadi reviewer.');
    }
}
