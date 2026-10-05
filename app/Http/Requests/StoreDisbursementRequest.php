<?php

namespace App\Http\Requests;

use App\Models\Application;
use App\Models\Disbursement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Application $application */
        $application = $this->route('application');

        return $this->user()->can('create', [Disbursement::class, $application]);
    }

    public function rules(): array
    {
        return [
            'seq_no' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'disbursement_date' => ['required', 'date'],
            'semester' => ['required', 'string', 'max:20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Application $application */
            $application = $this->route('application');
            $seqNo = $this->input('seq_no');

            if (! $seqNo) {
                return;
            }

            $duplicate = $application->disbursements()->where('seq_no', $seqNo)->exists();

            if ($duplicate) {
                $validator->errors()->add('seq_no', 'A disbursement with this sequence number already exists for this application.');
            }

            $this->checkFundsAvailable($validator, $application);
        });
    }

    /**
     * A payment may not take the student past what they were awarded, nor the
     * scholarship past its budget. Both figures are derived from the
     * disbursement rows, so this is the only place they can be overdrawn.
     */
    private function checkFundsAvailable(Validator $validator, Application $application): void
    {
        $amount = (float) $this->input('amount');

        if ($amount <= 0) {
            return;
        }

        $owedToStudent = $application->remainingAward();

        if ($owedToStudent !== null && $amount > $owedToStudent) {
            $validator->errors()->add('amount', sprintf(
                __('Only %s is still owed on this application.'),
                \App\Support\Money::rupiah($owedToStudent),
            ));
        }

        $leftInBudget = $application->program->remainingBudget();

        if ($amount > $leftInBudget) {
            $validator->errors()->add('amount', sprintf(
                __('The program has only %s left in its budget.'),
                \App\Support\Money::rupiah($leftInBudget),
            ));
        }
    }
}
