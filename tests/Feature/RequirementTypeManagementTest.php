<?php

namespace Tests\Feature;

use App\Enums\RequirementKind;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Program;
use App\Models\RequirementType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RequirementTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = $this->user('coordinator');
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    private function type(array $attributes = []): RequirementType
    {
        return RequirementType::create(array_merge([
            'name' => 'Proposal penelitian',
            'slug' => 'proposal-penelitian',
            'kind' => RequirementKind::File,
            'description' => 'Rencana penelitian singkat.',
        ], $attributes));
    }

    private function usedByAProgramme(RequirementType $type): Program
    {
        $program = Program::factory()->create(['coordinator_id' => $this->coordinator->id]);
        $program->requirements()->create(['requirement_type_id' => $type->id, 'is_required' => true]);

        return $program;
    }

    // ---- reading ----

    public function test_the_list_shows_every_type_and_what_uses_it(): void
    {
        $type = $this->type();
        $this->usedByAProgramme($type);
        $this->type(['name' => 'Surat pernyataan', 'slug' => 'surat-pernyataan']);

        $this->actingAs($this->coordinator)
            ->get(route('coordinator.requirement-types.index'))
            ->assertOk()
            ->assertSee('Proposal penelitian')
            ->assertSee('Surat pernyataan')
            ->assertSee('1 beasiswa');
    }

    public function test_only_coordinators_can_manage_requirement_types(): void
    {
        $type = $this->type();

        foreach (['student', 'reviewer'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)->get(route('coordinator.requirement-types.index'))->assertForbidden();
            $this->actingAs($user)->get(route('coordinator.requirement-types.create'))->assertForbidden();
            $this->actingAs($user)->post(route('coordinator.requirement-types.store'), ['name' => 'X', 'kind' => 'file'])->assertForbidden();
            $this->actingAs($user)->delete(route('coordinator.requirement-types.destroy', $type))->assertForbidden();
        }

        $this->assertModelExists($type);
    }

    public function test_a_guest_is_sent_to_log_in(): void
    {
        $this->get(route('coordinator.requirement-types.index'))->assertRedirect(route('login'));
    }

    public function test_the_sidebar_links_to_the_list(): void
    {
        $this->actingAs($this->coordinator)
            ->get(route('coordinator.programs.index'))
            ->assertSee(route('coordinator.requirement-types.index'))
            ->assertSee('Jenis Persyaratan');
    }

    // ---- creating ----

    public function test_a_coordinator_can_add_a_file_requirement(): void
    {
        $this->actingAs($this->coordinator)
            ->post(route('coordinator.requirement-types.store'), [
                'name' => 'Proposal Penelitian',
                'kind' => 'file',
                'description' => 'Rencana penelitian singkat.',
            ])
            ->assertRedirect(route('coordinator.requirement-types.index'))
            ->assertSessionHas('status', 'Persyaratan "Proposal Penelitian" berhasil dibuat.');

        $type = RequirementType::where('name', 'Proposal Penelitian')->firstOrFail();
        $this->assertSame('proposal-penelitian', $type->slug);
        $this->assertTrue($type->isFile());
    }

    public function test_a_coordinator_can_add_a_written_answer_requirement(): void
    {
        $this->actingAs($this->coordinator)
            ->post(route('coordinator.requirement-types.store'), ['name' => 'Motivasi singkat', 'kind' => 'text']);

        $this->assertFalse(RequirementType::where('name', 'Motivasi singkat')->firstOrFail()->isFile());
    }

    public function test_the_description_is_optional(): void
    {
        $this->actingAs($this->coordinator)
            ->post(route('coordinator.requirement-types.store'), ['name' => 'Foto', 'kind' => 'file'])
            ->assertSessionHasNoErrors();

        $this->assertNull(RequirementType::where('name', 'Foto')->firstOrFail()->description);
    }

    public function test_slugs_stay_unique(): void
    {
        $this->type(['name' => 'CV', 'slug' => 'cv']);

        $this->assertSame('cv-2', RequirementType::uniqueSlug('Cv'));

        $this->type(['name' => 'Cv 2', 'slug' => 'cv-2']);

        $this->assertSame('cv-3', RequirementType::uniqueSlug('CV'));
    }

    public function test_a_name_with_no_letters_still_gets_a_slug(): void
    {
        $this->assertSame('persyaratan', RequirementType::uniqueSlug('???'));
    }

    public function test_the_name_and_kind_are_required(): void
    {
        $this->actingAs($this->coordinator)
            ->post(route('coordinator.requirement-types.store'), [])
            ->assertSessionHasErrors(['name', 'kind']);
    }

    public function test_an_unknown_kind_is_refused(): void
    {
        $this->actingAs($this->coordinator)
            ->post(route('coordinator.requirement-types.store'), ['name' => 'Foto', 'kind' => 'video'])
            ->assertSessionHasErrors('kind');
    }

    public function test_two_requirements_cannot_share_a_name(): void
    {
        $this->type();

        $this->actingAs($this->coordinator)
            ->post(route('coordinator.requirement-types.store'), ['name' => 'Proposal penelitian', 'kind' => 'file'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, RequirementType::count());
    }

    // ---- editing ----

    public function test_a_coordinator_can_rename_and_describe_a_requirement(): void
    {
        $type = $this->type();

        $this->actingAs($this->coordinator)
            ->put(route('coordinator.requirement-types.update', $type), [
                'name' => 'Proposal riset',
                'kind' => 'file',
                'description' => 'Maksimal 3 halaman.',
            ])
            ->assertRedirect(route('coordinator.requirement-types.index'));

        $type->refresh();
        $this->assertSame('Proposal riset', $type->name);
        $this->assertSame('Maksimal 3 halaman.', $type->description);
        $this->assertSame('proposal-penelitian', $type->slug, 'the slug must not move');
    }

    public function test_saving_a_requirement_with_its_own_name_is_not_a_duplicate(): void
    {
        $type = $this->type();

        $this->actingAs($this->coordinator)
            ->put(route('coordinator.requirement-types.update', $type), ['name' => 'Proposal penelitian', 'kind' => 'file'])
            ->assertSessionHasNoErrors();
    }

    public function test_an_unused_requirement_can_change_how_it_is_answered(): void
    {
        $type = $this->type();

        $this->actingAs($this->coordinator)
            ->put(route('coordinator.requirement-types.update', $type), ['name' => $type->name, 'kind' => 'text'])
            ->assertSessionHasNoErrors();

        $this->assertFalse($type->refresh()->isFile());
    }

    public function test_a_requirement_in_use_cannot_change_how_it_is_answered(): void
    {
        $type = $this->type();
        $this->usedByAProgramme($type);

        $this->actingAs($this->coordinator)
            ->put(route('coordinator.requirement-types.update', $type), ['name' => 'Proposal riset', 'kind' => 'text'])
            ->assertSessionHasErrors('kind');

        $type->refresh();
        $this->assertTrue($type->isFile());
        $this->assertSame('Proposal penelitian', $type->name, 'nothing is saved when the form is refused');
    }

    public function test_a_requirement_in_use_can_still_be_renamed(): void
    {
        $type = $this->type();
        $this->usedByAProgramme($type);

        $this->actingAs($this->coordinator)
            ->put(route('coordinator.requirement-types.update', $type), ['name' => 'Proposal riset', 'kind' => 'file'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Proposal riset', $type->refresh()->name);
    }

    public function test_the_edit_page_shows_the_current_values(): void
    {
        $type = $this->type();

        $this->actingAs($this->coordinator)
            ->get(route('coordinator.requirement-types.edit', $type))
            ->assertOk()
            ->assertSee('value="Proposal penelitian"', false)
            ->assertSee('Rencana penelitian singkat.');
    }

    public function test_the_edit_page_warns_when_the_requirement_is_in_use(): void
    {
        $type = $this->type();
        $this->usedByAProgramme($type);

        $this->actingAs($this->coordinator)
            ->get(route('coordinator.requirement-types.edit', $type))
            ->assertSee('sudah dipakai, sehingga cara mengisinya tidak dapat diubah');
    }

    // ---- deleting ----

    public function test_an_unused_requirement_can_be_deleted(): void
    {
        $type = $this->type();

        $this->actingAs($this->coordinator)
            ->delete(route('coordinator.requirement-types.destroy', $type))
            ->assertRedirect(route('coordinator.requirement-types.index'));

        $this->assertModelMissing($type);
    }

    public function test_a_requirement_a_scholarship_asks_for_cannot_be_deleted(): void
    {
        $type = $this->type();
        $program = $this->usedByAProgramme($type);

        $this->actingAs($this->coordinator)
            ->delete(route('coordinator.requirement-types.destroy', $type))
            ->assertSessionHasErrors('requirement_type');

        // Deleting would have cascaded into the scholarship's own checklist.
        $this->assertModelExists($type);
        $this->assertSame(1, $program->requirements()->count());
    }

    public function test_a_requirement_applicants_have_answered_cannot_be_deleted(): void
    {
        $type = $this->type();
        $application = Application::factory()->create();
        ApplicationDocument::create([
            'application_id' => $application->id,
            'requirement_type_id' => $type->id,
            'file_path' => 'applications/1/proposal.pdf',
            'original_name' => 'proposal.pdf',
        ]);

        $this->actingAs($this->coordinator)
            ->delete(route('coordinator.requirement-types.destroy', $type))
            ->assertSessionHasErrors('requirement_type');

        $this->assertModelExists($type);
        $this->assertSame(1, ApplicationDocument::count());
    }

    public function test_the_list_offers_delete_only_for_unused_requirements(): void
    {
        $used = $this->type();
        $this->usedByAProgramme($used);
        $free = $this->type(['name' => 'Surat pernyataan', 'slug' => 'surat-pernyataan']);

        $html = $this->actingAs($this->coordinator)
            ->get(route('coordinator.requirement-types.index'))
            ->getContent();

        // The form's action, not just the URL: the edit link starts with the same address.
        $this->assertStringContainsString('action="'.route('coordinator.requirement-types.destroy', $free).'"', $html);
        $this->assertStringNotContainsString('action="'.route('coordinator.requirement-types.destroy', $used).'"', $html);
        $this->assertStringContainsString('data-confirm-title="Hapus persyaratan ini?"', $html);
    }

    // ---- what it is for ----

    public function test_a_new_requirement_shows_up_when_building_a_scholarship(): void
    {
        $this->type(['name' => 'Proposal penelitian']);

        $this->actingAs($this->coordinator)
            ->get(route('coordinator.programs.create'))
            ->assertOk()
            ->assertSee('Proposal penelitian')
            ->assertSee('Kelola jenis persyaratan');
    }

    public function test_a_scholarship_can_ask_for_the_new_requirement_and_students_can_answer_it(): void
    {
        Storage::fake('local');

        $type = $this->type();
        $program = Program::factory()->create([
            'coordinator_id' => $this->coordinator->id,
            'min_gpa' => null,
            'max_family_income' => null,
            'application_deadline' => now()->addMonth(),
        ]);
        $program->requirements()->create(['requirement_type_id' => $type->id, 'is_required' => true]);

        $student = $this->user('student');
        $student->studentProfile()->create(['gpa' => 3.5, 'year_enrolled' => 2022, 'family_income' => 3000000]);

        $this->actingAs($student)->post('/student/applications', [
            'program_id' => $program->id,
            'semester' => '2026-1',
            'answers' => [$type->id => UploadedFile::fake()->create('proposal.pdf', 200, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $document = ApplicationDocument::where('requirement_type_id', $type->id)->firstOrFail();
        $this->assertSame('proposal.pdf', $document->original_name);
        Storage::disk('local')->assertExists($document->file_path);
    }
}
