<?php

namespace App\Http\Requests;

use App\Enums\ProgramType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        $program = $this->route('program');

        return $program
            ? $this->user()->can('update', $program)
            : $this->user()->can('create', \App\Models\Program::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(ProgramType::class)],
            'funding_source' => ['required', 'string', 'max:255'],
            'budget' => ['required', 'numeric', 'min:0'],
            'application_deadline' => ['required', 'date'],
            'max_family_income' => [
                Rule::requiredIf($this->input('type') === ProgramType::NeedBased->value),
                'nullable', 'numeric', 'min:0',
            ],
            'min_gpa' => [
                Rule::requiredIf($this->input('type') === ProgramType::MeritBased->value),
                'nullable', 'numeric', 'min:0', 'max:4',
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
