<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Creating or editing a student account and its biodata, from the
 * coordinator's side. The student edits the same profile fields themselves in
 * Student\ProfileController; the account fields are coordinator-only.
 */
class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route sits behind role:coordinator.
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $userId = $student?->id;
        $profileId = $student?->studentProfile?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                // Soft-deleted rows still hold their address, so an archived
                // account's email stays reserved.
                Rule::unique('users', 'email')->ignore($userId),
            ],
            // Optional on edit: left blank, the current password stands.
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'confirmed',
                Password::defaults(),
            ],

            ...$this->profileRules($profileId),
        ];
    }

    /** @return array<string, array<int, mixed>> */
    private function profileRules(?int $profileId): array
    {
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

    /** Just the biodata, ready for the student_profiles row. */
    public function profileAttributes(): array
    {
        return collect($this->validated())
            ->except(['name', 'email', 'password', 'password_confirmation'])
            ->all();
    }
}
