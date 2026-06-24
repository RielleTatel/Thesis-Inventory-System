<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with foundation data:
     * roles, one administrator account, and the single owning department.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Administrator account (no owning department).
        User::factory()->create([
            'name' => 'System Administrator',
            'email' => 'admin@adzu.edu.ph',
            'password' => Hash::make('password'),
        ])->assignRole('administrator');

        // The single department that owns the seeded thesis catalog, with its
        // department-role login.
        $department = Department::create([
            'name' => 'Science Information Technology Engineering Academic Organization',
            'code' => 'SITEAO',
        ]);

        User::factory()->create([
            'name' => 'SITEAO Department Account',
            'email' => 'siteao@adzu.edu.ph',
            'password' => Hash::make('password'),
            'department_id' => $department->id,
        ])->assignRole('department');

        // Thesis catalog (depends on the department created above).
        $this->call(ThesisSeeder::class);
    }
}
