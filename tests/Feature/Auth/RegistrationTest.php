<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $studentRole = \App\Models\Role::where('name', 'student')->first();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.edu.in',
            'phone' => '1234567890',
            'role_id' => $studentRole->id,
            'enrollment_number' => '21IT001',
            'department' => 'IT',
            'semester' => 6,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_can_be_registered(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $adminRole = \App\Models\Role::where('name', 'admin')->first();

        $response = $this->post('/register', [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'role_id' => $adminRole->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        
        $user = \App\Models\User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isAdmin());
    }
}
