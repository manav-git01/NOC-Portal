<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Batch;
use App\Models\GuideAssignment;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletionConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $studentRole;
    protected Role $facultyRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $this->studentRole = Role::where('name', 'student')->first();
        $this->facultyRole = Role::where('name', 'faculty')->first();

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'phone' => '1234567890',
        ]);
    }

    /**
     * Test student deletion: Correct confirmation text allows deletion.
     */
    public function test_student_deletion_succeeds_with_correct_confirmation(): void
    {
        $student = User::create([
            'enrollment_number' => '23IT001',
            'name' => 'Rahul Patel',
            'email' => 'rahul@example.edu.in',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'phone' => '9876543210',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.students.destroy', $student->id), [
            'confirmation_text' => '23IT001',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'students']));
        $response->assertSessionHas('success', 'Student deleted successfully.');

        // Verify soft deletion
        $this->assertSoftDeleted('users', ['id' => $student->id]);

        // Verify Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'admin_name' => 'System Admin',
            'action' => 'Deleted Student',
            'target' => "Student Name: Rahul Patel, Enrollment: 23IT001, Email: rahul@example.edu.in",
        ]);
    }

    /**
     * Test student deletion: Incorrect confirmation text blocks deletion.
     */
    public function test_student_deletion_aborted_with_incorrect_confirmation(): void
    {
        $student = User::create([
            'enrollment_number' => '23IT001',
            'name' => 'Rahul Patel',
            'email' => 'rahul@example.edu.in',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'phone' => '9876543210',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.students.destroy', $student->id), [
            'confirmation_text' => 'WRONG_CODE',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'students']));
        $response->assertSessionHas('error', 'Student deletion aborted: Deletion confirmation text did not match the Enrollment Number.');

        // Verify student NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test faculty deletion: Correct confirmation text allows deletion when no dependencies.
     */
    public function test_faculty_deletion_succeeds_with_correct_confirmation_and_no_dependencies(): void
    {
        $faculty = User::create([
            'name' => 'Bimal Patel',
            'email' => 'bimal@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1122334455',
            'faculty_id' => 'FAC001',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.faculty.destroy', $faculty->id), [
            'confirmation_text' => 'Bimal Patel',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $response->assertSessionHas('success', 'Faculty deleted successfully.');

        // Verify soft deletion
        $this->assertSoftDeleted('users', ['id' => $faculty->id]);

        // Verify Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'admin_name' => 'System Admin',
            'action' => 'Deleted Faculty',
            'target' => "Faculty Name: Bimal Patel, Email: bimal@example.ac.in, Faculty ID: FAC001",
        ]);
    }

    /**
     * Test faculty deletion: Incorrect confirmation text blocks deletion.
     */
    public function test_faculty_deletion_aborted_with_incorrect_confirmation(): void
    {
        $faculty = User::create([
            'name' => 'Bimal Patel',
            'email' => 'bimal@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1122334455',
            'faculty_id' => 'FAC001',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.faculty.destroy', $faculty->id), [
            'confirmation_text' => 'Bimal Patel Wrong',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $response->assertSessionHas('error', 'Faculty deletion aborted: Deletion confirmation text did not match the Faculty Name.');

        // Verify NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test faculty deletion: Blocked if assigned as Guide to a student.
     */
    public function test_faculty_deletion_blocked_if_guide_to_student(): void
    {
        $faculty = User::create([
            'name' => 'Bimal Patel',
            'email' => 'bimal@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1122334455',
            'faculty_id' => 'FAC001',
        ]);

        // Create student assigned to this guide
        $student = User::create([
            'enrollment_number' => '23IT001',
            'name' => 'Rahul Patel',
            'email' => 'rahul@example.edu.in',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'phone' => '9876543210',
            'guide_id' => $faculty->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.faculty.destroy', $faculty->id), [
            'confirmation_text' => 'Bimal Patel',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $response->assertSessionHas('error', 'This faculty cannot be deleted because active assignments exist. Please reassign responsibilities first.');

        // Verify NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test faculty deletion: Blocked if has active mentor mapping (GuideAssignment).
     */
    public function test_faculty_deletion_blocked_if_active_mentor_mapping(): void
    {
        $faculty = User::create([
            'name' => 'Bimal Patel',
            'email' => 'bimal@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1122334455',
            'faculty_id' => 'FAC001',
        ]);

        // Create student
        $student = User::create([
            'enrollment_number' => '23IT001',
            'name' => 'Rahul Patel',
            'email' => 'rahul@example.edu.in',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'phone' => '9876543210',
        ]);

        // Create active GuideAssignment
        GuideAssignment::create([
            'student_id' => $student->id,
            'guide_id' => $faculty->id,
            'assigned_at' => now(),
            'unassigned_at' => null,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.faculty.destroy', $faculty->id), [
            'confirmation_text' => 'Bimal Patel',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $response->assertSessionHas('error', 'This faculty cannot be deleted because active assignments exist. Please reassign responsibilities first.');

        // Verify NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test faculty deletion: Blocked if has special rights (approval_faculty or noc_authority).
     */
    public function test_faculty_deletion_blocked_if_has_special_authority(): void
    {
        $faculty1 = User::create([
            'name' => 'Bimal Patel',
            'email' => 'bimal@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1122334455',
            'faculty_id' => 'FAC001',
        ]);
        $faculty1->assignPermission('approval_faculty');

        $faculty2 = User::create([
            'name' => 'Vipul Patel',
            'email' => 'vipul@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '2233445566',
            'faculty_id' => 'FAC002',
        ]);
        $faculty2->assignPermission('noc_authority');

        // Test approval_faculty deletion blocked
        $response1 = $this->actingAs($this->admin)->delete(route('admin.faculty.destroy', $faculty1->id), [
            'confirmation_text' => 'Bimal Patel',
        ]);
        $response1->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $response1->assertSessionHas('error', 'This faculty cannot be deleted because active assignments exist. Please reassign responsibilities first.');

        // Test noc_authority deletion blocked
        $response2 = $this->actingAs($this->admin)->delete(route('admin.faculty.destroy', $faculty2->id), [
            'confirmation_text' => 'Vipul Patel',
        ]);
        $response2->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $response2->assertSessionHas('error', 'This faculty cannot be deleted because active assignments exist. Please reassign responsibilities first.');

        // Verify NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $faculty1->id,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $faculty2->id,
            'deleted_at' => null,
        ]);
    }
}
