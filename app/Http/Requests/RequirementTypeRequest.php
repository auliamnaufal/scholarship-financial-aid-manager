<?php

namespace App\Http\Requests;

use App\Enums\RequirementKind;
use App\Models\RequirementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class RequirementTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('coordinator');
    }

    public function rules(): array
    {
        /** @var RequirementType|null $type */
        $type = $this->route('requirement_type');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('requirement_types', 'name')->ignore($type?->id)],
            'kind' => ['required', new Enum(RequirementKind::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var RequirementType|null $type */
            $type = $this->route('requirement_type');

            // Changing how something is answered would strand the answers already
            // given: an uploaded file cannot become a typed reply, or the reverse.
            if ($type && $type->isInUse() && $this->input('kind') !== $type->kind->value) {
                $validator->errors()->add('kind', __('This requirement is already in use, so how it is answered cannot change.'));
            }
        });
    }
}
