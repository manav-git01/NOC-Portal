<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Create demo users for each role
        $studentRole = \App\Models\Role::where('name', 'student')->first();
        $facultyRole = \App\Models\Role::where('name', 'faculty')->first();
        $higherFacultyRole = \App\Models\Role::where('name', 'higher_faculty')->first();

        // Create a student user
        User::create([
            'name' => 'John Doe',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'role_id' => $studentRole->id,
            'phone' => '1234567890',
            'enrollment_number' => 'ENR2024001',
            'department' => 'Computer Science',
            'semester' => '6th',
        ]);

        // Create a faculty user
        User::create([
            'name' => 'Dr. Jane Smith',
            'email' => 'faculty@example.com',
            'password' => bcrypt('password'),
            'role_id' => $facultyRole->id,
            'phone' => '9876543210',
        ]);

        // Create a higher faculty user
        User::create([
            'name' => 'Prof. Robert Johnson',
            'email' => 'higher_faculty@example.com',
            'password' => bcrypt('password'),
            'role_id' => $higherFacultyRole->id,
            'phone' => '5555555555',
        ]);
    }
}
