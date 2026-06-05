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
     * roles, one administrator account, and two sample departments.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Administrator account (no owning department).
        User::factory()->create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ])->assignRole('administrator');

        // Two sample departments, each with a department-role login.
        $departments = [
            ['name' => 'College of Computer Studies', 'code' => 'CCS'],
            ['name' => 'College of Engineering', 'code' => 'COE'],
        ];

        foreach ($departments as $data) {
            $department = Department::create($data);

            User::factory()->create([
                'name' => $data['name'].' Account',
                'email' => strtolower($data['code']).'@example.com',
                'password' => Hash::make('password'),
                'department_id' => $department->id,
            ])->assignRole('department');
        }

        // Sample theses (depends on the departments created above).
        $this->call(ThesisSeeder::class);
    }
}
