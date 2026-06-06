<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for editing a department account (name, code, login email).
 * Uniqueness ignores the account's own department/login rows.
 */
class UpdateDepartmentAccountRequest extends FormRequest
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
        $account = $this->route('account');
        $departmentId = $account instanceof Department ? $account->getKey() : null;
        $loginUserId = $account instanceof Department ? $account->users()->value('id') : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($departmentId)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($loginUserId)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'department name',
            'code' => 'department code',
        ];
    }
}
