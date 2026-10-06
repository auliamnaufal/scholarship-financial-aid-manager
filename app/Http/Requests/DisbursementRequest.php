<?php

namespace App\Http\Requests;

use App\Models\Application;
use App\Models\Disbursement;
use App\Support\Money;
use App\Support\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Recording a payment and correcting one share these rules. When a payment is
 * being corrected, its own amount and sequence number do not count against it.
 */
class DisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $disbursement = $this->route('disbursement');

        return $disbursement
            ? $this->user()->can('update', $disbursement)
            : $this->user()->can('create', [Disbursement::class, $this->route('application')]);
    }

    public function rules(): array
    {
        return [
            'seq_no' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'disbursement_date' => ['required', 'date'],
            'semester' => Semester::rules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Application $application */
            $application = $this->route('application');
            /** @var Disbursement|null $editing */
            $editing = $this->route('disbursement');
            $seqNo = $this->input('seq_no');

            if (! $seqNo) {
                return;
            }

            $duplicate = $application->disbursements()
                ->where('seq_no', $seqNo)
                ->when($editing, fn ($query) => $query->whereKeyNot($editing->getKey()))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('seq_no', 'A disbursement with this sequence number already exists for this application.');
            }

            $this->checkFundsAvailable($validator, $application, (float) ($editing?->amount ?? 0));
        });
    }

    /**
     * A payment may not take the student past what they were awarded, nor the
     * scholarship past its budget. Both figures are derived from the
     * disbursement rows, so this is the only place they can be overdrawn.
     *
     * `$replacing` is the amount of the payment being corrected: it is about to
     * be replaced, so it is given back before the new amount is measured.
     */
    private function checkFundsAvailable(Validator $validator, Application $application, float $replacing): void
    {
        $amount = (float) $this->input('amount');

        if ($amount <= 0) {
            return;
        }

        $owedToStudent = $application->remainingAward();

        if ($owedToStudent !== null && $amount > $owedToStudent + $replacing) {
            $validator->errors()->add('amount', sprintf(
                __('Only %s is still owed on this application.'),
                Money::rupiah($owedToStudent + $replacing),
            ));
        }

        $leftInBudget = $application->program->remainingBudget() + $replacing;

        if ($amount > $leftInBudget) {
            $validator->errors()->add('amount', sprintf(
                __('The program has only %s left in its budget.'),
                Money::rupiah($leftInBudget),
            ));
        }
    }
}
