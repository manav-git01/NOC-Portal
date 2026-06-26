<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Batch;
use App\Models\GuideAssignment;
use App\Models\GuideHistory;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $adminRole;
    protected Role $facultyRole;
    protected Role $studentRole;
    protected Batch $batchA;
    protected Batch $batchB;
    protected User $faculty1;
    protected User $faculty2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->adminRole = Role::where('name', 'admin')->first();
        $this->facultyRole = Role::where('name', 'faculty')->first();
        $this->studentRole = Role::where('name', 'student')->first();

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->adminRole->id,
            'phone' => '1234567890',
        ]);

        $this->faculty1 = User::create([
            'name' => 'Dr. Bimal Patel',
            'email' => 'bimal@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '9999999999',
        ]);
        $this->faculty1->assignPermission('guide');

        $this->faculty2 = User::create([
            'name' => 'Prof. Rajesh Patel',
            'email' => 'rajesh@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '8888888888',
        ]);
        $this->faculty2->assignPermission('guide');

        $this->batchA = Batch::create([
            'name' => 'IT_2023',
            'guide_id' => $this->faculty1->id,
        ]);

        $this->batchB = Batch::create([
            'name' => 'IT_2024',
            'guide_id' => $this->faculty2->id,
        ]);
    }

    public function test_batch_details_page_loads(): void
    {
        $student = User::create([
            'name' => 'Rahul Patel',
            'email' => 'rahul@charusat.edu.in',
            'enrollment_number' => '23IT001',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'batch_id' => $this->batchA->id,
            'guide_id' => $this->faculty1->id,
            'phone' => '1111111111',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.batches.show', $this->batchA->id));

        $response->assertStatus(200);
        $response->assertSee('IT_2023');
        $response->assertSee('Rahul Patel');
        $response->assertSee('Dr. Bimal Patel');
    }

    public function test_move_student_to_another_batch(): void
    {
        $student = User::create([
            'name' => 'Rahul Patel',
            'email' => 'rahul@charusat.edu.in',
            'enrollment_number' => '23IT001',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'batch_id' => $this->batchA->id,
            'guide_id' => $this->faculty1->id,
            'phone' => '1111111111',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.change-batch', $student->id), [
                'batch_id' => $this->batchB->id,
            ]);

        $response->assertRedirect();
        
        $student->refresh();
        $this->assertEquals($this->batchB->id, $student->batch_id);

        // Since Batch B has a designated guide (Rajesh), the student should be updated to Rajesh
        $this->assertEquals($this->faculty2->id, $student->guide_id);

        // Assert GuideHistory is logged
        $this->assertDatabaseHas('guide_histories', [
            'student_id' => $student->id,
            'old_guide_id' => $this->faculty1->id,
            'new_guide_id' => $this->faculty2->id,
            'changed_by' => $this->admin->id,
        ]);

        // Assert Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'admin_name' => $this->admin->name,
            'action' => 'Changed Student Batch',
        ]);
    }

    public function test_change_student_guide(): void
    {
        $student = User::create([
            'name' => 'Rahul Patel',
            'email' => 'rahul@charusat.edu.in',
            'enrollment_number' => '23IT001',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'batch_id' => $this->batchA->id,
            'guide_id' => $this->faculty1->id,
            'phone' => '1111111111',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.change-guide', $student->id), [
                'guide_id' => $this->faculty2->id,
            ]);

        $response->assertRedirect();

        $student->refresh();
        $this->assertEquals($this->faculty2->id, $student->guide_id);

        // GuideHistory check
        $this->assertDatabaseHas('guide_histories', [
            'student_id' => $student->id,
            'old_guide_id' => $this->faculty1->id,
            'new_guide_id' => $this->faculty2->id,
            'changed_by' => $this->admin->id,
        ]);

        // GuideAssignment check
        $this->assertDatabaseHas('guide_assignments', [
            'student_id' => $student->id,
            'guide_id' => $this->faculty2->id,
            'unassigned_at' => null,
        ]);
    }

    public function test_change_guide_for_entire_batch(): void
    {
        $student1 = User::create([
            'name' => 'Rahul Patel', 'email' => 'rahul@charusat.edu.in', 'enrollment_number' => '23IT001',
            'password' => bcrypt('password'), 'role_id' => $this->studentRole->id,
            'batch_id' => $this->batchA->id, 'guide_id' => $this->faculty1->id, 'phone' => '1111111111',
        ]);

        $student2 = User::create([
            'name' => 'Karan Patel', 'email' => 'karan@charusat.edu.in', 'enrollment_number' => '23IT002',
            'password' => bcrypt('password'), 'role_id' => $this->studentRole->id,
            'batch_id' => $this->batchA->id, 'guide_id' => $this->faculty1->id, 'phone' => '2222222222',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.batches.change-guide', $this->batchA->id), [
                'guide_id' => $this->faculty2->id,
            ]);

        $response->assertRedirect();

        $student1->refresh();
        $student2->refresh();
        $this->batchA->refresh();

        $this->assertEquals($this->faculty2->id, $student1->guide_id);
        $this->assertEquals($this->faculty2->id, $student2->guide_id);
        $this->assertEquals($this->faculty2->id, $this->batchA->guide_id);

        // Assert GuideHistory for both
        $this->assertDatabaseHas('guide_histories', [
            'student_id' => $student1->id,
            'old_guide_id' => $this->faculty1->id,
            'new_guide_id' => $this->faculty2->id,
        ]);
        $this->assertDatabaseHas('guide_histories', [
            'student_id' => $student2->id,
            'old_guide_id' => $this->faculty1->id,
            'new_guide_id' => $this->faculty2->id,
        ]);

        // Audit Log check
        $this->assertDatabaseHas('audit_logs', [
            'admin_name' => $this->admin->name,
            'action' => 'Changed Batch Guide',
        ]);
    }

    public function test_automatic_default_guide_assignment_when_students_moved_have_same_guide(): void
    {
        // 1. Create a target batch with no default guide
        $targetBatch = Batch::create([
            'name' => 'IT_2025_TEST',
            'guide_id' => null,
        ]);

        // 2. Create a faculty (Hemant Yadav)
        $hemant = User::create([
            'name' => 'Hemant Yadav',
            'email' => 'hemant@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '7777777777',
        ]);
        $hemant->assignPermission('guide');

        // 3. Create 5 students who are all assigned to Hemant Yadav
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = User::create([
                'name' => "Student {$i}",
                'email' => "student{$i}@charusat.edu.in",
                'enrollment_number' => "23IT10{$i}",
                'password' => bcrypt('password'),
                'role_id' => $this->studentRole->id,
                'batch_id' => $this->batchB->id, // Starts in batch B
                'guide_id' => $hemant->id,       // Guide is Hemant Yadav
                'phone' => "900000000{$i}",
            ]);
        }

        // 4. Move all 5 students to targetBatch (using bulk transfer)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.batches.bulk-transfer'), [
                'student_ids' => collect($students)->pluck('id')->toArray(),
                'batch_id' => $targetBatch->id,
            ]);

        $response->assertRedirect();

        // 5. Verify the target batch guide has been automatically updated to Hemant Yadav
        $targetBatch->refresh();
        $this->assertEquals($hemant->id, $targetBatch->guide_id);
    }
}
