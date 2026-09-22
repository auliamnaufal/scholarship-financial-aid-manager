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

            // The checklist an applicant will have to satisfy. Absent means
            // "no requirements", which is a legitimate scholarship.
            'requirements' => ['nullable', 'array'],
            'requirements.*.enabled' => ['nullable', 'boolean'],
            'requirements.*.is_required' => ['nullable', 'boolean'],
            'requirements.*.instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * The requirement rows the coordinator ticked, keyed by requirement type
     * id and ready for `Program::requirements()->sync()`-style replacement.
     *
     * @return array<int, array{is_required: bool, instructions: ?string}>
     */
    public function requirements(): array
    {
        $enabled = collect($this->input('requirements', []))
            ->filter(fn ($row) => filter_var($row['enabled'] ?? false, FILTER_VALIDATE_BOOL));

        return $enabled
            ->map(fn ($row) => [
                'is_required' => filter_var($row['is_required'] ?? false, FILTER_VALIDATE_BOOL),
                'instructions' => filled($row['instructions'] ?? null) ? trim($row['instructions']) : null,
            ])
            ->all();
    }

    /**
     * The programme's own columns, without the requirement checklist that
     * lives in its own table.
     */
    public function programAttributes(): array
    {
        return collect($this->validated())->except('requirements')->all();
    }
}
