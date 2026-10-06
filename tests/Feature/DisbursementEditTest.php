<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Disbursement;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DisbursementEditTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private Application $application;

    private Disbursement $first;

    private Disbursement $second;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = $this->user('coordinator');

        $program = Program::factory()->create([
            'coordinator_id' => $this->coordinator->id,
            'budget' => '100000000',
        ]);

        // Awarded 6.000.000, of which 3.000.000 is paid in two instalments.
        $this->application = Application::factory()->create([
            'student_id' => $this->user('student')->id,
            'program_id' => $program->id,
            'status' => ApplicationStatus::Approved,
            'awarded_amount' => '6000000',
        ]);

        $this->first = $this->disbursement(1, '2000000');
        $this->second = $this->disbursement(2, '1000000');
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    private function disbursement(int $seq, string $amount): Disbursement
    {
        return $this->application->disbursements()->create([
            'seq_no' => $seq,
            'amount' => $amount,
            'disbursement_date' => '2026-09-01',
            'semester' => '2026-1',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'seq_no' => 2,
            'amount' => '1000000',
            'disbursement_date' => '2026-09-15',
            'semester' => '2026-1',
        ], $overrides);
    }

    private function update(Disbursement $disbursement, array $overrides = [])
    {
        return $this->actingAs($this->coordinator)->put(
            route('coordinator.disbursements.update', [$this->application, $disbursement]),
            $this->payload($overrides),
        );
    }

    public function test_the_edit_page_shows_the_current_values(): void
    {
        $this->actingAs($this->coordinator)
            ->get(route('coordinator.disbursements.edit', [$this->application, $this->second]))
            ->assertOk()
            ->assertSee('value="1000000.00"', false)
            ->assertSee('value="2026-09-01"', false);
    }

    public function test_a_coordinator_can_correct_a_payment(): void
    {
        $this->update($this->second, ['amount' => '1500000', 'disbursement_date' => '2026-09-20'])
            ->assertRedirect(route('coordinator.applications.show', $this->application))
            ->assertSessionHas('status', 'Pencairan diperbarui.');

        $this->second->refresh();
        $this->assertSame('1500000.00', $this->second->amount);
        $this->assertSame('2026-09-20', $this->second->disbursement_date->format('Y-m-d'));
    }

    public function test_the_corrected_payment_may_grow_up_to_what_is_still_owed_including_itself(): void
    {
        // 3.000.000 is still owed and this payment's own 1.000.000 is given back: 4.000.000.
        $this->update($this->second, ['amount' => '4000000'])->assertSessionHasNoErrors();
        $this->assertSame('4000000.00', $this->second->refresh()->amount);
    }

    public function test_the_corrected_payment_cannot_go_past_the_award(): void
    {
        $this->update($this->second, ['amount' => '4000001'])->assertSessionHasErrors('amount');
        $this->assertSame('1000000.00', $this->second->refresh()->amount);
    }

    public function test_the_programme_budget_still_caps_a_correction(): void
    {
        // Leave the programme 500.000 above what it has already paid out.
        $this->application->program->update(['budget' => '3500000']);

        $this->update($this->second, ['amount' => '1500000'])->assertSessionHasNoErrors();
        $this->update($this->second, ['amount' => '1500001'])->assertSessionHasErrors('amount');
    }

    public function test_a_payment_keeps_its_own_sequence_number_but_cannot_take_another(): void
    {
        $this->update($this->second, ['seq_no' => 2])->assertSessionHasNoErrors();
        $this->update($this->second, ['seq_no' => 1])->assertSessionHasErrors('seq_no');

        $this->assertSame(2, $this->second->refresh()->seq_no);
    }

    public function test_a_payment_can_be_renumbered_to_a_free_number(): void
    {
        $this->update($this->second, ['seq_no' => 5])->assertSessionHasNoErrors();

        $this->assertSame(5, $this->second->refresh()->seq_no);
    }

    public function test_a_coordinator_can_delete_a_payment_and_the_amount_is_owed_again(): void
    {
        $this->assertEquals(3000000, $this->application->remainingAward());

        $this->actingAs($this->coordinator)
            ->delete(route('coordinator.disbursements.destroy', [$this->application, $this->second]))
            ->assertRedirect(route('coordinator.applications.show', $this->application))
            ->assertSessionHas('status', 'Pencairan dihapus.');

        $this->assertModelMissing($this->second);
        $this->assertEquals(4000000, $this->application->refresh()->remainingAward());
    }

    public function test_another_coordinator_cannot_touch_the_payment(): void
    {
        $other = $this->user('coordinator');

        $this->actingAs($other)
            ->get(route('coordinator.disbursements.edit', [$this->application, $this->second]))
            ->assertForbidden();

        $this->actingAs($other)
            ->put(route('coordinator.disbursements.update', [$this->application, $this->second]), $this->payload(['amount' => '1']))
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('coordinator.disbursements.destroy', [$this->application, $this->second]))
            ->assertForbidden();

        $this->assertSame('1000000.00', $this->second->refresh()->amount);
    }

    public function test_students_and_reviewers_cannot_touch_payments(): void
    {
        foreach (['student', 'reviewer'] as $role) {
            $this->actingAs($this->user($role))
                ->delete(route('coordinator.disbursements.destroy', [$this->application, $this->second]))
                ->assertForbidden();
        }

        $this->assertModelExists($this->second);
    }

    public function test_a_payment_of_another_application_is_not_found_under_this_one(): void
    {
        $elsewhere = Application::factory()->create([
            'program_id' => $this->application->program_id,
            'status' => ApplicationStatus::Approved,
            'awarded_amount' => '5000000',
        ]);
        $stray = $elsewhere->disbursements()->create([
            'seq_no' => 1, 'amount' => '1000000', 'disbursement_date' => '2026-09-01', 'semester' => '2026-1',
        ]);

        $this->actingAs($this->coordinator)
            ->delete(route('coordinator.disbursements.destroy', [$this->application, $stray]))
            ->assertNotFound();

        $this->assertModelExists($stray);
    }

    public function test_the_application_page_offers_edit_and_delete_to_its_coordinator(): void
    {
        $this->actingAs($this->coordinator)
            ->get(route('coordinator.applications.show', $this->application))
            ->assertOk()
            ->assertSee(route('coordinator.disbursements.edit', [$this->application, $this->second]))
            ->assertSee('data-confirm-title="Hapus pencairan ini?"', false)
            ->assertSee('Pencairan ke-2 akan dihapus');
    }

    public function test_another_coordinator_sees_the_payments_without_the_buttons(): void
    {
        $this->actingAs($this->user('coordinator'))
            ->get(route('coordinator.applications.show', $this->application))
            ->assertOk()
            ->assertDontSee('data-confirm-title="Hapus pencairan ini?"', false);
    }

    public function test_the_record_form_is_not_prefilled_with_the_last_payment(): void
    {
        $html = $this->actingAs($this->coordinator)
            ->get(route('coordinator.applications.show', $this->application))
            ->getContent();

        preg_match('/<input id="amount"[^>]*>/', $html, $match);

        $this->assertStringNotContainsString('value="1000000', $match[0] ?? '');
    }

    public function test_recording_a_new_payment_still_works(): void
    {
        $this->actingAs($this->coordinator)
            ->post(route('coordinator.disbursements.store', $this->application), $this->payload(['seq_no' => 3, 'amount' => '3000000']))
            ->assertRedirect(route('coordinator.applications.show', $this->application));

        $this->assertSame(3, $this->application->disbursements()->count());
        $this->assertEquals(0, $this->application->refresh()->remainingAward());
    }
}
