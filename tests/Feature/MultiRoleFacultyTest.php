<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\FacultyPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiRoleFacultyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $adminRole;
    protected Role $facultyRole;
    protected Role $higherFacultyRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->adminRole = Role::where('name', 'admin')->first();
        $this->facultyRole = Role::where('name', 'faculty')->first();
        $this->higherFacultyRole = Role::where('name', 'higher_faculty')->first();

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->adminRole->id,
            'phone' => '1234567890',
        ]);
    }

    public function test_multi_role_faculty_redirects_to_select_dashboard(): void
    {
        $faculty = User::create([
            'name' => 'Multi Role Faculty',
            'email' => 'multi@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1111111111',
        ]);

        $faculty->assignPermission('guide');
        $faculty->assignPermission('approval_faculty');

        $response = $this->actingAs($faculty)->get(route('dashboard'));

        $response->assertRedirect(route('select-dashboard'));
    }

    public function test_single_role_guide_redirects_to_guide_dashboard(): void
    {
        $faculty = User::create([
            'name' => 'Guide Faculty',
            'email' => 'guide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '2222222222',
        ]);

        $faculty->assignPermission('guide');

        $response = $this->actingAs($faculty)->get(route('dashboard'));

        $response->assertRedirect(route('faculty.guide-dashboard'));
    }

    public function test_single_role_approval_redirects_to_approval_dashboard(): void
    {
        $faculty = User::create([
            'name' => 'Approval Faculty',
            'email' => 'approval@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '3333333333',
        ]);

        $faculty->assignPermission('approval_faculty');

        $response = $this->actingAs($faculty)->get(route('dashboard'));

        $response->assertRedirect(route('faculty.approval-dashboard'));
    }

    public function test_single_role_noc_redirects_to_noc_dashboard(): void
    {
        $faculty = User::create([
            'name' => 'Noc Faculty',
            'email' => 'noc@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->higherFacultyRole->id,
            'phone' => '4444444444',
        ]);

        $faculty->assignPermission('noc_authority');

        $response = $this->actingAs($faculty)->get(route('dashboard'));

        $response->assertRedirect(route('higher-faculty.noc-dashboard'));
    }

    public function test_multi_role_faculty_dashboard_switching(): void
    {
        $faculty = User::create([
            'name' => 'Multi Role Faculty',
            'email' => 'multi@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1111111111',
        ]);

        $faculty->assignPermission('guide');
        $faculty->assignPermission('approval_faculty');

        // Initially goes to select-dashboard
        $response = $this->actingAs($faculty)->get(route('dashboard'));
        $response->assertRedirect(route('select-dashboard'));

        // Switch to Guide
        $response = $this->actingAs($faculty)->get(route('switch-dashboard', ['dashboard' => 'guide']));
        $response->assertRedirect(route('faculty.guide-dashboard'));
        $this->assertEquals('guide', session('selected_dashboard'));

        // Switch to Approval
        $response = $this->actingAs($faculty)->get(route('switch-dashboard', ['dashboard' => 'approval_faculty']));
        $response->assertRedirect(route('faculty.approval-dashboard'));
        $this->assertEquals('approval_faculty', session('selected_dashboard'));

        // Hitting dashboard again should auto redirect to active session dashboard (Approval)
        $response = $this->actingAs($faculty)->get(route('dashboard'));
        $response->assertRedirect(route('faculty.approval-dashboard'));
    }

    public function test_faculty_cannot_switch_to_unpermitted_dashboard(): void
    {
        $faculty = User::create([
            'name' => 'Guide Faculty',
            'email' => 'guide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '2222222222',
        ]);

        $faculty->assignPermission('guide');

        // Trying to switch to NOC (which is not assigned) should redirect to route('dashboard')
        $response = $this->actingAs($faculty)->get(route('switch-dashboard', ['dashboard' => 'noc_authority']));
        $response->assertRedirect(route('dashboard'));
        $this->assertNull(session('selected_dashboard'));
    }

    public function test_unauthorized_dashboard_access_is_blocked(): void
    {
        $faculty = User::create([
            'name' => 'Guide Faculty',
            'email' => 'guide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '2222222222',
        ]);

        $faculty->assignPermission('guide');

        // Attempting to access approval-dashboard directly should redirect to guide-dashboard (from FacultyApprovalMiddleware)
        $response = $this->actingAs($faculty)->get(route('faculty.approval-dashboard'));
        $response->assertRedirect(route('faculty.guide-dashboard'));

        // Attempting to access noc-dashboard directly should give 403 (from HigherFacultyMiddleware)
        $response = $this->actingAs($faculty)->get(route('higher-faculty.noc-dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_update_faculty_permissions(): void
    {
        $faculty = User::create([
            'name' => 'Test Faculty',
            'email' => 'test@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '5555555555',
        ]);

        $faculty->assignPermission('guide');

        $response = $this->actingAs($this->admin)->put(route('admin.faculty.update-authority', $faculty), [
            'permissions' => ['guide', 'approval_faculty', 'noc_authority']
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));

        $faculty->refresh();
        $this->assertTrue($faculty->hasPermission('guide'));
        $this->assertTrue($faculty->hasPermission('approval_faculty'));
        $this->assertTrue($faculty->hasPermission('noc_authority'));

        // Role should also become higher_faculty since noc_authority is present
        $this->assertEquals($this->higherFacultyRole->id, $faculty->role_id);
    }

    public function test_multi_role_faculty_dashboard_switcher_rendered(): void
    {
        $faculty = User::create([
            'name' => 'Multi Role Faculty',
            'email' => 'multi@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1111111111',
        ]);

        $faculty->assignPermission('guide');
        $faculty->assignPermission('approval_faculty');

        // Set the active dashboard in session
        $this->actingAs($faculty)->get(route('switch-dashboard', ['dashboard' => 'guide']));

        // Get the dashboard view
        $response = $this->actingAs($faculty)->get(route('faculty.guide-dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Switch Dashboard');
        $response->assertSee('Guide Dashboard');
        $response->assertSee('Approval Dashboard');
        $response->assertSee('✓');
    }

    public function test_guide_dashboard_filters_by_batch(): void
    {
        $faculty = User::create([
            'name' => 'Guide Faculty',
            'email' => 'guide_test@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '8888888889',
        ]);
        $faculty->assignPermission('guide');

        // Create two batches
        $batch1 = \App\Models\Batch::create(['name' => 'Batch 1']);
        $batch2 = \App\Models\Batch::create(['name' => 'Batch 2']);

        $studentRole = Role::where('name', 'student')->first();

        // Create student in batch 1 assigned to this guide
        $student1 = User::create([
            'name' => 'Student One',
            'email' => 'std1@example.ac.in',
            'enrollment_number' => '23IT001',
            'password' => bcrypt('password'),
            'role_id' => $studentRole->id,
            'batch_id' => $batch1->id,
            'guide_id' => $faculty->id,
            'phone' => '1234567801',
        ]);

        // Create student in batch 2 assigned to this guide
        $student2 = User::create([
            'name' => 'Student Two',
            'email' => 'std2@example.ac.in',
            'enrollment_number' => '23IT002',
            'password' => bcrypt('password'),
            'role_id' => $studentRole->id,
            'batch_id' => $batch2->id,
            'guide_id' => $faculty->id,
            'phone' => '1234567802',
        ]);

        // Access dashboard without specifying batch_id (should default to first batch, Batch 1)
        $response = $this->actingAs($faculty)->get(route('faculty.guide-dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Batch 1');
        $response->assertSee('Batch 2');
        $response->assertSee('Student One');
        $response->assertDontSee('Student Two');

        // Access dashboard specifying batch_id for Batch 2
        $response = $this->actingAs($faculty)->get(route('faculty.guide-dashboard', ['batch_id' => $batch2->id]));
        $response->assertStatus(200);
        $response->assertSee('Student Two');
        $response->assertDontSee('Student One');
    }
}
