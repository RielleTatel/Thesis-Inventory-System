<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validation for an admin-mediated department password reset. No email infra —
 * the admin sets the new password here and relays it out-of-band (coding
 * standard #3). Authorization is the role:administrator route middleware.
 */
class ResetDepartmentPasswordRequest extends FormRequest
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
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
