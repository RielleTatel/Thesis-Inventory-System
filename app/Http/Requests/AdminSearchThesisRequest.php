<?php

namespace App\Http\Requests;

/**
 * Extends the shared search request with admin-only filter fields:
 * department_id (to scope to one department) and status (to see drafts vs published).
 *
 * The base SearchThesisRequest handles q, year_from, year_to, program, keyword.
 */
class AdminSearchThesisRequest extends SearchThesisRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['nullable', 'in:draft,published'],
        ]);
    }
}
