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
     * Test importing students from XLSX.
     */
    public function test_import_students_from_xlsx(): void
    {
        $guide = User::create([
            'name' => 'CSV Guide',
            'email' => 'csv_guide@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '123'
        ]);
        $guide->assignPermission('guide');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['Enrollment Number', 'Name', 'Email', 'Department', 'Semester', 'Batch', 'Assigned Guide'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($headers as $colIdx => $header) {
            $sheet->setCellValue($cols[$colIdx] . '1', $header);
        }

        $row1 = ['21IT100', 'Student XLSX 1', 'stud1.xlsx@example.edu.in', 'IT', 6, 'IT_2023', 'csv_guide@example.ac.in'];
        foreach ($row1 as $colIdx => $val) {
            $sheet->setCellValue($cols[$colIdx] . '2', $val);
        }

        $row2 = ['21IT101', 'Student XLSX 2', 'stud2.xlsx@example.edu.in', 'IT', 6, 'IT_2023', 'nonexistent@example.ac.in'];
        foreach ($row2 as $colIdx => $val) {
            $sheet->setCellValue($cols[$colIdx] . '3', $val);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'students.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'file' => $file
        ]);

        // Clean up temp file
        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'students']));

        $this->assertDatabaseHas('batches', ['name' => 'IT_2023']);
        $batch = Batch::where('name', 'IT_2023')->first();

        // stud1 should be imported with batch and guide assigned
        $this->assertDatabaseHas('users', [
            'enrollment_number' => '21IT100',
            'email' => 'stud1.xlsx@example.edu.in',
            'batch_id' => $batch->id,
            'guide_id' => $guide->id,
            'role_id' => $this->studentRole->id,
        ]);

        // stud2 should be imported with batch but guide is null (guide not found)
        $this->assertDatabaseHas('users', [
            'enrollment_number' => '21IT101',
            'email' => 'stud2.xlsx@example.edu.in',
            'batch_id' => $batch->id,
            'guide_id' => null,
            'role_id' => $this->studentRole->id,
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
}
