<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Creating or editing a reviewer/moderator account. */
class StoreStaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('coordinator');
    }

    public function rules(): array
    {
        $staff = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                // Archived accounts keep their address, so it stays reserved.
                Rule::unique('users', 'email')->ignore($staff?->id),
            ],
            // Optional on edit: left blank, the current password stands.
            'password' => [
                $staff ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:reviewer,coordinator'],
        ];
    }
}
