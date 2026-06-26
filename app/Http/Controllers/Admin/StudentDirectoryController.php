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

        $students = $studentsQuery->orderBy('enrollment_number')->get();

        // Get filter choices
        $batches = Batch::orderBy('name')->get();
        $guides = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->whereHas('permissions', fn($q) => $q->where('permission', 'guide'))
            ->orderBy('name')->get();

        $faculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->with(['permissions'])
            ->withCount('students')
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
            'guides',
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
        $existingSoftDeleted = User::onlyTrashed()
            ->where(function($q) use ($request) {
                if ($request->filled('enrollment_number')) {
                    $q->where('enrollment_number', $request->enrollment_number);
                }
                if ($request->filled('email')) {
                    $q->orWhere('email', $request->email);
                }
            })->first();

        if ($existingSoftDeleted) {
            $request->validate([
                'enrollment_number' => 'required|string|max:255|unique:users,enrollment_number,' . $existingSoftDeleted->id,
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $existingSoftDeleted->id,
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

            $existingSoftDeleted->restore();
            $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];

            $existingSoftDeleted->update([
                'enrollment_number' => $request->enrollment_number,
                'name' => $request->name,
                'email' => $request->email,
                'department' => $request->department,
                'semester' => $request->semester,
                'batch_id' => $request->batch_id,
                'guide_id' => $request->guide_id,
                'role_id' => $studentRole->id,
                'phone' => 'N/A',
                'account_status' => 'active',
                'is_locked' => $request->guide_id ? true : false,
                'password' => Hash::make($request->enrollment_number),
                'must_change_password' => true,
            ]);

            $student = $existingSoftDeleted;
        } else {
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
            ]);

            $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];

            // Create student with password set to enrollment number and account_status = 'active'
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
                'password' => Hash::make($request->enrollment_number),
                'account_status' => 'active',
                'must_change_password' => true,
                'is_locked' => $request->guide_id ? true : false,
            ]);
        }

        if ($request->guide_id) {
            // Remove previous guide assignments for safety
            GuideAssignment::where('student_id', $student->id)->delete();
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
     * Assign guide to student + lock
     */
    public function assignGuide(Request $request, User $user)
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

        if ($newBatchId) {
            $newBatch = \App\Models\Batch::find($newBatchId);
            if ($newBatch) {
                // If the batch has a guide, and student doesn't have a guide (or we want to update it to batch's guide)
                if ($newBatch->guide_id && $user->guide_id !== $newBatch->guide_id) {
                    $oldGuideId = $user->guide_id;
                    $user->update(['guide_id' => $newBatch->guide_id]);

                    \App\Models\GuideAssignment::where('student_id', $user->id)
                        ->whereNull('unassigned_at')
                        ->update(['unassigned_at' => now()]);

                    \App\Models\GuideAssignment::create([
                        'student_id' => $user->id,
                        'guide_id' => $newBatch->guide_id,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                    ]);

                    \App\Models\GuideHistory::create([
                        'student_id' => $user->id,
                        'old_guide_id' => $oldGuideId,
                        'new_guide_id' => $newBatch->guide_id,
                        'changed_by' => auth()->id(),
                    ]);
                }

                $studentRole = \App\Models\Role::where('name', 'student')->first();
                if ($studentRole) {
                    $batchStudents = User::where('batch_id', $newBatch->id)
                        ->where('role_id', $studentRole->id)
                        ->get();
                    if ($batchStudents->isNotEmpty()) {
                        $guideIds = $batchStudents->pluck('guide_id')->filter()->unique();
                        $allHaveGuide = $batchStudents->every(fn($s) => $s->guide_id !== null);
                        if ($allHaveGuide && $guideIds->count() === 1) {
                            $newBatch->update(['guide_id' => $guideIds->first()]);
                        }
                    }
                }
            }
        }

        AuditLog::log(
            "Moved student to different batch: {$user->name}",
            "Student ID: {$user->id}, Old Batch: {$oldBatchId}, New Batch: {$newBatchId}",
            "batch_change",
            ['student_id' => $user->id, 'old_batch_id' => $oldBatchId, 'new_batch_id' => $newBatchId]
        );

        return redirect()->back()->with('success', 'Student batch moved successfully.');
    }

    /**
     * Deactivate student (toggle account status)
     */
    public function deactivate(Request $request, User $user)
    {
        $oldStatus = $user->account_status;
        $newStatus = $oldStatus === 'active' ? 'inactive' : 'active';

        $user->update([
            'account_status' => $newStatus,
        ]);

        AuditLog::log(
            "Toggled student account status: {$user->name} ({$oldStatus} -> {$newStatus})",
            "Student ID: {$user->id}",
            "student_deactivate",
            ['student_id' => $user->id, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );

        $msg = $newStatus === 'active' ? 'Student account activated successfully.' : 'Student account deactivated successfully.';
        return redirect()->back()->with('success', $msg);
    }




}
