<?php

namespace App\Actions;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

/**
 * Create a department account: the department unit + its single login user
 * (department role), in one transaction.
 */
class CreateDepartmentAccountAction
{
    /**
     * @param  array<string, mixed>  $data  name, code, email, password
     */
    public function execute(array $data): Department
    {
        return DB::transaction(function () use ($data): Department {
            $department = Department::create([
                'name' => $data['name'],
                'code' => $data['code'],
            ]);

            // The 'password' cast hashes the plain value on assignment.
            $login = $department->users()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $login->assignRole('department');

            // FR-7.1: record the account creation (causer auto-resolves to the admin).
            activity('account')
                ->performedOn($department)
                ->withProperties(['department' => $department->name, 'code' => $department->code])
                ->event('created')
                ->log('created');

            return $department;
        });
    }
}
