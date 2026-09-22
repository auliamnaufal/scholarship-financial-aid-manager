<?php

namespace App\Http\Requests;

use App\Enums\RequirementKind;
use App\Models\Application;
use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    /** Uploads: PDF/DOC/DOCX, 5 MB. */
    public const FILE_RULES = ['file', 'mimes:pdf,doc,docx', 'max:5120'];

    private ?Program $program = null;

    public function authorize(): bool
    {
        return $this->user()->can('create', Application::class);
    }

    public function rules(): array
    {
        $rules = [
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'semester' => ['required', 'string', 'max:20'],
        ];

        // One rule per requirement this scholarship actually asks for, so an
        // optional recommendation letter is validated only if one is sent.
        foreach ($this->program()?->requirements ?? [] as $requirement) {
            $key = $requirement->requirement_type_id;
            $compulsory = $requirement->is_required ? 'required' : 'nullable';

            $rules["answers.{$key}"] = $requirement->requirementType->kind === RequirementKind::File
                ? [$compulsory, ...self::FILE_RULES]
                : [$compulsory, 'string', 'min:20', 'max:5000'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $names = [];

        foreach ($this->program()?->requirements ?? [] as $requirement) {
            $names["answers.{$requirement->requirement_type_id}"] = strtolower($requirement->requirementType->name);
        }

        return $names;
    }

    /** The programme being applied to, loaded once with its checklist. */
    public function program(): ?Program
    {
        return $this->program ??= Program::with('requirements.requirementType')
            ->find($this->input('program_id'));
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $program = $this->program();
            $semester = $this->input('semester');

            if (! $program || ! $semester) {
                return;
            }

            if (! $program->isOpen()) {
                $validator->errors()->add('program_id', 'The application deadline for this program has already passed.');
            }

            $duplicate = Application::query()
                ->where('student_id', $this->user()->id)
                ->where('program_id', $program->id)
                ->where('semester', $semester)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('semester', 'You have already applied to this program for this semester.');
            }

            $this->checkEligibility($validator, $program);
        });
    }

    /**
     * The programme's own thresholds. `max_family_income` could not be checked
     * at all until students had a family income of their own to compare.
     */
    private function checkEligibility(Validator $validator, Program $program): void
    {
        $profile = $this->user()->studentProfile;

        if (! $profile) {
            $validator->errors()->add('program_id', 'Fill in your biodata before applying.');

            return;
        }

        if ($program->min_gpa !== null && (float) $profile->gpa < (float) $program->min_gpa) {
            $validator->errors()->add('program_id', sprintf(
                'This scholarship needs a GPA of at least %s; yours is %s.',
                number_format((float) $program->min_gpa, 2),
                number_format((float) $profile->gpa, 2),
            ));
        }

        if ($program->max_family_income !== null) {
            if ($profile->family_income === null) {
                $validator->errors()->add('program_id', 'This scholarship is means-tested. Record your family income in your biodata first.');
            } elseif ((float) $profile->family_income > (float) $program->max_family_income) {
                $validator->errors()->add('program_id', sprintf(
                    'This scholarship is for family incomes up to %s; yours is recorded as %s.',
                    number_format((float) $program->max_family_income),
                    number_format((float) $profile->family_income),
                ));
            }
        }
    }
}
