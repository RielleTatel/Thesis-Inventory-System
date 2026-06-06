<?php

namespace App\Actions;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

/**
 * Delete a department account, handling the SRS keep-or-delete-records choice
 * (FR-2.3) explicitly — never a cascade default. theses.department_id is
 * restrictOnDelete, so each path is ordered to keep the FK valid.
 */
class DeleteDepartmentAccountAction
{
    public const MODE_KEEP = 'keep';

    public const MODE_DELETE = 'delete';

    public function execute(Department $account, string $mode): void
    {
        DB::transaction(function () use ($account, $mode): void {
            // FR-7.1: record the deletion AND whether records were kept or deleted
            // (captured before the rows go away; department name kept in properties).
            activity('account')
                ->performedOn($account)
                ->withProperties(['department' => $account->name, 'records_mode' => $mode])
                ->event('deleted')
                ->log($mode === self::MODE_DELETE ? 'deleted (records deleted)' : 'deleted (records kept)');

            if ($mode === self::MODE_DELETE) {
                // Remove the records too: delete theses first (their authors/
                // advisers/panelists/keywords cascade via FK), which clears the
                // restrictOnDelete guard, then the login(s), then the department.
                $account->theses()->delete();
                $account->users()->delete();
                $account->delete();

                return;
            }

            // KEEP: the department and its theses stay in the public catalog
            // (FK intact, nothing orphaned). Only the login is removed, so the
            // records can no longer be managed by that account.
            $account->users()->delete();
        });
    }
}
