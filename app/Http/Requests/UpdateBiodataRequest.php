<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A student editing their own biodata. The same fields the coordinator can
 * edit in StudentRequest, minus the account credentials.
 */
class UpdateBiodataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('student');
    }

    public function rules(): array
    {
        $profileId = $this->user()->studentProfile?->id;

        return [
            'nim' => ['nullable', 'string', 'max:32', Rule::unique('student_profiles', 'nim')->ignore($profileId)],
            'faculty' => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
            'gpa' => ['required', 'numeric', 'min:0', 'max:4'],
            'year_enrolled' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:1000'],
            'family_income' => ['nullable', 'numeric', 'min:0'],
            'parent_occupation' => ['nullable', 'string', 'max:255'],
            'dependents_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:64'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
        ];
    }
}
