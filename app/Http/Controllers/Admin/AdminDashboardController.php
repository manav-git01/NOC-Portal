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
            // Sync/Verify the batch guide assignment dynamically
            $batchStudents = User::where('role_id', $studentRole->id)
                ->where('batch_id', $batch->id)
                ->get();
            if ($batchStudents->isNotEmpty()) {
                $guideIds = $batchStudents->pluck('guide_id')->filter()->unique();
                $allHaveGuide = $batchStudents->every(fn($s) => $s->guide_id !== null);
                if ($allHaveGuide && $guideIds->count() === 1) {
                    $commonGuideId = $guideIds->first();
                    if ($batch->guide_id !== $commonGuideId) {
                        $batch->update(['guide_id' => $commonGuideId]);
                        $batch->load('guide');
                    }
                }
            }

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

        $guides = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->whereHas('permissions', fn($q) => $q->where('permission', 'guide'))
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

        $students = $studentsQuery->orderBy('enrollment_number')->get();

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
            'guides',
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
            'password' => Hash::make($request->password ?: $request->enrollment_number),
            'must_change_password' => $request->filled('password') ? false : true,
            'account_status' => 'active',
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
        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        // 1. Student Directory checks
        $totalStudents = User::where('role_id', $studentRole->id)->count();
        $activeStudents = User::where('role_id', $studentRole->id)->where('account_status', 'active')->count();
        
        // Find students with guide_id set but no active guide assignment row, or vice versa
        $desyncedGuidesCount = DB::table('users as u')
            ->where('u.role_id', $studentRole->id)
            ->where(function($query) {
                $query->whereNotNull('u.guide_id')
                    ->whereNotExists(function($q) {
                        $q->select(DB::raw(1))
                            ->from('guide_assignments as ga')
                            ->whereColumn('ga.student_id', 'u.id')
                            ->whereColumn('ga.guide_id', 'u.guide_id')
                            ->whereNull('ga.unassigned_at');
                    })
                    ->orWhereNull('u.guide_id')
                    ->whereExists(function($q) {
                        $q->select(DB::raw(1))
                            ->from('guide_assignments as ga')
                            ->whereColumn('ga.student_id', 'u.id')
                            ->whereNull('ga.unassigned_at');
                    });
            })
            ->count();

        $studentStatus = $desyncedGuidesCount === 0;
        $studentDetail = "Total: {$totalStudents} (Active: {$activeStudents}). " . ($desyncedGuidesCount === 0 ? "All guide links are synced." : "Warning: {$desyncedGuidesCount} desynced guide assignments detected.");

        // 2. Faculty Directory checks
        $totalFaculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])->count();
        $guidesCount = DB::table('faculty_permissions')->where('permission', 'guide')->distinct('user_id')->count('user_id');
        $nocCount = DB::table('faculty_permissions')->where('permission', 'noc_authority')->distinct('user_id')->count('user_id');
        $approvalCount = DB::table('faculty_permissions')->where('permission', 'approval_faculty')->distinct('user_id')->count('user_id');

        $facultyStatus = $nocCount > 0 && $approvalCount > 0;
        $facultyDetail = "Total: {$totalFaculty} (Guides: {$guidesCount}, NOC Auth: {$nocCount}, Approvals: {$approvalCount}).";
        if (!$facultyStatus) {
            $facultyDetail .= " Alert: Missing NOC Authority or Approval Faculty permissions in the system.";
        }

        // 3. Batch Directory checks
        $totalBatches = Batch::count();
        $emptyBatchesCount = 0;
        if ($totalBatches > 0) {
            $emptyBatchesCount = Batch::leftJoin('users', function($join) use ($studentRole) {
                $join->on('batches.id', '=', 'users.batch_id')
                     ->where('users.role_id', '=', $studentRole->id);
            })
            ->groupBy('batches.id')
            ->having(DB::raw('COUNT(users.id)'), '=', 0)
            ->get()
            ->count();
        }
        $batchStatus = $emptyBatchesCount === 0;
        $batchDetail = "Total Batches: {$totalBatches}. " . ($emptyBatchesCount === 0 ? "All batches are populated." : "Warning: {$emptyBatchesCount} batches have 0 students.");

        // 4. Guide Assignment metrics
        $activeAssignments = DB::table('guide_assignments')->whereNull('unassigned_at')->count();
        $totalHistory = DB::table('guide_histories')->count();
        $guideStatus = true;
        $guideDetail = "Active Assignments: {$activeAssignments}. Historical changes: {$totalHistory}.";

        // 5. Authority Management checks
        $totalPermissions = DB::table('faculty_permissions')->count();
        $authorityStatus = $totalPermissions > 0;
        $authorityDetail = "Total assigned permission nodes: {$totalPermissions}.";

        // 6. Audit Logs checks
        $totalLogs = AuditLog::count();
        $recentLogs = AuditLog::where('created_at', '>=', now()->subDays(7))->count();
        $auditStatus = true;
        $auditDetail = "Total logged events: {$totalLogs}. Events in last 7 days: {$recentLogs}.";

        // 7. Applications & NOC checks
        $totalApps = InternshipApplication::count();
        $pendingApps = InternshipApplication::whereIn('status', ['pending', 'pending_higher'])->count();
        $approvedApps = InternshipApplication::whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'noc_generated'])->count();
        $totalNocs = Noc::count();
        $appStatus = true;
        $appDetail = "Applications: {$totalApps} (Pending: {$pendingApps}, Approved: {$approvedApps}). Generated NOCs: {$totalNocs}.";

        // Environment diagnostic checks (Original checks preserved)
        $environment = [
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

        // Combine into structure
        $modules = [
            'student_directory' => [
                'label' => 'Student Directory Module',
                'status' => $studentStatus,
                'detail' => $studentDetail,
                'icon' => 'fa-user-graduate',
            ],
            'faculty_directory' => [
                'label' => 'Faculty Directory Module',
                'status' => $facultyStatus,
                'detail' => $facultyDetail,
                'icon' => 'fa-chalkboard-teacher',
            ],
            'batch_directory' => [
                'label' => 'Batch Directory Module',
                'status' => $batchStatus,
                'detail' => $batchDetail,
                'icon' => 'fa-layer-group',
            ],
            'guide_assignments' => [
                'label' => 'Guide Assignment Center',
                'status' => $guideStatus,
                'detail' => $guideDetail,
                'icon' => 'fa-user-check',
            ],
            'authority_management' => [
                'label' => 'Authority Management Module',
                'status' => $authorityStatus,
                'detail' => $authorityDetail,
                'icon' => 'fa-users-cog',
            ],
            'internship_noc' => [
                'label' => 'Internship & NOC Applications',
                'status' => $appStatus,
                'detail' => $appDetail,
                'icon' => 'fa-file-invoice',
            ],
            'audit_logs' => [
                'label' => 'Audit Logging Registry',
                'status' => $auditStatus,
                'detail' => $auditDetail,
                'icon' => 'fa-history',
            ],
        ];

        // Score calculation
        $totalChecks = count($environment) + count($modules);
        $passedChecks = 0;
        foreach ($environment as $c) {
            if ($c['status']) $passedChecks++;
        }
        foreach ($modules as $c) {
            if ($c['status']) $passedChecks++;
        }
        $score = round(($passedChecks / $totalChecks) * 100);

        return response()->json([
            'score' => $score,
            'environment' => $environment,
            'modules' => $modules,
            'system_info' => [
                'laravel_version' => app()->version(),
                'db_connection' => config('database.default'),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            ]
        ]);
    }
}
