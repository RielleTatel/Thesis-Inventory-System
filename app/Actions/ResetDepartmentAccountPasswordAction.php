<?php

namespace App\Actions;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin-mediated password reset for a department login (no email infra — the
 * admin relays the new password out-of-band). Touches only the password; roles
 * and is_active are deliberately left untouched. Logs the reset WITHOUT ever
 * recording the password value.
 */
class ResetDepartmentAccountPasswordAction
{
    /**
     * @return User the department login whose password was reset
     */
    public function execute(Department $account, string $password): User
    {
        return DB::transaction(function () use ($account, $password): User {
            $login = $account->users()->firstOrFail();

            // The 'password' cast hashes the plain value on assignment.
            $login->update(['password' => $password]);

            // FR-7.1: record the reset (causer auto-resolves to the admin). Only
            // the department is referenced — the password value is never logged.
            activity('account')
                ->performedOn($account)
                ->withProperties(['department' => $account->name])
                ->event('password reset')
                ->log('password reset');

            return $login;
        });
    }
}
