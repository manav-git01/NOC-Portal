<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $studentRole = Role::where('name', 'student')->first();

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'phone' => '1234567890',
        ]);

        $this->student = User::create([
            'name' => 'John Student',
            'email' => 'student@example.edu.in',
            'password' => bcrypt('password'),
            'role_id' => $studentRole->id,
            'phone' => '9876543210',
            'enrollment_number' => '2024001',
            'department' => 'Computer Science',
            'semester' => 6,
        ]);
    }

    /**
     * Test guests are redirected to login.
     */
    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test non-admin is forbidden.
     */
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->student)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test admin can access admin dashboard.
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test admin is redirected on landing on general /dashboard.
     */
    public function test_admin_is_redirected_to_admin_dashboard_from_general_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }
}
