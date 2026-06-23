<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'student',
                'display_name' => 'Student',
                'description' => 'Student who can submit internship applications',
            ],
            [
                'name' => 'faculty',
                'display_name' => 'Faculty In-Charge',
                'description' => 'Faculty who can review and approve/reject student applications',
            ],
            [
                'name' => 'higher_faculty',
                'display_name' => 'Higher-Level Faculty',
                'description' => 'Higher-level faculty who can give final approval and generate NOC',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Administrator with access to all data and configurations',
            ],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::create($role);
        }
    }
}
