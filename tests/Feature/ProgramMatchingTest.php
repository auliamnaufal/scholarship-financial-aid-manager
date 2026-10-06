<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Program;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\Eligibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProgramMatchingTest extends TestCase
{
    use RefreshDatabase;

    private function student(?array $profile = ['gpa' => 3.50, 'family_income' => 3000000]): User
    {
        $student = User::factory()->create();
        $student->assignRole(Role::findOrCreate('student'));

        if ($profile !== null) {
            $student->studentProfile()->create(array_merge(['year_enrolled' => 2022], $profile));
        }

        return $student->refresh();
    }

    private function meritProgram(float $minGpa = 3.25): Program
    {
        return Program::factory()->create([
            'type' => 'merit_based',
            'min_gpa' => $minGpa,
            'max_family_income' => null,
            'application_deadline' => now()->addDays(30),
        ]);
    }

    private function needProgram(float $maxIncome = 4000000): Program
    {
        return Program::factory()->create([
            'type' => 'need_based',
            'min_gpa' => null,
            'max_family_income' => $maxIncome,
            'application_deadline' => now()->addDays(30),
        ]);
    }

    private function openProgramsOf(int $count): void
    {
        Program::factory()->count($count)->sequence(
            fn ($sequence) => ['application_deadline' => now()->addDays(10 + $sequence->index)],
        )->create();
    }

    public function test_a_visitor_sees_only_a_preview_of_the_catalogue(): void
    {
        $this->openProgramsOf(8);

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Lihat Lebih Banyak');

        // Five scholarships, and the sixth slot is the "see more" card.
        $this->assertSame(5, substr_count($response->getContent(), '<article'));
    }

    public function test_a_signed_in_student_sees_the_whole_catalogue(): void
    {
        $this->openProgramsOf(8);

        $response = $this->actingAs($this->student())->get('/')->assertOk()->assertDontSee('Lihat Lebih Banyak');

        $this->assertSame(8, substr_count($response->getContent(), '<article'));
    }

    public function test_a_catalogue_that_fills_the_grid_exactly_shows_everything(): void
    {
        $this->openProgramsOf(6);

        $response = $this->get('/')->assertOk()->assertDontSee('Lihat Lebih Banyak');

        $this->assertSame(6, substr_count($response->getContent(), '<article'));
    }

    public function test_guest_sees_no_match_badges(): void
    {
        $this->meritProgram();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Cocok untuk Anda');
    }

    public function test_a_student_who_meets_the_gpa_is_told_the_scholarship_fits(): void
    {
        $this->meritProgram(3.25);

        $this->actingAs($this->student(['gpa' => 3.50, 'family_income' => 3000000]))
            ->get('/')
            ->assertOk()
            ->assertSee('Cocok untuk Anda');
    }

    public function test_a_student_below_the_gpa_cutoff_does_not_fit(): void
    {
        $this->meritProgram(3.75);

        $this->actingAs($this->student(['gpa' => 3.10, 'family_income' => 3000000]))
            ->get('/')
            ->assertOk()
            ->assertSee('Belum memenuhi syarat')
            ->assertDontSee('Cocok untuk Anda');
    }

    public function test_a_student_over_the_income_cap_does_not_fit(): void
    {
        $this->needProgram(3500000);

        $this->actingAs($this->student(['gpa' => 3.90, 'family_income' => 6000000]))
            ->get('/')
            ->assertSee('Belum memenuhi syarat');
    }

    public function test_a_student_without_biodata_is_asked_to_complete_it(): void
    {
        $this->needProgram();

        $this->actingAs($this->student(null))
            ->get('/')
            ->assertSee('Lengkapi biodata');
    }

    public function test_a_missing_family_income_makes_a_means_tested_match_incomplete(): void
    {
        $program = $this->needProgram();

        $result = Eligibility::check($program, new StudentProfile(['gpa' => 3.8]));

        $this->assertSame(Eligibility::INCOMPLETE, $result['state']);
    }

    public function test_a_programme_already_applied_to_is_marked_as_such(): void
    {
        $program = $this->meritProgram();
        $student = $this->student();
        Application::factory()->create(['student_id' => $student->id, 'program_id' => $program->id]);

        $this->actingAs($student)->get('/')->assertSee('Sudah Anda daftar');
    }

    public function test_a_withdrawn_application_does_not_count_as_applied(): void
    {
        $program = $this->meritProgram();
        $student = $this->student();
        Application::factory()->create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'status' => ApplicationStatus::Cancelled,
        ]);

        $this->actingAs($student)->get('/')
            ->assertDontSee('Sudah Anda daftar')
            ->assertSee('Cocok untuk Anda');
    }

    public function test_the_detail_page_explains_each_check(): void
    {
        $program = $this->meritProgram(3.25);

        $this->actingAs($this->student(['gpa' => 3.50, 'family_income' => 3000000]))
            ->get("/scholarships/{$program->id}")
            ->assertOk()
            ->assertSee('Cocok untuk Anda')
            ->assertSee('IPK Anda 3,50 memenuhi syarat minimal 3,25.');
    }

    public function test_the_detail_page_names_the_reason_when_it_does_not_fit(): void
    {
        $program = $this->needProgram(3500000);

        $this->actingAs($this->student(['gpa' => 3.50, 'family_income' => 5000000]))
            ->get("/scholarships/{$program->id}")
            ->assertSee('melebihi batas Rp 3.500.000');
    }

    public function test_the_detail_page_shows_no_match_box_to_guests(): void
    {
        $program = $this->meritProgram();

        $this->get("/scholarships/{$program->id}")
            ->assertOk()
            ->assertDontSee('Cocok untuk Anda');
    }

    public function test_staff_see_no_match_badges(): void
    {
        $this->meritProgram();
        $coordinator = User::factory()->create();
        $coordinator->assignRole(Role::findOrCreate('coordinator'));

        $this->actingAs($coordinator)->get('/')
            ->assertOk()
            ->assertDontSee('Cocok untuk Anda');
    }
}
