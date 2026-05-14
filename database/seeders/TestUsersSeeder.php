<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Student User
        User::create([
            'name' => 'John Doe',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'phone' => '1234567890',
            'role' => 'student',
            'enrollment_number' => '2024001',
            'department' => 'Computer Science',
            'semester' => 6,
        ]);

        // Create Faculty User
        User::create([
            'name' => 'Dr. Jane Smith',
            'email' => 'faculty@example.com',
            'password' => Hash::make('password'),
            'phone' => '9876543210',
            'role' => 'faculty',
        ]);

        // Create Higher Faculty User
        User::create([
            'name' => 'Prof. Robert Johnson',
            'email' => 'higher.faculty@example.com',
            'password' => Hash::make('password'),
            'phone' => '5555555555',
            'role' => 'higher_faculty',
        ]);

        $this->command->info('Test users created successfully!');
        $this->command->info('Student: student@example.com / password');
        $this->command->info('Faculty: faculty@example.com / password');
        $this->command->info('Higher Faculty: higher.faculty@example.com / password');
    }
}
