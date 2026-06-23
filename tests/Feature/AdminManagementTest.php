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
     * Test mentor mapping imports with merged cells, carry forward, and multiple worksheets.
     */
    public function test_import_mentor_mappings_with_merged_cells_and_carry_forward(): void
    {
        // 1. Create pre-existing students in DB
        $students = [
            ['23IT001', 'Rahul Patel', 'rahul@example.edu.in'],
            ['23IT002', 'Amit Shah', 'amit@example.edu.in'],
            ['23IT003', 'Karan Patel', 'karan@example.edu.in'],
            ['23IT004', 'Priya Shah', 'priya@example.edu.in'],
            ['23CS001', 'CS Student 1', 'cs1@example.edu.in'],
            ['23CS002', 'CS Student 2', 'cs2@example.edu.in'],
        ];

        foreach ($students as $s) {
            User::create([
                'enrollment_number' => $s[0],
                'name' => $s[1],
                'email' => $s[2],
                'department' => 'IT',
                'semester' => 6,
                'role_id' => $this->studentRole->id,
                'password' => bcrypt('password'),
                'phone' => '123456789',
            ]);
        }

        // 2. Generate multi-sheet workbook using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // --- Sheet 1: IT_2023 (tests merged cells, decorative headings) ---
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('IT_2023');

        // Decorative headers
        $sheet1->setCellValue('A1', 'Faculty of Technology and Engineering');
        $sheet1->setCellValue('A2', 'Department of IT');
        $sheet1->setCellValue('A3', 'Semester 6');
        $sheet1->setCellValue('A4', '-------------------------------');

        // Real Headers
        $sheet1->setCellValue('A5', 'Student ID');
        $sheet1->setCellValue('B5', 'Student Name');
        $sheet1->setCellValue('C5', 'Mentor Faculty Name');

        // Data (Row 6 Bimal Patel, Rows 7-9 merged)
        $sheet1->setCellValue('A6', '23IT001');
        $sheet1->setCellValue('B6', 'Rahul Patel');
        $sheet1->setCellValue('C6', 'Dr. Bimal Patel');

        $sheet1->setCellValue('A7', '23IT002');
        $sheet1->setCellValue('B7', 'Amit Shah');

        $sheet1->setCellValue('A8', '23IT003');
        $sheet1->setCellValue('B8', 'Karan Patel');

        $sheet1->setCellValue('A9', '23IT004');
        $sheet1->setCellValue('B9', 'Priya Shah');

        // Merge cells vertically for the mentor
        $sheet1->mergeCells('C6:C9');

        // --- Sheet 2: CS_2023 (tests carry forward, blank rows) ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('CS_2023');

        // Real Headers
        $sheet2->setCellValue('A1', 'Enrollment Number');
        $sheet2->setCellValue('B1', 'Name');
        $sheet2->setCellValue('C1', 'Guide Name');

        // Data (Row 2 Vipul Patel, Row 3 empty mentor to carry forward)
        $sheet2->setCellValue('A2', '23CS001');
        $sheet2->setCellValue('B2', 'CS Student 1');
        $sheet2->setCellValue('C2', 'Prof. Vipul Patel');

        $sheet2->setCellValue('A3', '23CS002');
        $sheet2->setCellValue('B3', 'CS Student 2');
        // A3:C3 has empty mentor to test carry-forward

        // Decorative / Blank row at the end
        $sheet2->setCellValue('A4', '');
        $sheet2->setCellValue('B4', '');
        $sheet2->setCellValue('C4', '');

        // 3. Save to temporary XLSX file
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'mentor_mappings.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // 4. Test Preview Mapping Route
        $response = $this->actingAs($this->admin)->post(route('admin.mentor-mapping.preview'), [
            'file' => $file
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'mentor_mapping']));
        $response->assertSessionHas('mentor_mapping_preview');
        $response->assertSessionHas('mentor_mapping_json');

        $previewRows = session('mentor_mapping_preview');
        $this->assertCount(6, $previewRows); // 4 from IT, 2 from CS (blank row ignored)

        // Verify propagation in preview:
        // Rahul Patel, Amit Shah, Karan Patel, Priya Shah should all be mapped to Dr. Bimal Patel
        foreach (range(0, 3) as $idx) {
            $this->assertEquals('Dr. Bimal Patel', $previewRows[$idx]['mentor_name']);
            $this->assertEquals('bimalpatel.it@charusat.ac.in', $previewRows[$idx]['mentor_email']);
            $this->assertEquals('IT_2023', $previewRows[$idx]['batch']);
        }

        // CS Student 1 and CS Student 2 should be mapped to Prof. Vipul Patel
        foreach (range(4, 5) as $idx) {
            $this->assertEquals('Prof. Vipul Patel', $previewRows[$idx]['mentor_name']);
            $this->assertEquals('vipulpatel.it@charusat.ac.in', $previewRows[$idx]['mentor_email']);
            $this->assertEquals('CS_2023', $previewRows[$idx]['batch']);
        }

        // 5. Test Confirm Mapping Route
        $confirmResponse = $this->actingAs($this->admin)->post(route('admin.mentor-mapping.confirm'), [
            'mappings_json' => session('mentor_mapping_json')
        ]);

        $confirmResponse->assertRedirect(route('admin.dashboard', ['tab' => 'mentor_mapping']));
        $confirmResponse->assertSessionHas('import_report');
        
        $report = session('import_report');
        $this->assertEquals(6, $report['success']); // 6 mapped students
        $this->assertEquals(2, $report['created_faculty']); // Bimal Patel + Vipul Patel
        $this->assertEquals(2, $report['created_batches']); // IT_2023 + CS_2023

        // 6. Verify Database State
        // Check Batches
        $this->assertDatabaseHas('batches', ['name' => 'IT_2023']);
        $this->assertDatabaseHas('batches', ['name' => 'CS_2023']);

        $batchIt = Batch::where('name', 'IT_2023')->first();
        $batchCs = Batch::where('name', 'CS_2023')->first();

        // Check Faculty (Guides)
        $this->assertDatabaseHas('users', [
            'name' => 'Dr. Bimal Patel',
            'email' => 'bimalpatel.it@charusat.ac.in',
            'role_id' => $this->facultyRole->id,
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Prof. Vipul Patel',
            'email' => 'vipulpatel.it@charusat.ac.in',
            'role_id' => $this->facultyRole->id,
        ]);

        $guideBimal = User::where('email', 'bimalpatel.it@charusat.ac.in')->first();
        $guideVipul = User::where('email', 'vipulpatel.it@charusat.ac.in')->first();

        // Check Students are updated with guides and batches
        foreach (['23IT001', '23IT002', '23IT003', '23IT004'] as $enroll) {
            $this->assertDatabaseHas('users', [
                'enrollment_number' => $enroll,
                'batch_id' => $batchIt->id,
                'guide_id' => $guideBimal->id,
            ]);
        }

        foreach (['23CS001', '23CS002'] as $enroll) {
            $this->assertDatabaseHas('users', [
                'enrollment_number' => $enroll,
                'batch_id' => $batchCs->id,
                'guide_id' => $guideVipul->id,
            ]);
        }

        // Verify GuideAssignment logs
        foreach (['23IT001', '23IT002', '23IT003', '23IT004'] as $enroll) {
            $student = User::where('enrollment_number', $enroll)->first();
            $this->assertDatabaseHas('guide_assignments', [
                'student_id' => $student->id,
                'guide_id' => $guideBimal->id,
                'unassigned_at' => null,
            ]);
        }
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

        $guide2 = User::create([
            'name' => 'Guide Two',
            'email' => 'guide2@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id,
            'phone' => '2222222222',
        ]);

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
