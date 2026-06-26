<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\User;
use App\Models\Role;
use App\Models\GuideAssignment;
use App\Models\GuideHistory;
use App\Models\AuditLog;
use App\Models\Noc;
use App\Models\InternshipApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchDirectoryController extends Controller
{
    /**
     * Display the detailed batch page with statistics and student list.
     */
    public function show(Batch $batch)
    {
        $studentRole = Role::where('name', 'student')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first();

        // 1. Load students in this batch
        $students = User::where('batch_id', $batch->id)
            ->where('role_id', $studentRole->id)
            ->with(['guide', 'internshipApplications.noc'])
            ->orderBy('enrollment_number')
            ->get();

        // Sync/Verify the batch guide assignment dynamically
        if ($students->isNotEmpty()) {
            $guideIds = $students->pluck('guide_id')->filter()->unique();
            $allHaveGuide = $students->every(fn($s) => $s->guide_id !== null);
            if ($allHaveGuide && $guideIds->count() === 1) {
                $commonGuideId = $guideIds->first();
                if ($batch->guide_id !== $commonGuideId) {
                    $batch->update(['guide_id' => $commonGuideId]);
                    $batch->load('guide');
                }
            }
        }

        $totalStudents = $students->count();

        // 2. Load internship statistics
        $studentIds = $students->pluck('id');

        $applicationsCount = InternshipApplication::whereIn('user_id', $studentIds)->count();
        
        $approvedCount = InternshipApplication::whereIn('user_id', $studentIds)
            ->whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'noc_generated'])
            ->count();

        $nocGeneratedCount = Noc::whereIn('application_id', 
            InternshipApplication::whereIn('user_id', $studentIds)->pluck('id')
        )->count();

        // 3. Load all active guides and batches for reassignments
        $faculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->whereHas('permissions', fn($q) => $q->where('permission', 'guide'))
            ->orderBy('name')->get();
        $batches = Batch::orderBy('name')->get();

        return view('admin.batches.show', compact(
            'batch',
            'students',
            'totalStudents',
            'applicationsCount',
            'approvedCount',
            'nocGeneratedCount',
            'faculty',
            'batches'
        ));
    }

    /**
     * Move an individual student to another batch.
     */
    public function updateStudentBatch(Request $request, User $student)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
        ]);

        $oldBatchName = $student->batch?->name ?? 'N/A';
        $newBatch = Batch::findOrFail($request->batch_id);

        DB::transaction(function () use ($student, $newBatch) {
            $student->update([
                'batch_id' => $newBatch->id,
            ]);

            // If the new batch has a guide assigned and the student doesn't have a guide (or we want to update it)
            if ($newBatch->guide_id && $student->guide_id !== $newBatch->guide_id) {
                $oldGuideId = $student->guide_id;
                $student->update(['guide_id' => $newBatch->guide_id]);

                // Terminate current guide assignment
                GuideAssignment::where('student_id', $student->id)
                    ->whereNull('unassigned_at')
                    ->update(['unassigned_at' => now()]);

                // Create new guide assignment
                GuideAssignment::create([
                    'student_id' => $student->id,
                    'guide_id' => $newBatch->guide_id,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);

                // Log guide history
                GuideHistory::create([
                    'student_id' => $student->id,
                    'old_guide_id' => $oldGuideId,
                    'new_guide_id' => $newBatch->guide_id,
                    'changed_by' => auth()->id(),
                ]);
            }

            // Post-transfer: if all students in this batch have the same guide, assign it as the batch's default guide
            $studentRole = Role::where('name', 'student')->first();
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
        });

        // Audit Log
        $adminName = auth()->user()->name;
        $targetRecord = "Student: {$student->name} ({$student->enrollment_number}), Moved from Batch: {$oldBatchName} to {$newBatch->name}";
        AuditLog::create([
            'admin_name' => $adminName,
            'action' => 'Changed Student Batch',
            'target' => $targetRecord,
            'timestamp' => now(),
        ]);
        Log::info("Admin {$adminName} updated student batch: {$targetRecord}");

        return redirect()->back()->with('success', "Student {$student->name} moved to batch {$newBatch->name} successfully.");
    }

    /**
     * Change guide for a single student.
     */
    public function updateStudentGuide(Request $request, User $student)
    {
        $request->validate([
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

        $oldGuideId = $student->guide_id;
        $newGuideId = $request->guide_id ?: null;

        if ($oldGuideId == $newGuideId) {
            return redirect()->back()->with('info', 'No guide changes were made.');
        }

        $oldGuideName = $student->guide?->name ?? 'N/A';
        $newGuideName = 'N/A';

        DB::transaction(function () use ($student, $oldGuideId, $newGuideId) {
            $student->update([
                'guide_id' => $newGuideId,
            ]);

            // Terminate current active assignment
            GuideAssignment::where('student_id', $student->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            if ($newGuideId) {
                // Create new active assignment
                GuideAssignment::create([
                    'student_id' => $student->id,
                    'guide_id' => $newGuideId,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);
            }

            // Log guide history
            GuideHistory::create([
                'student_id' => $student->id,
                'old_guide_id' => $oldGuideId,
                'new_guide_id' => $newGuideId,
                'changed_by' => auth()->id(),
            ]);
        });

        if ($newGuideId) {
            $newGuideName = User::find($newGuideId)->name;
        }

        // Audit Log
        $adminName = auth()->user()->name;
        $targetRecord = "Student: {$student->name} ({$student->enrollment_number}), Guide Changed from: {$oldGuideName} to {$newGuideName}";
        AuditLog::create([
            'admin_name' => $adminName,
            'action' => 'Changed Student Guide',
            'target' => $targetRecord,
            'timestamp' => now(),
        ]);
        Log::info("Admin {$adminName} updated student guide: {$targetRecord}");

        return redirect()->back()->with('success', "Guide for student {$student->name} updated successfully.");
    }

    /**
     * Change guide for an entire batch.
     */
    public function updateBatchGuide(Request $request, Batch $batch)
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
        $newGuide = User::findOrFail($newGuideId);
        
        $studentRole = Role::where('name', 'student')->first();
        if (!$studentRole) {
            return redirect()->back()->with('error', 'Student role not found in the system.');
        }

        $students = User::where('batch_id', $batch->id)
            ->where('role_id', $studentRole->id)
            ->get();

        $oldGuideName = $batch->guide?->name ?? 'Multiple/None';

        DB::transaction(function () use ($batch, $students, $newGuideId) {
            // Update Batch guide
            $batch->update([
                'guide_id' => $newGuideId,
            ]);

            // Update all students in the batch
            foreach ($students as $student) {
                $oldGuideId = $student->guide_id;
                if ($oldGuideId != $newGuideId) {
                    $student->update(['guide_id' => $newGuideId]);

                    // Terminate active guide assignment
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

                    // Log history
                    GuideHistory::create([
                        'student_id' => $student->id,
                        'old_guide_id' => $oldGuideId,
                        'new_guide_id' => $newGuideId,
                        'changed_by' => auth()->id(),
                    ]);
                }
            }
        });

        // Audit Log
        $adminName = auth()->user()->name;
        $targetRecord = "Batch: {$batch->name}, Guide Changed from: {$oldGuideName} to {$newGuide->name}, Affected Students: " . $students->count();
        AuditLog::create([
            'admin_name' => $adminName,
            'action' => 'Changed Batch Guide',
            'target' => $targetRecord,
            'timestamp' => now(),
        ]);
        Log::info("Admin {$adminName} updated batch guide: {$targetRecord}");

        return redirect()->back()->with('success', "Batch {$batch->name} guide updated to {$newGuide->name} successfully. {$students->count()} students updated.");
    }

    /**
     * Bulk transfer multiple students to another batch.
     */
    public function bulkTransfer(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'batch_id' => 'required|exists:batches,id',
        ]);

        $studentIds = $request->student_ids;
        $batchId = $request->batch_id;
        $newBatch = Batch::findOrFail($batchId);

        DB::transaction(function() use ($studentIds, $newBatch) {
            foreach ($studentIds as $id) {
                $student = User::find($id);
                if ($student) {
                    $student->update([
                        'batch_id' => $newBatch->id,
                    ]);

                    // Assign new batch's guide if exists and student has no guide
                    if ($newBatch->guide_id && $student->guide_id !== $newBatch->guide_id) {
                        $oldGuideId = $student->guide_id;
                        $student->update(['guide_id' => $newBatch->guide_id]);

                        GuideAssignment::where('student_id', $student->id)
                            ->whereNull('unassigned_at')
                            ->update(['unassigned_at' => now()]);

                        GuideAssignment::create([
                            'student_id' => $student->id,
                            'guide_id' => $newBatch->guide_id,
                            'assigned_by' => auth()->id(),
                            'assigned_at' => now(),
                        ]);

                        GuideHistory::create([
                            'student_id' => $student->id,
                            'old_guide_id' => $oldGuideId,
                            'new_guide_id' => $newBatch->guide_id,
                            'changed_by' => auth()->id(),
                        ]);
                    }
                }
            }

            // Post-transfer: if all students in this batch have the same guide, assign it as the batch's default guide
            $studentRole = Role::where('name', 'student')->first();
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
        });

        // Audit Log using log helper
        AuditLog::log(
            "Bulk transferred " . count($studentIds) . " students to batch: {$newBatch->name}",
            "Batch ID: {$newBatch->id}",
            "batch_change",
            ['student_ids' => $studentIds, 'batch_id' => $newBatch->id]
        );

        return redirect()->back()->with('success', 'Students transferred successfully to batch: ' . $newBatch->name);
    }
}
