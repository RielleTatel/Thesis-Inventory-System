<?php

namespace App\Http\Requests;

use App\Actions\DeleteDepartmentAccountAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the FR-2.3 keep-or-delete choice when removing a department account.
 */
class DeleteDepartmentAccountRequest extends FormRequest
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
            'mode' => ['required', Rule::in([
                DeleteDepartmentAccountAction::MODE_KEEP,
                DeleteDepartmentAccountAction::MODE_DELETE,
            ])],
        ];
    }
}
