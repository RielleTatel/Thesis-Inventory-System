<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating a thesis record (coding standard #3) — the single
 * definition of "what is a valid thesis". UpdateThesisRequest reuses these rules.
 *
 * Authorization is handled in the controller via ThesisPolicy; the route is
 * already gated to the department role.
 */
class StoreThesisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:500'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.(int) date('Y')],
            'program' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'max:20000'],
            'recommendations' => ['nullable', 'string', 'max:20000'],

            // Ordered multi-value fields — blanks are filtered out in the Action.
            'authors' => ['array'],
            'authors.*' => ['nullable', 'string', 'max:255'],
            'advisers' => ['array'],
            'advisers.*' => ['nullable', 'string', 'max:255'],
            'panelists' => ['array'],
            'panelists.*' => ['nullable', 'string', 'max:255'],
            'keywords' => ['array'],
            'keywords.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'authors.*' => 'author',
            'advisers.*' => 'adviser',
            'panelists.*' => 'panelist',
            'keywords.*' => 'keyword',
        ];
    }
}
