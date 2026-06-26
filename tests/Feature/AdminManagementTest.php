<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Batch;
use App\Models\GuideAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminManagementTest extends TestCase
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
     * Test batch CRUD operations.
     */
    public function test_batch_crud(): void
    {
        // 1. Create Batch
        $response = $this->actingAs($this->admin)->post(route('admin.batches.store'), [
            'name' => 'IT_2023'
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'batches']));
        $this->assertDatabaseHas('batches', ['name' => 'IT_2023']);
        $batch = Batch::where('name', 'IT_2023')->first();

        // 2. Update Batch
        $response = $this->actingAs($this->admin)->put(route('admin.batches.update', $batch->id), [
            'name' => 'IT_2023_UPDATED'
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'batches']));
        $this->assertDatabaseHas('batches', ['name' => 'IT_2023_UPDATED']);

        // 3. Delete Batch
        $response = $this->actingAs($this->admin)->delete(route('admin.batches.destroy', $batch->id));

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'batches']));
        $this->assertDatabaseMissing('batches', ['name' => 'IT_2023_UPDATED']);
    }


    /**
     * Test manual student creation, editing and deletion.
     */
    public function test_student_management(): void
    {
        $batch = Batch::create(['name' => 'IT_2023']);
        
        $guide = User::create([
            'name' => 'Guide Faculty',
            'email' => 'guide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1122334455',
        ]);
        $guide->assignPermission('guide');

        // 1. Create Student
        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'enrollment_number' => '21IT001',
            'name' => 'Alice Green',
            'email' => 'alice@example.edu.in',
            'department' => 'Information Technology',
            'semester' => 6,
            'batch_id' => $batch->id,
            'guide_id' => $guide->id,
            'password' => 'student123',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'students']));
        $this->assertDatabaseHas('users', [
            'enrollment_number' => '21IT001',
            'email' => 'alice@example.edu.in',
            'batch_id' => $batch->id,
            'guide_id' => $guide->id,
            'role_id' => $this->studentRole->id,
        ]);
        $student = User::where('email', 'alice@example.edu.in')->first();

        // Verify GuideAssignment was logged
        $this->assertDatabaseHas('guide_assignments', [
            'student_id' => $student->id,
            'guide_id' => $guide->id,
            'unassigned_at' => null,
        ]);

        // 2. Update Student (Change Guide & Batch)
        $newGuide = User::create([
            'name' => 'New Guide Faculty',
            'email' => 'newguide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '5544332211',
        ]);
        $newGuide->assignPermission('guide');

        $response = $this->actingAs($this->admin)->put(route('admin.students.update', $student->id), [
            'enrollment_number' => '21IT001_MOD',
            'name' => 'Alice Green Updated',
            'email' => 'alice@example.edu.in',
            'department' => 'Information Technology',
            'semester' => 7,
            'batch_id' => $batch->id,
            'guide_id' => $newGuide->id,
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'students']));
        $this->assertDatabaseHas('users', [
            'enrollment_number' => '21IT001_MOD',
            'semester' => 7,
            'guide_id' => $newGuide->id,
        ]);

        // Verify previous guide assignment was unassigned and new assignment created
        $this->assertDatabaseHas('guide_assignments', [
            'student_id' => $student->id,
            'guide_id' => $guide->id,
            'unassigned_at' => now()->toDateTimeString(), // updated_at or unassigned_at is not null
        ]);
        $this->assertDatabaseHas('guide_assignments', [
            'student_id' => $student->id,
            'guide_id' => $newGuide->id,
            'unassigned_at' => null,
        ]);

        // 3. Delete Student
        $response = $this->actingAs($this->admin)->delete(route('admin.students.destroy', $student->id), [
            'confirmation_text' => '21IT001_MOD',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'students']));
        $this->assertSoftDeleted('users', ['email' => 'alice@example.edu.in']);
    }

    /**
     * Test StudentDirectoryController store, update and destroy routes (used by UI).
     */
    public function test_student_directory_management(): void
    {
        $batch = Batch::create(['name' => 'IT_2023']);
        
        $guide = User::create([
            'name' => 'Guide Faculty',
            'email' => 'guide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1122334455',
        ]);
        $guide->assignPermission('guide');

        // 1. Create Student
        $response = $this->actingAs($this->admin)->post(route('admin.student-directory.store'), [
            'enrollment_number' => '21IT001',
            'name' => 'Alice Green',
            'email' => 'alice@example.edu.in',
            'department' => 'Information Technology',
            'semester' => 6,
            'batch_id' => $batch->id,
            'guide_id' => $guide->id,
        ]);

        $response->assertRedirect(route('admin.student-directory.index', ['tab' => 'student_directory']));
        $this->assertDatabaseHas('users', [
            'enrollment_number' => '21IT001',
            'email' => 'alice@example.edu.in',
            'batch_id' => $batch->id,
            'guide_id' => $guide->id,
            'role_id' => $this->studentRole->id,
        ]);
        $student = User::where('email', 'alice@example.edu.in')->first();

        // Verify GuideAssignment was logged
        $this->assertDatabaseHas('guide_assignments', [
            'student_id' => $student->id,
            'guide_id' => $guide->id,
            'unassigned_at' => null,
        ]);

        // 2. Update Student
        $newGuide = User::create([
            'name' => 'New Guide Faculty',
            'email' => 'newguide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '5544332211',
        ]);
        $newGuide->assignPermission('guide');

        $response = $this->actingAs($this->admin)->put(route('admin.student-directory.update', $student->id), [
            'enrollment_number' => '21IT001_MOD',
            'name' => 'Alice Green Updated',
            'email' => 'alice@example.edu.in',
            'department' => 'Information Technology',
            'semester' => 7,
            'batch_id' => $batch->id,
            'guide_id' => $newGuide->id,
        ]);

        $response->assertRedirect(route('admin.student-directory.index', ['tab' => 'student_directory']));
        $this->assertDatabaseHas('users', [
            'enrollment_number' => '21IT001_MOD',
            'semester' => 7,
            'guide_id' => $newGuide->id,
        ]);

        // 3. Delete Student
        $response = $this->actingAs($this->admin)->delete(route('admin.student-directory.destroy', $student->id), [
            'confirmation_text' => '21IT001_MOD',
        ]);

        $response->assertRedirect(route('admin.student-directory.index', ['tab' => 'student_directory']));
        $this->assertSoftDeleted('users', ['email' => 'alice@example.edu.in']);
    }

    /**
     * Test restoring a soft-deleted student when re-adding them.
     */
    public function test_restore_soft_deleted_student_on_store(): void
    {
        $batch = Batch::create(['name' => 'IT_2023']);
        $studentRole = Role::where('name', 'student')->first();

        // 1. Create a student and soft delete them
        $student = User::create([
            'enrollment_number' => '21IT001',
            'name' => 'Deleted Student',
            'email' => 'deletedstudent@example.edu.in',
            'department' => 'IT',
            'semester' => 6,
            'role_id' => $studentRole->id,
            'phone' => '12345',
            'password' => bcrypt('password'),
        ]);
        $student->delete();
        $this->assertSoftDeleted('users', ['id' => $student->id]);

        // 2. Try to add them manually again
        $response = $this->actingAs($this->admin)->post(route('admin.student-directory.store'), [
            'enrollment_number' => '21IT001',
            'name' => 'Restored Student',
            'email' => 'deletedstudent@example.edu.in',
            'department' => 'Information Technology',
            'semester' => 7,
            'batch_id' => $batch->id,
        ]);

        $response->assertRedirect(route('admin.student-directory.index', ['tab' => 'student_directory']));
        
        // Assert student was restored and updated
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'enrollment_number' => '21IT001',
            'name' => 'Restored Student',
            'email' => 'deletedstudent@example.edu.in',
            'department' => 'Information Technology',
            'semester' => 7,
            'batch_id' => $batch->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test restoring a soft-deleted faculty when re-adding them.
     */
    public function test_restore_soft_deleted_faculty_on_store(): void
    {
        $facultyRole = Role::where('name', 'faculty')->first();

        // 1. Create faculty and soft delete them
        $faculty = User::create([
            'faculty_id' => 'FAC001',
            'name' => 'Deleted Faculty',
            'email' => 'deletedfaculty@example.ac.in',
            'department' => 'IT',
            'designation' => 'Assistant Professor',
            'role_id' => $facultyRole->id,
            'phone' => '12345',
            'password' => bcrypt('password'),
        ]);
        $faculty->delete();
        $this->assertSoftDeleted('users', ['id' => $faculty->id]);

        // 2. Try to add them manually again
        $response = $this->actingAs($this->admin)->post(route('admin.faculty-directory.store'), [
            'faculty_id' => 'FAC001',
            'name' => 'Restored Faculty',
            'email' => 'deletedfaculty@example.ac.in',
            'department' => 'Computer Science',
            'designation' => 'Associate Professor',
        ]);

        $response->assertRedirect(route('admin.faculty-directory.index', ['tab' => 'faculty_directory']));

        // Assert faculty was restored and updated
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'faculty_id' => 'FAC001',
            'name' => 'Restored Faculty',
            'email' => 'deletedfaculty@example.ac.in',
            'department' => 'Computer Science',
            'designation' => 'Associate Professor',
            'deleted_at' => null,
        ]);
    }







    /**
     * Test faculty authority updates.
     */
    public function test_faculty_authority_update(): void
    {
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first();

        $faculty = User::create([
            'name' => 'John Faculty',
            'email' => 'john.fac@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '9998887776',
        ]);

        // 1. Assign NOC Authority / Higher Faculty
        $response = $this->actingAs($this->admin)->put(route('admin.faculty.update-authority', $faculty->id), [
            'permissions' => ['noc_authority']
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $this->assertDatabaseHas('faculty_permissions', [
            'user_id' => $faculty->id,
            'permission' => 'noc_authority',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'role_id' => $higherFacultyRole->id,
        ]);

        // 2. Assign Approval Faculty
        $response = $this->actingAs($this->admin)->put(route('admin.faculty.update-authority', $faculty->id), [
            'permissions' => ['approval_faculty']
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $this->assertDatabaseHas('faculty_permissions', [
            'user_id' => $faculty->id,
            'permission' => 'approval_faculty',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'role_id' => $this->facultyRole->id,
        ]);

        // 3. Assign Guide Faculty
        $response = $this->actingAs($this->admin)->put(route('admin.faculty.update-authority', $faculty->id), [
            'permissions' => ['guide']
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'faculty_authority']));
        $this->assertDatabaseHas('faculty_permissions', [
            'user_id' => $faculty->id,
            'permission' => 'guide',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'role_id' => $this->facultyRole->id,
        ]);
    }

    /**
     * Test batch guide reassignment.
     */
    public function test_batch_guide_reassignment(): void
    {
        $batch = Batch::create(['name' => 'IT_2023']);

        $guide1 = User::create([
            'name' => 'Guide One',
            'email' => 'guide1@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '1111111111',
        ]);
        $guide1->assignPermission('guide');

        $guide2 = User::create([
            'name' => 'Guide Two',
            'email' => 'guide2@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '2222222222',
        ]);
        $guide2->assignPermission('guide');

        $student = User::create([
            'enrollment_number' => '21IT201',
            'name' => 'Student Bob',
            'email' => 'bob@example.edu.in',
            'department' => 'IT',
            'semester' => 6,
            'batch_id' => $batch->id,
            'guide_id' => $guide1->id,
            'role_id' => $this->studentRole->id,
            'phone' => '12345',
            'password' => bcrypt('password'),
        ]);

        GuideAssignment::create([
            'student_id' => $student->id,
            'guide_id' => $guide1->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        // Reassign the batch to Guide Two
        $response = $this->actingAs($this->admin)->post(route('admin.batches.reassign-guide', $batch->id), [
            'guide_id' => $guide2->id
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'batches']));
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'guide_id' => $guide2->id,
        ]);

        // Check guide assignments
        $this->assertDatabaseHas('guide_assignments', [
            'student_id' => $student->id,
            'guide_id' => $guide1->id,
            'unassigned_at' => now()->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('guide_assignments', [
            'student_id' => $student->id,
            'guide_id' => $guide2->id,
            'unassigned_at' => null,
        ]);
    }

    /**
     * Test student search combined with approved application status filter (including noc_generated status).
     */
    public function test_student_search_and_approved_filter(): void
    {
        $student = User::create([
            'enrollment_number' => '21IT999',
            'name' => 'Bob Approved Student',
            'email' => 'bobapproved@example.edu.in',
            'department' => 'IT',
            'semester' => 6,
            'role_id' => $this->studentRole->id,
            'phone' => '123456',
            'password' => bcrypt('password'),
        ]);

        // Create internship application in final approved status (noc_generated)
        $application = \App\Models\InternshipApplication::create([
            'user_id' => $student->id,
            'company_name' => 'Google Inc',
            'company_address' => '123 Main St',
            'company_website' => 'https://google.com',
            'branch_address' => '456 Branch Ave',
            'number_of_employees' => '100-500',
            'technology' => 'Laravel',
            'how_did_you_get_company' => 'Through job portal',
            'reason_to_select_company' => 'Good opportunity',
            'internship_position' => 'Backend Developer',
            'company_letter_path' => 'letters/sample.pdf',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'noc_generated',
        ]);

        // Make search request combining name search 'Bob' and app_status 'approved'
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard', [
            'student_search' => 'Bob',
            'app_status' => 'approved',
            'tab' => 'students',
        ]));

        $response->assertStatus(200);
        
        // Assert Bob Approved Student is returned in students list
        $viewStudents = $response->viewData('students');
        $this->assertNotNull($viewStudents);
        $this->assertTrue($viewStudents->contains('id', $student->id));
    }

    /**
     * Test that student lists are sorted by enrollment_number and faculty lists by name.
     */
    public function test_sorting_order(): void
    {
        // 1. Create multiple students with out-of-order names and enrollment numbers
        $studentC = User::create([
            'enrollment_number' => '21IT003',
            'name' => 'Charlie Student',
            'email' => 'charlie@example.edu.in',
            'department' => 'IT',
            'semester' => 6,
            'role_id' => $this->studentRole->id,
            'phone' => '123',
            'password' => bcrypt('password'),
        ]);

        $studentA = User::create([
            'enrollment_number' => '21IT001',
            'name' => 'Alice Student',
            'email' => 'alice@example.edu.in',
            'department' => 'IT',
            'semester' => 6,
            'role_id' => $this->studentRole->id,
            'phone' => '123',
            'password' => bcrypt('password'),
        ]);

        $studentB = User::create([
            'enrollment_number' => '21IT002',
            'name' => 'Bob Student',
            'email' => 'bob@example.edu.in',
            'department' => 'IT',
            'semester' => 6,
            'role_id' => $this->studentRole->id,
            'phone' => '123',
            'password' => bcrypt('password'),
        ]);

        // 2. Create multiple faculty with out-of-order names
        $facultyB = User::create([
            'name' => 'Bob Faculty',
            'email' => 'bob_fac@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '123',
        ]);

        $facultyA = User::create([
            'name' => 'Alice Faculty',
            'email' => 'alice_fac@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '123',
        ]);

        // Access Student Directory index
        $response = $this->actingAs($this->admin)->get(route('admin.student-directory.index'));
        $response->assertStatus(200);
        $students = $response->viewData('students');
        
        // Assert sorted by enrollment_number: Alice (21IT001) -> Bob (21IT002) -> Charlie (21IT003)
        $this->assertEquals('21IT001', $students->first()->enrollment_number);
        $this->assertEquals('21IT002', $students->get(1)->enrollment_number);
        $this->assertEquals('21IT003', $students->get(2)->enrollment_number);

        // Access Faculty Directory index
        $response = $this->actingAs($this->admin)->get(route('admin.faculty-directory.index'));
        $response->assertStatus(200);
        $faculty = $response->viewData('faculty');
        
        // Assert sorted by name (alphabetically): Alice Faculty -> Bob Faculty
        $names = $faculty->pluck('name')->toArray();
        $sortedNames = $names;
        sort($sortedNames);
        $this->assertEquals($sortedNames, $names);
    }
}
