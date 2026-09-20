<?php

namespace App\Http\Requests;

use App\Models\Application;
use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Application::class);
    }

    public function rules(): array
    {
        return [
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'semester' => ['required', 'string', 'max:20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $programId = $this->input('program_id');
            $semester = $this->input('semester');

            if (! $programId || ! $semester) {
                return;
            }

            $program = Program::find($programId);

            if ($program && ! $program->isOpen()) {
                $validator->errors()->add('program_id', 'The application deadline for this program has already passed.');
            }

            $duplicate = Application::query()
                ->where('student_id', $this->user()->id)
                ->where('program_id', $programId)
                ->where('semester', $semester)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('semester', 'You have already applied to this program for this semester.');
            }
        });
    }
}
