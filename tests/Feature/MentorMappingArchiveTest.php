<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Batch;
use App\Models\MentorMappingArchive;
use App\Models\ArchivedMentorMapping;
use App\Models\GuideAssignment;
use App\Models\GuideHistory;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MentorMappingArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $adminRole;
    protected Role $facultyRole;
    protected Role $studentRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Use local disk for testing storage
        Storage::fake('local');

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
    }

    private function createMockSpreadsheetPath(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('IT_2023');

        // Headers
        $sheet->setCellValue('A1', 'Student ID');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->setCellValue('C1', 'Mentor');
        $sheet->setCellValue('D1', 'Email');

        // Data
        $sheet->setCellValue('A2', '23IT001');
        $sheet->setCellValue('B2', 'Rahul Patel');
        $sheet->setCellValue('C2', 'Dr. Bimal Patel');
        $sheet->setCellValue('D2', 'bimal@example.ac.in');

        $sheet->setCellValue('A3', '23IT002');
        $sheet->setCellValue('B3', 'Amit Shah');
        $sheet->setCellValue('C3', 'Dr. Bimal Patel');
        $sheet->setCellValue('D3', 'bimal@example.ac.in');

        // Save
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    public function test_preview_mentor_mapping_calculates_differences(): void
    {
        // 1. Create a student already in the database
        $student = User::create([
            'name' => 'Rahul Patel',
            'email' => 'rahul@charusat.edu.in',
            'enrollment_number' => '23IT001',
            'password' => bcrypt('password'),
            'role_id' => $this->studentRole->id,
            'phone' => '1111111111',
        ]);

        $tempPath = $this->createMockSpreadsheetPath();
        $file = new UploadedFile(
            $tempPath,
            'mentor_mappings.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('admin.mentor-mapping.preview'), [
            'file' => $file
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'mentor_mapping']));
        $response->assertSessionHas('mentor_mapping_preview');
        $response->assertSessionHas('mentor_mapping_comparison');

        $comp = session('mentor_mapping_comparison');

        // Amit Shah (23IT002) is not in database, so should be under 'added'
        $this->assertCount(1, $comp['added']);
        $this->assertEquals('23IT002', $comp['added'][0]['enrollment']);

        // Rahul Patel (23IT001) is already in database, so not added
        $this->assertCount(0, $comp['removed']);
    }

    public function test_confirm_mentor_mapping_creates_snapshot_archive(): void
    {
        // Create an existing student & guide
        $guide = User::create([
            'name' => 'Old Guide', 'email' => 'old@example.ac.in', 'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id, 'phone' => '2222222222',
        ]);
        $student = User::create([
            'name' => 'Rahul Patel', 'email' => 'rahul@charusat.edu.in', 'enrollment_number' => '23IT001',
            'password' => bcrypt('password'), 'role_id' => $this->studentRole->id,
            'guide_id' => $guide->id, 'phone' => '1111111111',
        ]);

        $mappingsJson = json_encode([
            [
                'enrollment' => '23IT001',
                'student_name' => 'Rahul Patel',
                'mentor_name' => 'Dr. Bimal Patel',
                'mentor_email' => 'bimal@example.ac.in',
                'batch' => 'IT_2023',
                'student_exists' => true,
                'status' => 'Update Assignment',
            ]
        ]);

        // Place a mock file in temp mappings storage
        Storage::put('temp_mentor_mappings/mock_import.xlsx', 'excel contents');

        $response = $this->actingAs($this->admin)->post(route('admin.mentor-mapping.confirm'), [
            'mappings_json' => $mappingsJson,
            'file_name' => 'new_mentor_mappings.xlsx',
            'file_path' => 'temp_mentor_mappings/mock_import.xlsx',
        ]);

        $response->assertRedirect();

        // 1. Verify new guide mapped in database
        $student->refresh();
        $this->assertNotEquals($guide->id, $student->guide_id);

        // 2. Verify archive created representing PREVIOUS database state (with Old Guide)
        $this->assertDatabaseHas('mentor_mapping_archives', [
            'file_name' => 'new_mentor_mappings.xlsx',
            'total_students' => 1,
            'total_guides' => 1, // Old Guide
        ]);

        $archive = MentorMappingArchive::first();
        $this->assertDatabaseHas('archived_mentor_mappings', [
            'archive_id' => $archive->id,
            'student_id' => $student->id,
            'guide_id' => $guide->id,
            'guide_name' => 'Old Guide',
        ]);

        // 3. Verify Excel file was moved to permanent archive storage
        Storage::assertExists('mentor_mapping_archives/mock_import.xlsx');
    }

    public function test_archive_pages_load_and_restore_works(): void
    {
        $guide = User::create([
            'name' => 'Archived Guide', 'email' => 'archived@example.ac.in', 'password' => bcrypt('password'),
            'role_id' => $this->facultyRole->id, 'phone' => '3333333333',
        ]);
        $student = User::create([
            'name' => 'Rahul Patel', 'email' => 'rahul@charusat.edu.in', 'enrollment_number' => '23IT001',
            'password' => bcrypt('password'), 'role_id' => $this->studentRole->id,
            'guide_id' => null, 'phone' => '1111111111',
        ]);

        // 1. Manually build an archive snapshot
        $archive = MentorMappingArchive::create([
            'import_date' => now(),
            'imported_by' => $this->admin->id,
            'file_name' => 'archive_snapshot.xlsx',
            'total_students' => 1,
            'total_guides' => 1,
            'total_batches' => 0,
        ]);

        ArchivedMentorMapping::create([
            'archive_id' => $archive->id,
            'student_id' => $student->id,
            'student_name' => 'Rahul Patel',
            'enrollment_number' => '23IT001',
            'guide_id' => $guide->id,
            'guide_name' => 'Archived Guide',
        ]);

        // 2. Load index page
        $response = $this->actingAs($this->admin)->get(route('admin.mentor-mapping.archives'));
        $response->assertStatus(200);
        $response->assertSee('archive_snapshot.xlsx');

        // 3. Load show page
        $response = $this->actingAs($this->admin)->get(route('admin.mentor-mapping.archives.show', $archive->id));
        $response->assertStatus(200);
        $response->assertSee('Archived Guide');

        // 4. Restore the mappings (requires typed 'RESTORE')
        // Attempt with invalid text first
        $this->actingAs($this->admin)->post(route('admin.mentor-mapping.archives.restore', $archive->id), [
            'confirmation_text' => 'INVALID'
        ])->assertSessionHas('error');

        // Restore correctly
        $restoreResponse = $this->actingAs($this->admin)->post(route('admin.mentor-mapping.archives.restore', $archive->id), [
            'confirmation_text' => 'RESTORE'
        ]);

        $restoreResponse->assertRedirect();
        
        // Student should now be assigned to Archived Guide
        $student->refresh();
        $this->assertEquals($guide->id, $student->guide_id);

        // Audit Log entry created
        $this->assertDatabaseHas('audit_logs', [
            'admin_name' => $this->admin->name,
            'action' => 'Restored Mentor Mapping Archive',
        ]);
    }
}
