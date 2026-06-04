<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the three SRS user classes as spatie roles.
     *
     * `viewer` is the public, unauthenticated class — seeded for completeness
     * so authorization checks have a stable role name to reference.
     */
    public function run(): void
    {
        foreach (['administrator', 'department', 'viewer'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
