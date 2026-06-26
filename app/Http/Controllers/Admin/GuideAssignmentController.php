<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Batch;
use App\Models\GuideAssignment;
use App\Models\GuideHistory;
use App\Models\AuditLog;
use App\Models\InternshipApplication;
use App\Models\Noc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuideAssignmentController extends Controller
{
    /**
     * Guide Assignment Center
     */
    public function index()
    {
        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        $unassignedStudents = User::where('role_id', $studentRole->id)
            ->whereNull('guide_id')
            ->orderBy('enrollment_number')
            ->get();

        $assignedStudents = User::where('role_id', $studentRole->id)
            ->whereNotNull('guide_id')
            ->with(['guide', 'batch'])
            ->orderBy('enrollment_number')
            ->get();

        $guides = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->whereHas('permissions', fn($q) => $q->where('permission', 'guide'))
            ->orderBy('name')
            ->get();

        $batches = Batch::orderBy('name')->get();

        // Stats
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

        $activeTab = 'guide_assignments';

        return view('admin.dashboard', compact(
            'unassignedStudents',
            'assignedStudents',
            'guides',
            'batches',
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
     * Assign student to guide & batch + lock
     */
    public function assign(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'guide_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !User::find($value)?->hasPermission('guide')) {
                        $fail('The selected guide must have guide authority.');
                    }
                }
            ],
            'batch_id' => 'nullable|exists:batches,id',
        ]);

        $student = User::findOrFail($request->student_id);
        $oldGuideId = $student->guide_id;
        $newGuideId = $request->guide_id;

        DB::transaction(function() use ($student, $oldGuideId, $newGuideId, $request) {
            $student->update([
                'guide_id' => $newGuideId,
                'batch_id' => $request->batch_id ?: $student->batch_id,
                'is_locked' => true,
            ]);

            if ($oldGuideId != $newGuideId) {
                // Terminate active assignment
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

                // Log guide history
                GuideHistory::create([
                    'student_id' => $student->id,
                    'old_guide_id' => $oldGuideId,
                    'new_guide_id' => $newGuideId,
                    'changed_by' => auth()->id(),
                ]);
            }
        });

        AuditLog::log(
            "Assigned and locked guide for student: {$student->name}",
            "Student ID: {$student->id}, Guide ID: {$newGuideId}",
            "guide_assignment",
            ['student_id' => $student->id, 'guide_id' => $newGuideId, 'batch_id' => $request->batch_id]
        );

        return redirect()->back()->with('success', 'Student guide assigned and locked successfully.');
    }

    /**
     * Release student (remove guide)
     */
    public function release(User $user)
    {
        $oldGuideId = $user->guide_id;

        DB::transaction(function() use ($user, $oldGuideId) {
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
        });

        AuditLog::log(
            "Released student: {$user->name}",
            "Student ID: {$user->id}, Old Guide ID: {$oldGuideId}",
            "guide_release",
            ['student_id' => $user->id, 'old_guide_id' => $oldGuideId]
        );

        return redirect()->back()->with('success', 'Student guide assignment released successfully.');
    }

    /**
     * Bulk assign guide & batch to multiple students
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'guide_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !User::find($value)?->hasPermission('guide')) {
                        $fail('The selected guide must have guide authority.');
                    }
                }
            ],
            'batch_id' => 'nullable|exists:batches,id',
        ]);

        $studentIds = $request->student_ids;
        $guideId = $request->guide_id;
        $batchId = $request->batch_id;

        DB::transaction(function() use ($studentIds, $guideId, $batchId) {
            foreach ($studentIds as $id) {
                $student = User::find($id);
                if ($student) {
                    $oldGuideId = $student->guide_id;

                    $student->update([
                        'guide_id' => $guideId,
                        'batch_id' => $batchId ?: $student->batch_id,
                        'is_locked' => true,
                    ]);

                    if ($oldGuideId != $guideId) {
                        GuideAssignment::where('student_id', $student->id)
                            ->whereNull('unassigned_at')
                            ->update(['unassigned_at' => now()]);

                        GuideAssignment::create([
                            'student_id' => $student->id,
                            'guide_id' => $guideId,
                            'assigned_by' => auth()->id(),
                            'assigned_at' => now(),
                        ]);

                        GuideHistory::create([
                            'student_id' => $student->id,
                            'old_guide_id' => $oldGuideId,
                            'new_guide_id' => $guideId,
                            'changed_by' => auth()->id(),
                        ]);
                    }
                }
            }

            if ($batchId) {
                $targetBatch = \App\Models\Batch::find($batchId);
                if ($targetBatch) {
                    $studentRole = Role::where('name', 'student')->first();
                    if ($studentRole) {
                        $batchStudents = User::where('batch_id', $targetBatch->id)
                            ->where('role_id', $studentRole->id)
                            ->get();
                        if ($batchStudents->isNotEmpty()) {
                            $guideIds = $batchStudents->pluck('guide_id')->filter()->unique();
                            $allHaveGuide = $batchStudents->every(fn($s) => $s->guide_id !== null);
                            if ($allHaveGuide && $guideIds->count() === 1) {
                                $targetBatch->update(['guide_id' => $guideIds->first()]);
                            }
                        }
                    }
                }
            }
        });

        AuditLog::log(
            "Bulk assigned guide to " . count($studentIds) . " students",
            "Guide ID: {$guideId}",
            "guide_assignment",
            ['student_ids' => $studentIds, 'guide_id' => $guideId, 'batch_id' => $batchId]
        );

        return redirect()->back()->with('success', 'Bulk guide assignment completed successfully.');
    }
}
