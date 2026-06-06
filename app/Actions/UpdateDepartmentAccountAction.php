<?php

namespace App\Actions;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

/**
 * Update a department account's name/code and its login email.
 */
class UpdateDepartmentAccountAction
{
    /**
     * @param  array<string, mixed>  $data  name, code, email
     */
    public function execute(Department $account, array $data): Department
    {
        return DB::transaction(function () use ($account, $data): Department {
            $account->update([
                'name' => $data['name'],
                'code' => $data['code'],
            ]);

            $account->users()->first()?->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            return $account;
        });
    }
}
