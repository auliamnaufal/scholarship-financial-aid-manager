<?php

namespace App\Http\Requests;

use App\Models\Application;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Application $application */
        $application = $this->route('application');

        return $this->user()->can('create', [Review::class, $application]);
    }

    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
