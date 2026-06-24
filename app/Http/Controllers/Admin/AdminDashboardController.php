<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Batch;
use App\Models\GuideAssignment;
use App\Models\InternshipApplication;
use App\Models\Noc;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with stats, student management, and batch directory.
     */
    public function index(Request $request)
    {
        // 1. Resolve Role IDs
        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        // 2. Statistics
        $totalStudents = User::where('role_id', $studentRole->id)->count();
        $totalFaculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])->count();
        
        // Guides are Faculty who have at least one student assigned to them
        $totalGuides = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->whereIn('id', User::where('role_id', $studentRole->id)->whereNotNull('guide_id')->pluck('guide_id'))
            ->count();

        $totalBatches = Batch::count();
        $totalApplications = InternshipApplication::count();
        
        $pendingApplications = InternshipApplication::whereIn('status', [
            'pending', 
            'pending_higher'
        ])->count();

        $approvedApplications = InternshipApplication::whereIn('status', [
            'faculty_approved', 
            'higher_faculty_approved', 
            'noc_generated'
        ])->count();
        
        $generatedNocs = Noc::count();

        // 3. Batches Listing with guide counts and internship stats
        $batches = Batch::withCount(['students' => function($query) use ($studentRole) {
            $query->where('role_id', $studentRole->id);
        }])->get();

        // Enhance batches with guide counts and internship statistics
        foreach ($batches as $batch) {
            // Count distinct guides assigned to students in this batch
            $batch->guides_count = User::where('role_id', $studentRole->id)
                ->where('batch_id', $batch->id)
                ->whereNotNull('guide_id')
                ->distinct('guide_id')
                ->count('guide_id');

            // Internship stats for this batch
            $batchStudentIds = User::where('role_id', $studentRole->id)
                ->where('batch_id', $batch->id)
                ->pluck('id');

            $batch->pending_apps = InternshipApplication::whereIn('user_id', $batchStudentIds)
                ->whereIn('status', ['pending', 'pending_higher'])->count();
            $batch->approved_apps = InternshipApplication::whereIn('user_id', $batchStudentIds)
                ->whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'noc_generated'])->count();
            $batch->noc_count = Noc::whereIn('application_id', 
                InternshipApplication::whereIn('user_id', $batchStudentIds)->pluck('id')
            )->count();
        }

        // 4. Faculty Directory
        $faculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->with(['permissions'])
            ->withCount('students')
            ->orderBy('name')->get();

        // Add batch counts for each faculty member
        foreach ($faculty as $fac) {
            $fac->batches_count = User::where('guide_id', $fac->id)
                ->where('role_id', $studentRole->id)
                ->whereNotNull('batch_id')
                ->distinct('batch_id')
                ->count('batch_id');
        }

        // 5. Students Listing with Filtering & Searching
        $studentsQuery = User::where('role_id', $studentRole->id)
            ->with(['batch', 'guide', 'guideAssignments.guide', 'internshipApplications']);

        if ($request->filled('student_search')) {
            $search = $request->student_search;
            $studentsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('enrollment_number', 'like', "%$search%");
            });
        }

        if ($request->filled('batch_id')) {
            $studentsQuery->where('batch_id', $request->batch_id);
        }

        if ($request->filled('guide_id')) {
            $studentsQuery->where('guide_id', $request->guide_id);
        }

        if ($request->filled('department')) {
            $studentsQuery->where('department', $request->department);
        }

        if ($request->filled('semester')) {
            $studentsQuery->where('semester', $request->semester);
        }

        // Application status filter
        if ($request->filled('app_status')) {
            $statusFilter = $request->app_status;
            if ($statusFilter === 'no_application') {
                $studentsQuery->whereDoesntHave('internshipApplications');
            } elseif ($statusFilter === 'noc_generated') {
                $studentsQuery->whereHas('internshipApplications', fn($q) => $q->where('status', 'noc_generated'));
            } elseif ($statusFilter === 'pending') {
                $studentsQuery->whereHas('internshipApplications', fn($q) => $q->whereIn('status', ['pending', 'pending_higher']));
            } elseif ($statusFilter === 'approved') {
                $studentsQuery->whereHas('internshipApplications', fn($q) => $q->whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'noc_generated']));
            } elseif ($statusFilter === 'rejected') {
                $studentsQuery->whereHas('internshipApplications', fn($q) => $q->whereIn('status', ['faculty_rejected', 'higher_faculty_rejected']));
            }
        }

        $students = $studentsQuery->orderBy('name')->get();

        // Filter Options
        $departments = User::where('role_id', $studentRole->id)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        $semesters = User::where('role_id', $studentRole->id)
            ->whereNotNull('semester')
            ->distinct()
            ->pluck('semester');

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalFaculty',
            'totalGuides',
            'totalBatches',
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'generatedNocs',
            'batches',
            'faculty',
            'students',
            'departments',
            'semesters'
        ));
    }

    // ==========================================
    // BATCH MANAGEMENT
    // ==========================================

    public function storeBatch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:batches,name',
        ]);

        Batch::create(['name' => $request->name]);

        return redirect()->route('admin.dashboard', ['tab' => 'batches'])->with('success', 'Batch created successfully.');
    }

    public function updateBatch(Request $request, Batch $batch)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:batches,name,' . $batch->id,
        ]);

        $batch->update(['name' => $request->name]);

        return redirect()->route('admin.dashboard', ['tab' => 'batches'])->with('success', 'Batch updated successfully.');
    }

    public function destroyBatch(Batch $batch)
    {
        $batch->delete();
        return redirect()->route('admin.dashboard', ['tab' => 'batches'])->with('success', 'Batch deleted successfully.');
    }

    // ==========================================
    // STUDENT MANAGEMENT
    // ==========================================

    public function storeStudent(Request $request)
    {
        $request->validate([
            'enrollment_number' => 'required|string|max:255|unique:users,enrollment_number',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'department' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:10',
            'batch_id' => 'nullable|exists:batches,id',
            'guide_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !User::find($value)?->hasPermission('guide')) {
                        $fail('The selected guide must have guide authority.');
                    }
                }
            ],
            'password' => 'nullable|string|min:6',
        ]);

        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];

        $student = User::create([
            'enrollment_number' => $request->enrollment_number,
            'name' => $request->name,
            'email' => $request->email,
            'department' => $request->department,
            'semester' => $request->semester,
            'batch_id' => $request->batch_id,
            'guide_id' => $request->guide_id,
            'role_id' => $studentRole->id,
            'phone' => 'N/A',
            'password' => Hash::make($request->password ?: 'password123'),
        ]);

        if ($request->guide_id) {
            GuideAssignment::create([
                'student_id' => $student->id,
                'guide_id' => $request->guide_id,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'students'])->with('success', 'Student created successfully.');
    }

    public function updateStudent(Request $request, User $user)
    {
        $request->validate([
            'enrollment_number' => 'required|string|max:255|unique:users,enrollment_number,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'department' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:10',
            'batch_id' => 'nullable|exists:batches,id',
            'guide_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !User::find($value)?->hasPermission('guide')) {
                        $fail('The selected guide must have guide authority.');
                    }
                }
            ],
        ]);

        $oldGuideId = $user->guide_id;
        $newGuideId = $request->guide_id;

        $user->update([
            'enrollment_number' => $request->enrollment_number,
            'name' => $request->name,
            'email' => $request->email,
            'department' => $request->department,
            'semester' => $request->semester,
            'batch_id' => $request->batch_id,
            'guide_id' => $newGuideId,
        ]);

        if ($oldGuideId != $newGuideId) {
            // Terminate current assignment if exists
            GuideAssignment::where('student_id', $user->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            // Add new assignment
            if ($newGuideId) {
                GuideAssignment::create([
                    'student_id' => $user->id,
                    'guide_id' => $newGuideId,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.dashboard', ['tab' => 'students'])->with('success', 'Student updated successfully.');
    }

    public function destroyStudent(Request $request, User $user)
    {
        $request->validate([
            'confirmation_text' => 'required|string',
        ]);

        if ($request->confirmation_text !== $user->enrollment_number) {
            return redirect()->route('admin.dashboard', ['tab' => 'students'])
                ->with('error', 'Student deletion aborted: Deletion confirmation text did not match the Enrollment Number.');
        }

        $adminName = auth()->user()->name;
        $targetRecord = "Student Name: {$user->name}, Enrollment: {$user->enrollment_number}, Email: {$user->email}";

        $user->delete();

        // Write audit log entry
        \App\Models\AuditLog::create([
            'admin_name' => $adminName,
            'action' => 'Deleted Student',
            'target' => $targetRecord,
            'timestamp' => now(),
        ]);
        \Illuminate\Support\Facades\Log::info("Admin {$adminName} deleted student: {$targetRecord}");

        return redirect()->route('admin.dashboard', ['tab' => 'students'])->with('success', 'Student deleted successfully.');
    }

    public function destroyFaculty(Request $request, User $user)
    {
        $request->validate([
            'confirmation_text' => 'required|string',
        ]);

        if ($request->confirmation_text !== $user->name) {
            return redirect()->route('admin.dashboard', ['tab' => 'faculty_authority'])
                ->with('error', 'Faculty deletion aborted: Deletion confirmation text did not match the Faculty Name.');
        }

        // Dependency Check
        // 1. Assigned Guides (e.g. guide_id in users table for students)
        $hasStudents = User::where('guide_id', $user->id)->exists();
        // 2. Active Guide Assignments (e.g. guide_id in active GuideAssignment)
        $hasActiveAssignments = GuideAssignment::where('guide_id', $user->id)->whereNull('unassigned_at')->exists();
        // 3. Approval / NOC Rights
        $hasSpecialRights = $user->hasPermission('approval_faculty') || $user->hasPermission('noc_authority');

        if ($hasStudents || $hasActiveAssignments || $hasSpecialRights) {
            return redirect()->route('admin.dashboard', ['tab' => 'faculty_authority'])
                ->with('error', 'This faculty cannot be deleted because active assignments exist. Please reassign responsibilities first.');
        }

        $adminName = auth()->user()->name;
        $targetRecord = "Faculty Name: {$user->name}, Email: {$user->email}, Faculty ID: {$user->faculty_id}";

        $user->delete();

        // Write audit log entry
        \App\Models\AuditLog::create([
            'admin_name' => $adminName,
            'action' => 'Deleted Faculty',
            'target' => $targetRecord,
            'timestamp' => now(),
        ]);
        \Illuminate\Support\Facades\Log::info("Admin {$adminName} deleted faculty: {$targetRecord}");

        return redirect()->route('admin.dashboard', ['tab' => 'faculty_authority'])->with('success', 'Faculty deleted successfully.');
    }

    // ==========================================
    // IMPORTS (XLSX Only)
    // ==========================================

    /**
     * Validate that the uploaded file is a valid XLSX and system can process it.
     * Returns null on success, or a redirect response on failure.
     */
    private function validateXlsxUpload(Request $request, string $redirectTab): ?\Illuminate\Http\RedirectResponse
    {
        // 1. Check ZipArchive availability FIRST (required for XLSX)
        if (!class_exists('ZipArchive')) {
            Log::error('Excel Upload Failed: PHP ZipArchive class is not available. The php_zip extension must be enabled in php.ini for the web server (Apache).');
            return redirect()->route('admin.dashboard', ['tab' => $redirectTab])
                ->with('error', 'Excel processing is currently unavailable because the PHP Zip extension is not enabled. Please contact your server administrator to enable "extension=zip" in the Apache php.ini file, then restart Apache.');
        }

        // 2. Check PhpSpreadsheet availability
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            Log::error('Excel Upload Failed: PhpSpreadsheet library is not installed or autoloaded.');
            return redirect()->route('admin.dashboard', ['tab' => $redirectTab])
                ->with('error', 'Excel processing library (PhpSpreadsheet) is not available. Please run "composer install" to restore dependencies.');
        }

        // 3. Validate file presence, type, and size
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:5120', // 5MB max
            ],
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The upload failed. Please try again.',
            'file.max' => 'The file is too large. Maximum allowed size is 5MB.',
        ]);

        $file = $request->file('file');

        // 4. Validate file extension strictly
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'xlsx') {
            return redirect()->route('admin.dashboard', ['tab' => $redirectTab])
                ->with('error', 'Only Excel (.xlsx) files are supported. You uploaded a .' . $extension . ' file. Please save your spreadsheet as .xlsx format and try again.');
        }

        // 5. Validate MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', // Some systems detect XLSX as zip
            'application/octet-stream', // Fallback for some uploads
        ];
        if (!in_array($mimeType, $allowedMimes)) {
            return redirect()->route('admin.dashboard', ['tab' => $redirectTab])
                ->with('error', 'The uploaded file does not appear to be a valid Excel (.xlsx) file. Detected type: ' . $mimeType);
        }

        // 6. Check file size is not zero
        if ($file->getSize() === 0) {
            return redirect()->route('admin.dashboard', ['tab' => $redirectTab])
                ->with('error', 'The uploaded file is empty (0 bytes). Please check your file and try again.');
        }

        return null; // All checks passed
    }

    /**
     * Load a spreadsheet from an XLSX file.
     * Returns [Spreadsheet, errorResponse]. If errorResponse is not null, return it.
     */
    private function loadSpreadsheet(string $filePath, string $redirectTab): array
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            return [$spreadsheet, null];
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('XLSX Read Error (Reader): ' . $e->getMessage(), ['file' => $filePath]);
            return [null, redirect()->route('admin.dashboard', ['tab' => $redirectTab])
                ->with('error', 'The uploaded file could not be read. It may be corrupted or not a valid Excel file. Error: ' . $e->getMessage())];
        } catch (\Exception $e) {
            Log::error('XLSX Read Error (General): ' . $e->getMessage(), ['file' => $filePath]);
            return [null, redirect()->route('admin.dashboard', ['tab' => $redirectTab])
                ->with('error', 'An unexpected error occurred while processing the Excel file: ' . $e->getMessage())];
        }
    }

    /**
     * Helper: read rows from the active sheet of a spreadsheet (for student import).
     */
    private function readXlsxRows(string $filePath, string $redirectTab): array
    {
        [$spreadsheet, $error] = $this->loadSpreadsheet($filePath, $redirectTab);
        if ($error) return [null, $error];
        $rows = $spreadsheet->getActiveSheet()->toArray();
        return [$rows, null];
    }

    public function importStudents(Request $request)
    {
        // Validate XLSX upload
        $validationError = $this->validateXlsxUpload($request, 'students');
        if ($validationError) {
            return $validationError;
        }

        $file = $request->file('file');
        $path = $file->getRealPath();

        // Read XLSX
        [$rows, $readError] = $this->readXlsxRows($path, 'students');
        if ($readError) {
            return $readError;
        }

        if (count($rows) < 2) {
            return redirect()->route('admin.dashboard', ['tab' => 'students'])
                ->with('error', 'The uploaded file is empty or contains only headers.');
        }

        // Parse headers
        $header = array_map(fn($h) => strtolower(trim($h ?? '')), $rows[0]);

        $enrollIndex = $this->findHeaderIndex($header, ['enrollment number', 'enrollment_number', 'enrollment', 'roll number', 'roll_number']);
        $nameIndex = $this->findHeaderIndex($header, ['name', 'student name', 'student_name']);
        $emailIndex = $this->findHeaderIndex($header, ['email', 'email address', 'email_address']);
        $deptIndex = $this->findHeaderIndex($header, ['department', 'dept']);
        $semIndex = $this->findHeaderIndex($header, ['semester', 'sem']);
        $batchIndex = $this->findHeaderIndex($header, ['batch', 'batch name', 'batch_name']);
        $guideIndex = $this->findHeaderIndex($header, ['assigned guide', 'guide email', 'guide_email', 'guide']);

        if ($nameIndex === false || $emailIndex === false) {
            return redirect()->route('admin.dashboard', ['tab' => 'students'])
                ->with('error', 'Invalid file structure. Make sure "Name" and "Email" columns are present in the first row.');
        }

        $successCount = 0;
        $duplicateCount = 0;
        $errors = [];

        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $defaultPasswordHash = Hash::make('password123');

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $name = trim($row[$nameIndex] ?? '');
            $email = trim($row[$emailIndex] ?? '');
            $enrollment = $enrollIndex !== false ? trim($row[$enrollIndex] ?? '') : null;
            $dept = $deptIndex !== false ? trim($row[$deptIndex] ?? '') : null;
            $sem = $semIndex !== false ? trim($row[$semIndex] ?? '') : null;
            $batchName = $batchIndex !== false ? trim($row[$batchIndex] ?? '') : null;
            $guideVal = $guideIndex !== false ? trim($row[$guideIndex] ?? '') : null;

            if (empty($name) || empty($email)) {
                $errors[] = "Row " . ($i + 1) . " skipped: Name and email are required.";
                continue;
            }

            // Check duplicates
            $emailExists = User::where('email', $email)->exists();
            $enrollExists = $enrollment ? User::where('enrollment_number', $enrollment)->exists() : false;

            if ($emailExists || $enrollExists) {
                $duplicateCount++;
                continue;
            }

            // Resolve Batch
            $batchId = null;
            if (!empty($batchName)) {
                $batch = Batch::firstOrCreate(['name' => $batchName]);
                $batchId = $batch->id;
            }

            // Resolve Guide
            $guideId = null;
            if (!empty($guideVal)) {
                $guide = User::whereHas('role', fn($q) => $q->whereIn('name', ['faculty', 'higher_faculty']))
                    ->whereHas('permissions', fn($q) => $q->where('permission', 'guide'))
                    ->where(function($q) use ($guideVal) {
                        $q->where('email', $guideVal)
                          ->orWhere('faculty_id', $guideVal)
                          ->orWhere('name', $guideVal);
                    })->first();
                if ($guide) {
                    $guideId = $guide->id;
                } else {
                    $errors[] = "Row " . ($i + 1) . ": Guide '{$guideVal}' not found or lacks guide authority.";
                }
            }

            try {
                $student = User::create([
                    'enrollment_number' => $enrollment,
                    'name' => $name,
                    'email' => $email,
                    'department' => $dept,
                    'semester' => $sem ?: 1,
                    'batch_id' => $batchId,
                    'guide_id' => $guideId,
                    'role_id' => $studentRole->id,
                    'phone' => 'N/A',
                    'password' => $defaultPasswordHash,
                ]);

                if ($guideId) {
                    GuideAssignment::create([
                        'student_id' => $student->id,
                        'guide_id' => $guideId,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                    ]);
                }
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($i + 1) . ": Error inserting {$name}: " . $e->getMessage();
            }
        }

        return redirect()->route('admin.dashboard', ['tab' => 'students'])->with('import_report', [
            'type' => 'Student',
            'success' => $successCount,
            'duplicates' => $duplicateCount,
            'errors' => $errors,
        ]);
    }

    private function findHeaderIndex(array $headers, array $possibilities)
    {
        foreach ($possibilities as $p) {
            $index = array_search($p, $headers);
            if ($index !== false) {
                return $index;
            }
        }
        return false;
    }


    /**
     * Update faculty authority type and automatically manage their role.
     */
    public function updateAuthority(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:guide,approval_faculty,noc_authority',
        ]);

        // Resolve roles
        $facultyRole = Role::where('name', 'faculty')->first();
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first();

        if (!$facultyRole || !$higherFacultyRole) {
            return redirect()->back()->with('error', 'Faculty or Higher Faculty role not found in the system.');
        }

        // Sync permissions
        $user->syncPermissions($request->permissions);

        // Update role based on permissions
        if ($user->hasPermission('noc_authority')) {
            $user->update([
                'role_id' => $higherFacultyRole->id,
            ]);
        } else {
            $user->update([
                'role_id' => $facultyRole->id,
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'faculty_authority'])
            ->with('success', "Faculty authority updated successfully to: " . $user->authority_display);
    }

    /**
     * Reassign all students in a batch to a specific guide.
     */
    public function reassignBatchGuide(Request $request, Batch $batch)
    {
        $request->validate([
            'guide_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !User::find($value)?->hasPermission('guide')) {
                        $fail('The selected guide must have guide authority.');
                    }
                }
            ],
        ]);

        $newGuideId = $request->guide_id;

        // Resolve student role ID dynamically
        $studentRole = Role::where('name', 'student')->first();
        if (!$studentRole) {
            return redirect()->back()->with('error', 'Student role not found in the system.');
        }

        $students = User::where('batch_id', $batch->id)
            ->where('role_id', $studentRole->id)
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('admin.dashboard', ['tab' => 'batches'])
                ->with('error', "No students found in batch {$batch->name} to reassign.");
        }

        DB::transaction(function() use ($students, $newGuideId) {
            foreach ($students as $student) {
                $oldGuideId = $student->guide_id;
                if ($oldGuideId != $newGuideId) {
                    $student->update(['guide_id' => $newGuideId]);

                    // Terminate current assignment
                    GuideAssignment::where('student_id', $student->id)
                        ->whereNull('unassigned_at')
                        ->update(['unassigned_at' => now()]);

                    // Create new assignment
                    GuideAssignment::create([
                        'student_id' => $student->id,
                        'guide_id' => $newGuideId,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.dashboard', ['tab' => 'batches'])
            ->with('success', "All students in batch {$batch->name} have been reassigned to the selected guide.");
    }

    // ==========================================
    // SYSTEM DIAGNOSTICS
    // ==========================================

    /**
     * Return system diagnostics data for the admin dashboard.
     */
    public function systemDiagnostics()
    {
        $diagnostics = [
            'zip_extension' => [
                'label' => 'PHP Zip Extension',
                'status' => extension_loaded('zip'),
                'detail' => extension_loaded('zip') ? 'Loaded (v' . phpversion('zip') . ')' : 'Not loaded — enable "extension=zip" in php.ini and restart Apache',
            ],
            'zip_archive' => [
                'label' => 'ZipArchive Class',
                'status' => class_exists('ZipArchive'),
                'detail' => class_exists('ZipArchive') ? 'Available' : 'Missing — requires php_zip extension',
            ],
            'phpspreadsheet' => [
                'label' => 'PhpSpreadsheet Library',
                'status' => class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class),
                'detail' => class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class) ? 'Installed (Composer)' : 'Not found — run "composer require phpoffice/phpspreadsheet"',
            ],
            'xlsx_ready' => [
                'label' => 'XLSX Upload Ready',
                'status' => class_exists('ZipArchive') && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class),
                'detail' => (class_exists('ZipArchive') && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class))
                    ? 'System is ready to process Excel uploads'
                    : 'System cannot process Excel uploads — see issues above',
            ],
            'php_version' => [
                'label' => 'PHP Version',
                'status' => version_compare(PHP_VERSION, '8.1', '>='),
                'detail' => PHP_VERSION,
            ],
            'upload_max_filesize' => [
                'label' => 'Upload Max Filesize',
                'status' => true,
                'detail' => ini_get('upload_max_filesize'),
            ],
            'post_max_size' => [
                'label' => 'POST Max Size',
                'status' => true,
                'detail' => ini_get('post_max_size'),
            ],
            'php_ini_path' => [
                'label' => 'PHP INI Path (Web)',
                'status' => true,
                'detail' => php_ini_loaded_file() ?: 'Unknown',
            ],
        ];

        return response()->json($diagnostics);
    }
}
