<?php

namespace App\Actions;

use App\Models\Department;

/**
 * Flip a department login between active and inactive (FR — admin can disable
 * a login without deleting it). Returns the new active state.
 */
class ToggleDepartmentAccountStatusAction
{
    public function execute(Department $account): bool
    {
        $login = $account->users()->first();

        if ($login === null) {
            return false;
        }

        $login->update(['is_active' => ! $login->is_active]);

        return $login->is_active;
    }
}
