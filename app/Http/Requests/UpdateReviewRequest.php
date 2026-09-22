<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Review $review */
        $review = $this->route('review');

        return $this->user()->can('update', $review);
    }

    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
