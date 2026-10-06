<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Program;
use App\Models\Review;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\Semester;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The inner-page polish: searchable paginated lists, a fixed list of
 * semesters, guardian phone numbers, staff account upkeep and deleting a
 * review.
 */
class InnerPagesTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    private function student(array $attributes = []): User
    {
        $student = $this->user('student', $attributes);
        StudentProfile::factory()->create(['user_id' => $student->id]);

        return $student;
    }

    // ---------- semester ----------

    public function test_semester_options_run_from_one_to_seven(): void
    {
        $options = Semester::options();

        $this->assertSame(range(1, 7), array_map('intval', array_keys($options)));
        $this->assertSame('Semester 1', $options['1']);
        $this->assertSame('Semester 7', $options['7']);
        $this->assertSame('Semester 9', Semester::label('9'));
    }

    public function test_a_semester_on_record_stays_in_the_list(): void
    {
        $this->assertArrayHasKey('2019-1', Semester::options('2019-1'));
        $this->assertSame('2019-1', Semester::options('2019-1')['2019-1']);
    }

    public function test_the_default_semester_follows_the_year_of_enrolment(): void
    {
        $profile = new StudentProfile(['year_enrolled' => 2024]);

        // The odd semester starts in August, the even one in February.
        $this->assertSame('5', Semester::forProfile($profile, Carbon::parse('2026-10-06')));
        $this->assertSame('4', Semester::forProfile($profile, Carbon::parse('2026-03-10')));
        $this->assertSame('1', Semester::forProfile($profile, Carbon::parse('2024-09-01')));
        $this->assertSame('1', Semester::forProfile(null));
        $this->assertSame('7', Semester::forProfile(new StudentProfile(['year_enrolled' => 2000]), Carbon::parse('2026-10-06')));
    }

    public function test_the_apply_form_offers_a_semester_dropdown_with_the_students_own_semester_chosen(): void
    {
        $program = Program::factory()->create();
        $student = $this->student();
        $student->studentProfile->update(['year_enrolled' => now()->year - 2]);
        $expected = Semester::forProfile($student->studentProfile->fresh());

        $this->actingAs($student)
            ->get(route('student.applications.create', $program))
            ->assertOk()
            ->assertSee('<select id="semester"', false)
            ->assertSee('Semester 1')
            ->assertSee('Semester 7')
            ->assertSee('value="'.$expected.'" selected', false);
    }

    public function test_a_semester_outside_the_list_is_rejected(): void
    {
        $program = Program::factory()->create(['application_deadline' => now()->addMonth()]);

        foreach (['Semester 5', '2026-1', '0', '8', '14', '5.5', ''] as $typed) {
            $this->actingAs($this->student())
                ->post(route('student.applications.store'), ['program_id' => $program->id, 'semester' => $typed])
                ->assertSessionHasErrors('semester');
        }
    }

    public function test_semesters_are_shown_as_semester_and_the_number(): void
    {
        $coordinator = $this->user('coordinator');
        $program = Program::factory()->create(['coordinator_id' => $coordinator->id]);
        Application::factory()->create(['program_id' => $program->id, 'semester' => '7']);

        $this->actingAs($coordinator)->get(route('coordinator.applications.index'))
            ->assertOk()
            ->assertSee('Semester 7');
    }

    public function test_old_year_term_semesters_are_converted_to_numbers(): void
    {
        $student = $this->student();
        $student->studentProfile->update(['year_enrolled' => 2024]);
        $application = Application::factory()->create(['student_id' => $student->id, 'semester' => '2026-1']);
        $second = Application::factory()->create(['student_id' => $student->id, 'semester' => '2026-2']);
        $disbursement = $application->disbursements()->create(['seq_no' => 1, 'amount' => '1000', 'disbursement_date' => '2026-09-01', 'semester' => '2026-1']);

        (require base_path('database/migrations/2026_10_06_100004_store_semesters_as_numbers.php'))->up();

        $this->assertSame('5', $application->fresh()->semester);
        $this->assertSame('6', $second->fresh()->semester);
        $this->assertSame('5', $disbursement->fresh()->semester);
    }

    // ---------- guardian phones ----------

    public function test_a_student_saves_several_guardian_numbers_and_blanks_are_dropped(): void
    {
        $student = $this->student();

        $this->actingAs($student)->put(route('student.biodata.update'), [
            'gpa' => 3.5,
            'year_enrolled' => 2023,
            'guardian_phones' => ['0812111', '', '0813222', '0812111'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['0812111', '0813222'], $student->guardianPhones()->orderBy('id')->pluck('phone_number')->all());
    }

    public function test_saving_biodata_replaces_the_guardian_numbers(): void
    {
        $student = $this->student();
        $student->guardianPhones()->create(['phone_number' => '0811']);

        $this->actingAs($student)->put(route('student.biodata.update'), [
            'gpa' => 3.5,
            'year_enrolled' => 2023,
            'guardian_phones' => ['0899'],
        ]);

        $this->assertSame(['0899'], $student->guardianPhones()->pluck('phone_number')->all());
    }

    public function test_more_than_five_guardian_numbers_are_refused(): void
    {
        $this->actingAs($this->student())->put(route('student.biodata.update'), [
            'gpa' => 3.5,
            'year_enrolled' => 2023,
            'guardian_phones' => ['1', '2', '3', '4', '5', '6'],
        ])->assertSessionHasErrors('guardian_phones');
    }

    public function test_the_biodata_page_lists_the_current_numbers(): void
    {
        $student = $this->student();
        $student->guardianPhones()->create(['phone_number' => '08123456789']);

        $this->actingAs($student)->get(route('student.biodata.edit'))
            ->assertOk()
            ->assertSee('08123456789');
    }

    public function test_a_coordinator_manages_a_students_guardian_numbers(): void
    {
        $coordinator = $this->user('coordinator');
        $student = $this->student();
        $student->guardianPhones()->create(['phone_number' => '0811']);

        $this->actingAs($coordinator)->get(route('coordinator.students.edit', $student))
            ->assertOk()->assertSee('0811');

        $this->actingAs($coordinator)->put(route('coordinator.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'gpa' => 3.2,
            'year_enrolled' => 2022,
            'guardian_phones' => ['0877', '0878'],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['0877', '0878'], $student->guardianPhones()->orderBy('id')->pluck('phone_number')->all());
    }

    // ---------- staff accounts ----------

    public function test_a_coordinator_edits_a_staff_account_and_keeps_the_password_when_blank(): void
    {
        $coordinator = $this->user('coordinator');
        $staff = $this->user('reviewer');
        $hash = $staff->password;

        $this->actingAs($coordinator)->put(route('coordinator.users.update', $staff), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'roles' => ['reviewer', 'coordinator'],
        ])->assertRedirect(route('coordinator.users.index'));

        $staff->refresh();
        $this->assertSame('Nama Baru', $staff->name);
        $this->assertSame('baru@example.com', $staff->email);
        $this->assertSame($hash, $staff->password);
        $this->assertTrue($staff->hasRole('coordinator'));
    }

    public function test_the_staff_edit_form_shows_current_values_and_roles(): void
    {
        $coordinator = $this->user('coordinator');
        $staff = $this->user('reviewer', ['name' => 'Bu Rina']);

        $this->actingAs($coordinator)->get(route('coordinator.users.edit', $staff))
            ->assertOk()
            ->assertSee('Bu Rina');
    }

    public function test_a_student_cannot_be_edited_through_the_staff_pages(): void
    {
        $coordinator = $this->user('coordinator');
        $student = $this->student();

        $this->actingAs($coordinator)->get(route('coordinator.users.edit', $student))->assertNotFound();
        $this->actingAs($coordinator)->delete(route('coordinator.users.destroy', $student))->assertNotFound();
    }

    public function test_you_cannot_remove_your_own_moderator_role_or_archive_yourself(): void
    {
        $coordinator = $this->user('coordinator');

        $this->actingAs($coordinator)->put(route('coordinator.users.update', $coordinator), [
            'name' => $coordinator->name,
            'email' => $coordinator->email,
            'roles' => ['reviewer'],
        ])->assertSessionHasErrors('roles');

        $this->actingAs($coordinator)->delete(route('coordinator.users.destroy', $coordinator))
            ->assertSessionHasErrors('user');

        $this->assertTrue($coordinator->fresh()->hasRole('coordinator'));
        $this->assertNull($coordinator->fresh()->deleted_at);
    }

    public function test_a_moderator_who_runs_scholarships_keeps_the_role_and_the_account(): void
    {
        $coordinator = $this->user('coordinator');
        $other = $this->user('coordinator');
        Program::factory()->create(['coordinator_id' => $other->id]);

        $this->actingAs($coordinator)->put(route('coordinator.users.update', $other), [
            'name' => $other->name,
            'email' => $other->email,
            'roles' => ['reviewer'],
        ])->assertSessionHasErrors('roles');

        $this->actingAs($coordinator)->delete(route('coordinator.users.destroy', $other))
            ->assertSessionHasErrors('user');

        $this->assertNull($other->fresh()->deleted_at);
    }

    public function test_staff_can_be_archived_and_restored(): void
    {
        $coordinator = $this->user('coordinator');
        $staff = $this->user('reviewer');

        $this->actingAs($coordinator)->delete(route('coordinator.users.destroy', $staff))
            ->assertRedirect(route('coordinator.users.index'));
        $this->assertNotNull($staff->fresh()->deleted_at);

        $this->actingAs($coordinator)->get(route('coordinator.users.index'))
            ->assertOk()->assertSee('Diarsipkan');

        $this->actingAs($coordinator)->post(route('coordinator.users.restore', $staff->id))
            ->assertRedirect(route('coordinator.users.index'));
        $this->assertNull($staff->fresh()->deleted_at);
    }

    public function test_an_archived_reviewer_cannot_sign_in_but_keeps_their_reviews(): void
    {
        $coordinator = $this->user('coordinator');
        $reviewer = $this->user('reviewer', ['password' => bcrypt('rahasia-123')]);
        $review = Review::factory()->create(['reviewer_id' => $reviewer->id]);

        $this->actingAs($coordinator)->delete(route('coordinator.users.destroy', $reviewer));

        $this->post('/logout');
        $this->post('/login', ['email' => $reviewer->email, 'password' => 'rahasia-123']);

        $this->assertGuest();
        $this->assertNotNull(Review::find($review->id));
    }

    public function test_a_reviewer_cannot_use_the_staff_pages(): void
    {
        $reviewer = $this->user('reviewer');

        $this->actingAs($reviewer)->get(route('coordinator.users.index'))->assertForbidden();
    }

    // ---------- deleting a review ----------

    private function underReview(User $reviewer): Application
    {
        $application = Application::factory()->create([
            'student_id' => $this->student()->id,
            'program_id' => Program::factory()->create(['coordinator_id' => $this->user('coordinator')->id])->id,
            'status' => ApplicationStatus::UnderReview,
        ]);
        Review::factory()->create(['application_id' => $application->id, 'reviewer_id' => $reviewer->id]);

        return $application;
    }

    public function test_a_reviewer_can_delete_their_review_and_write_a_new_one(): void
    {
        $reviewer = $this->user('reviewer');
        $application = $this->underReview($reviewer);
        $review = $application->reviews()->first();

        $this->actingAs($reviewer)->delete(route('reviewer.reviews.destroy', $review))
            ->assertRedirect(route('reviewer.applications.show', $application));

        $this->assertNull(Review::find($review->id));

        $this->actingAs($reviewer)->post(route('reviewer.reviews.store', $application), ['score' => 80, 'comments' => 'Ulang'])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $application->reviews()->count());
    }

    public function test_nobody_else_can_delete_a_review(): void
    {
        $reviewer = $this->user('reviewer');
        $application = $this->underReview($reviewer);
        $review = $application->reviews()->first();

        $this->actingAs($this->user('reviewer'))->delete(route('reviewer.reviews.destroy', $review))->assertForbidden();
        $this->assertNotNull(Review::find($review->id));
    }

    public function test_a_review_is_locked_once_the_coordinator_has_decided(): void
    {
        $reviewer = $this->user('reviewer');
        $application = $this->underReview($reviewer);
        $application->update(['status' => ApplicationStatus::Approved]);
        $review = $application->reviews()->first();

        $this->actingAs($reviewer)->delete(route('reviewer.reviews.destroy', $review))->assertForbidden();
        $this->assertNotNull(Review::find($review->id));
    }

    public function test_the_review_page_offers_the_delete_button_with_a_confirmation(): void
    {
        $reviewer = $this->user('reviewer');
        $application = $this->underReview($reviewer);

        $this->actingAs($reviewer)->get(route('reviewer.applications.show', $application))
            ->assertOk()
            ->assertSee('Hapus review saya')
            ->assertSee('data-confirm-title', false);
    }

    // ---------- search and pagination ----------

    public function test_applications_are_searchable_by_student_or_scholarship_and_paginated(): void
    {
        $coordinator = $this->user('coordinator');
        $program = Program::factory()->create(['coordinator_id' => $coordinator->id, 'name' => 'Beasiswa Unggulan Nusantara']);
        $other = Program::factory()->create(['coordinator_id' => $coordinator->id, 'name' => 'Beasiswa Prestasi Daerah']);

        $budi = $this->student(['name' => 'Budi Santoso']);
        Application::factory()->create(['student_id' => $budi->id, 'program_id' => $program->id]);
        Application::factory()->create(['student_id' => $this->student(['name' => 'Citra Lestari'])->id, 'program_id' => $other->id]);

        $this->actingAs($coordinator)->get(route('coordinator.applications.index', ['q' => 'budi']))
            ->assertOk()->assertSee('Budi Santoso')->assertDontSee('Citra Lestari');

        $this->actingAs($coordinator)->get(route('coordinator.applications.index', ['q' => 'Prestasi']))
            ->assertOk()->assertSee('Citra Lestari')->assertDontSee('Budi Santoso');

        $this->actingAs($coordinator)->get(route('coordinator.applications.index', ['q' => 'tidak-ada']))
            ->assertOk()->assertSee('Tidak ada pendaftaran yang cocok');

        Application::factory()->count(16)->create(['program_id' => $program->id]);
        $this->actingAs($coordinator)->get(route('coordinator.applications.index'))
            ->assertOk()->assertSee('page=2', false);
    }

    public function test_the_search_and_the_status_filter_work_together(): void
    {
        $coordinator = $this->user('coordinator');
        $program = Program::factory()->create(['coordinator_id' => $coordinator->id]);
        $budi = $this->student(['name' => 'Budi Santoso']);

        Application::factory()->create(['student_id' => $budi->id, 'program_id' => $program->id, 'status' => ApplicationStatus::Submitted, 'semester' => '5']);
        Application::factory()->create(['student_id' => $budi->id, 'program_id' => $program->id, 'status' => ApplicationStatus::Rejected, 'semester' => '6']);

        $response = $this->actingAs($coordinator)->get(route('coordinator.applications.index', ['q' => 'Budi', 'status' => 'rejected']));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('applications'));
        $this->assertSame('6', $response->viewData('applications')->first()->semester);
    }

    public function test_students_are_searchable_by_name_email_or_nim_and_filterable_by_archive(): void
    {
        $coordinator = $this->user('coordinator');
        $ani = $this->student(['name' => 'Ani Wijaya', 'email' => 'ani@kampus.test']);
        $ani->studentProfile->update(['nim' => '2301234']);
        $this->student(['name' => 'Dedi Kurnia']);
        $gone = $this->student(['name' => 'Eko Lama']);
        $gone->delete();

        $this->actingAs($coordinator)->get(route('coordinator.students.index', ['q' => 'wijaya']))
            ->assertSee('Ani Wijaya')->assertDontSee('Dedi Kurnia');
        $this->actingAs($coordinator)->get(route('coordinator.students.index', ['q' => 'kampus.test']))
            ->assertSee('Ani Wijaya')->assertDontSee('Dedi Kurnia');
        $this->actingAs($coordinator)->get(route('coordinator.students.index', ['q' => '2301234']))
            ->assertSee('Ani Wijaya')->assertDontSee('Dedi Kurnia');

        $this->actingAs($coordinator)->get(route('coordinator.students.index', ['show' => 'archived']))
            ->assertSee('Eko Lama')->assertDontSee('Ani Wijaya');
        $this->actingAs($coordinator)->get(route('coordinator.students.index', ['show' => 'active']))
            ->assertDontSee('Eko Lama')->assertSee('Ani Wijaya');

        $this->actingAs($coordinator)->get(route('coordinator.students.index', ['q' => 'zzz']))
            ->assertSee('Tidak ada mahasiswa yang cocok');
    }

    public function test_the_student_list_is_paginated(): void
    {
        $coordinator = $this->user('coordinator');
        User::factory()->count(16)->create()->each->assignRole(Role::findOrCreate('student'));

        $response = $this->actingAs($coordinator)->get(route('coordinator.students.index'));

        $response->assertOk()->assertSee('page=2', false);
        $this->assertCount(15, $response->viewData('students'));
    }

    public function test_scholarships_are_searchable_filterable_and_paginated(): void
    {
        $coordinator = $this->user('coordinator');
        Program::factory()->create(['coordinator_id' => $coordinator->id, 'name' => 'Beasiswa Garuda', 'type' => 'merit_based', 'funding_source' => 'Bank Nusantara']);
        Program::factory()->create(['coordinator_id' => $coordinator->id, 'name' => 'Bantuan Peduli', 'type' => 'need_based', 'funding_source' => 'Yayasan Harapan']);

        $this->actingAs($coordinator)->get(route('coordinator.programs.index', ['q' => 'garuda']))
            ->assertSee('Beasiswa Garuda')->assertDontSee('Bantuan Peduli');
        $this->actingAs($coordinator)->get(route('coordinator.programs.index', ['q' => 'Harapan']))
            ->assertSee('Bantuan Peduli')->assertDontSee('Beasiswa Garuda');
        $this->actingAs($coordinator)->get(route('coordinator.programs.index', ['type' => 'need_based']))
            ->assertSee('Bantuan Peduli')->assertDontSee('Beasiswa Garuda');
        $this->actingAs($coordinator)->get(route('coordinator.programs.index', ['q' => 'zzz']))
            ->assertSee('Tidak ada beasiswa yang cocok');

        Program::factory()->count(10)->create(['coordinator_id' => $coordinator->id]);
        $this->actingAs($coordinator)->get(route('coordinator.programs.index'))
            ->assertOk()->assertSee('page=2', false);
    }

    public function test_the_list_filters_do_not_leak_other_coordinators_scholarships(): void
    {
        $coordinator = $this->user('coordinator');
        $other = $this->user('coordinator');
        Program::factory()->create(['coordinator_id' => $other->id, 'name' => 'Beasiswa Rahasia Lain']);

        $this->actingAs($coordinator)->get(route('coordinator.programs.index', ['q' => 'Rahasia']))
            ->assertOk()->assertDontSee('Beasiswa Rahasia Lain');
    }

    public function test_the_plural_for_missing_biodata_fields_is_in_indonesian(): void
    {
        $this->assertSame('2 isian belum diisi', trans_choice('1 field missing|:count fields missing', 2, ['count' => 2]));
        $this->assertSame('1 isian belum diisi', trans_choice('1 field missing|:count fields missing', 1, ['count' => 1]));
    }
}
