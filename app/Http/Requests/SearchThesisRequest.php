<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the public browse/search inputs (coding standard #3).
 *
 * Public endpoint — anyone may search, so authorization is open; the rules
 * exist to keep the query inputs sane (bounded free-text, integer years).
 */
class SearchThesisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'year_from' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'year_to' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'program' => ['nullable', 'string', 'max:255'],
            'keyword' => ['nullable', 'array'],
            'keyword.*' => ['string', 'max:255'],
        ];
    }

    /**
     * The validated filter set passed to the repository.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->validated();
    }
}
