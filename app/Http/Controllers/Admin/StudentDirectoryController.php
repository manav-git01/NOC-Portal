<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Batch;
use App\Models\Role;
use App\Models\GuideAssignment;
use App\Models\GuideHistory;
use App\Models\AuditLog;
use App\Models\InternshipApplication;
use App\Models\Noc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentDirectoryController extends Controller
{
    /**
     * Student listing with search + filters
     */
    public function index(Request $request)
    {
        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        $studentsQuery = User::where('role_id', $studentRole->id)
            ->with(['batch', 'guide', 'guideAssignments.guide', 'internshipApplications']);

        // Search filter
        if ($request->filled('student_search')) {
            $search = $request->student_search;
            $studentsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('enrollment_number', 'like', "%$search%");
            });
        }

        // Batch filter
        if ($request->filled('batch_id')) {
            $studentsQuery->where('batch_id', $request->batch_id);
        }

        // Guide filter
        if ($request->filled('guide_id')) {
            $studentsQuery->where('guide_id', $request->guide_id);
        }

        // Semester filter
        if ($request->filled('semester')) {
            $studentsQuery->where('semester', $request->semester);
        }

        // Assignment status filter
        if ($request->filled('assignment_status')) {
            $status = $request->assignment_status;
            if ($status === 'assigned') {
                $studentsQuery->whereNotNull('guide_id');
            } elseif ($status === 'unassigned') {
                $studentsQuery->whereNull('guide_id');
            }
        }

        $students = $studentsQuery->orderBy('name')->get();

        // Get filter choices
        $batches = Batch::orderBy('name')->get();
        $faculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->orderBy('name')->get();

        $departments = User::where('role_id', $studentRole->id)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        $semesters = User::where('role_id', $studentRole->id)
            ->whereNotNull('semester')
            ->distinct()
            ->pluck('semester');

        // Main statistics for sidecards/context
        $totalStudents = User::where('role_id', $studentRole->id)->count();
        $totalFaculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])->count();
        $totalGuides = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->whereIn('id', User::where('role_id', $studentRole->id)->whereNotNull('guide_id')->pluck('guide_id'))
            ->count();
        $totalBatches = Batch::count();
        $totalApplications = InternshipApplication::count();
        $pendingApplications = InternshipApplication::whereIn('status', ['pending', 'pending_higher'])->count();
        $approvedApplications = InternshipApplication::whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'noc_generated'])->count();
        $generatedNocs = Noc::count();

        $activeTab = 'student_directory';

        return view('admin.dashboard', compact(
            'students',
            'batches',
            'faculty',
            'departments',
            'semesters',
            'totalStudents',
            'totalFaculty',
            'totalGuides',
            'totalBatches',
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'generatedNocs',
            'activeTab'
        ));
    }

    /**
     * Add student manually (directory only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'enrollment_number' => 'required|string|max:255|unique:users,enrollment_number',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'department' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:10',
            'batch_id' => 'nullable|exists:batches,id',
            'guide_id' => 'nullable|exists:users,id',
        ]);

        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];

        // Create student with a long random password as a dummy, and set account_status = 'inactive'
        // Student will register themselves later
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
            'password' => Hash::make(\Illuminate\Support\Str::random(16)),
            'account_status' => 'inactive', // directory record only
            'is_locked' => $request->guide_id ? true : false,
        ]);

        if ($request->guide_id) {
            GuideAssignment::create([
                'student_id' => $student->id,
                'guide_id' => $request->guide_id,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);
        }

        AuditLog::log(
            "Created directory student record: {$student->name} ({$student->enrollment_number})",
            "Student ID: {$student->id}",
            "student_create",
            ['student' => $student->only(['id', 'name', 'enrollment_number', 'email', 'department', 'semester', 'batch_id', 'guide_id'])]
        );

        return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])->with('success', 'Student record added to directory successfully.');
    }

    /**
     * Edit student
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'enrollment_number' => 'required|string|max:255|unique:users,enrollment_number,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'department' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:10',
            'batch_id' => 'nullable|exists:batches,id',
            'guide_id' => 'nullable|exists:users,id',
        ]);

        $oldGuideId = $user->guide_id;
        $newGuideId = $request->guide_id ?: null;

        $user->update([
            'enrollment_number' => $request->enrollment_number,
            'name' => $request->name,
            'email' => $request->email,
            'department' => $request->department,
            'semester' => $request->semester,
            'batch_id' => $request->batch_id,
            'guide_id' => $newGuideId,
            'is_locked' => $newGuideId ? true : $user->is_locked,
        ]);

        if ($oldGuideId != $newGuideId) {
            GuideAssignment::where('student_id', $user->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            if ($newGuideId) {
                GuideAssignment::create([
                    'student_id' => $user->id,
                    'guide_id' => $newGuideId,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);
            }

            GuideHistory::create([
                'student_id' => $user->id,
                'old_guide_id' => $oldGuideId,
                'new_guide_id' => $newGuideId,
                'changed_by' => auth()->id(),
            ]);
        }

        AuditLog::log(
            "Updated student record: {$user->name} ({$user->enrollment_number})",
            "Student ID: {$user->id}",
            "student_update",
            [
                'old_guide_id' => $oldGuideId,
                'new_guide_id' => $newGuideId,
                'student' => $user->only(['id', 'name', 'enrollment_number', 'email', 'department', 'semester', 'batch_id', 'guide_id'])
            ]
        );

        return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])->with('success', 'Student record updated successfully.');
    }

    /**
     * Delete student (GitHub style confirmation)
     */
    public function destroy(Request $request, User $user)
    {
        $request->validate([
            'confirmation_text' => 'required|string',
        ]);

        if ($request->confirmation_text !== $user->enrollment_number) {
            return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])
                ->with('error', 'Student deletion aborted: Deletion confirmation text did not match the Enrollment Number.');
        }

        $enrollment = $user->enrollment_number;
        $name = $user->name;

        // Clean up assignments
        GuideAssignment::where('student_id', $user->id)->delete();
        GuideHistory::where('student_id', $user->id)->delete();
        
        $user->delete();

        AuditLog::log(
            "Deleted student directory record: {$name} ({$enrollment})",
            "Enrollment: {$enrollment}",
            "student_delete",
            ['name' => $name, 'enrollment_number' => $enrollment]
        );

        return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])->with('success', 'Student record deleted successfully.');
    }

    /**
     * Import student master list from XLSX
     */
    public function import(Request $request)
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])
                ->with('error', 'Excel processing library (PhpSpreadsheet) is not available.');
        }

        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
        } catch (\Exception $e) {
            return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])
                ->with('error', 'Error reading Excel file: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])
                ->with('error', 'The uploaded file is empty.');
        }

        $header = array_map(fn($h) => strtolower(trim($h ?? '')), $rows[0]);

        $enrollIndex = $this->findHeaderIndex($header, ['enrollment number', 'enrollment_number', 'enrollment', 'roll number', 'roll_number']);
        $nameIndex = $this->findHeaderIndex($header, ['name', 'student name', 'student_name']);
        $emailIndex = $this->findHeaderIndex($header, ['email', 'email address', 'email_address']);
        $deptIndex = $this->findHeaderIndex($header, ['department', 'dept']);
        $semIndex = $this->findHeaderIndex($header, ['semester', 'sem']);
        $batchIndex = $this->findHeaderIndex($header, ['batch', 'batch name', 'batch_name']);
        $guideIndex = $this->findHeaderIndex($header, ['assigned guide', 'guide email', 'guide_email', 'guide']);

        if ($nameIndex === false || $emailIndex === false) {
            return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])
                ->with('error', 'Invalid structure. "Name" and "Email" columns are required in row 1.');
        }

        $successCount = 0;
        $duplicateCount = 0;
        $errors = [];

        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $name = trim($row[$nameIndex] ?? '');
                $email = trim($row[$emailIndex] ?? '');
                $enrollment = $enrollIndex !== false ? trim($row[$enrollIndex] ?? '') : null;
                $dept = $deptIndex !== false ? trim($row[$deptIndex] ?? '') : 'IT';
                $sem = $semIndex !== false ? trim($row[$semIndex] ?? '') : '7';
                $batchName = $batchIndex !== false ? trim($row[$batchIndex] ?? '') : null;
                $guideVal = $guideIndex !== false ? trim($row[$guideIndex] ?? '') : null;

                if (empty($name) || empty($email)) {
                    $errors[] = "Row " . ($i + 1) . " skipped: Name and email are required.";
                    continue;
                }

                $existing = User::where('email', $email)
                    ->orWhere(function($q) use ($enrollment) {
                        if ($enrollment) $q->where('enrollment_number', $enrollment);
                    })->first();

                if ($existing) {
                    $duplicateCount++;
                    continue;
                }

                $batchId = null;
                if (!empty($batchName)) {
                    $batch = Batch::firstOrCreate(['name' => $batchName]);
                    $batchId = $batch->id;
                }

                $guideId = null;
                if (!empty($guideVal)) {
                    $guide = User::whereHas('role', fn($q) => $q->whereIn('name', ['faculty', 'higher_faculty']))
                        ->where(function($q) use ($guideVal) {
                            $q->where('email', $guideVal)
                              ->orWhere('faculty_id', $guideVal)
                              ->orWhere('name', $guideVal);
                        })->first();
                    if ($guide) {
                        $guideId = $guide->id;
                    }
                }

                $student = User::create([
                    'name' => $name,
                    'email' => $email,
                    'enrollment_number' => $enrollment,
                    'department' => $dept,
                    'semester' => $sem,
                    'batch_id' => $batchId,
                    'guide_id' => $guideId,
                    'role_id' => $studentRole->id,
                    'phone' => 'N/A',
                    'password' => Hash::make(\Illuminate\Support\Str::random(16)),
                    'account_status' => 'inactive', // directory record
                    'is_locked' => $guideId ? true : false,
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
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

        $report = [
            'type' => 'Student Directory',
            'success' => $successCount,
            'duplicates' => $duplicateCount,
            'errors' => $errors,
        ];

        AuditLog::log(
            "Imported {$successCount} student directory records from file",
            "Records imported: {$successCount}",
            "student_import",
            ['success' => $successCount, 'duplicates' => $duplicateCount, 'errors_count' => count($errors)]
        );

        return redirect()->route('admin.student-directory.index', ['tab' => 'student_directory'])
            ->with('success', 'Import processed successfully.')
            ->with('import_report', $report);
    }

    /**
     * Assign guide to student + lock
     */
    public function assignGuide(Request $request, User $user)
    {
        $request->validate([
            'guide_id' => 'required|exists:users,id',
        ]);

        $oldGuideId = $user->guide_id;
        $newGuideId = $request->guide_id;

        $user->update([
            'guide_id' => $newGuideId,
            'is_locked' => true,
        ]);

        if ($oldGuideId != $newGuideId) {
            GuideAssignment::where('student_id', $user->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            GuideAssignment::create([
                'student_id' => $user->id,
                'guide_id' => $newGuideId,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);

            GuideHistory::create([
                'student_id' => $user->id,
                'old_guide_id' => $oldGuideId,
                'new_guide_id' => $newGuideId,
                'changed_by' => auth()->id(),
            ]);
        }

        AuditLog::log(
            "Assigned guide to student: {$user->name}",
            "Student ID: {$user->id}, Guide ID: {$newGuideId}",
            "guide_assignment",
            ['student_id' => $user->id, 'guide_id' => $newGuideId]
        );

        return redirect()->back()->with('success', 'Guide assigned and locked successfully.');
    }

    /**
     * Remove guide (release) + unlock
     */
    public function removeGuide(Request $request, User $user)
    {
        $oldGuideId = $user->guide_id;

        $user->update([
            'guide_id' => null,
            'is_locked' => false,
        ]);

        GuideAssignment::where('student_id', $user->id)
            ->whereNull('unassigned_at')
            ->update(['unassigned_at' => now()]);

        GuideHistory::create([
            'student_id' => $user->id,
            'old_guide_id' => $oldGuideId,
            'new_guide_id' => null,
            'changed_by' => auth()->id(),
        ]);

        AuditLog::log(
            "Released student guide: {$user->name}",
            "Student ID: {$user->id}",
            "guide_release",
            ['student_id' => $user->id, 'old_guide_id' => $oldGuideId]
        );

        return redirect()->back()->with('success', 'Guide released and assignment unlocked successfully.');
    }

    /**
     * Move student to different batch
     */
    public function moveBatch(Request $request, User $user)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
        ]);

        $oldBatchId = $user->batch_id;
        $newBatchId = $request->batch_id ?: null;

        $user->update([
            'batch_id' => $newBatchId,
        ]);

        AuditLog::log(
            "Moved student to different batch: {$user->name}",
            "Student ID: {$user->id}, Old Batch: {$oldBatchId}, New Batch: {$newBatchId}",
            "batch_change",
            ['student_id' => $user->id, 'old_batch_id' => $oldBatchId, 'new_batch_id' => $newBatchId]
        );

        return redirect()->back()->with('success', 'Student batch moved successfully.');
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
}
