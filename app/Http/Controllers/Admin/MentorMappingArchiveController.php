<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorMappingArchive;
use App\Models\ArchivedMentorMapping;
use App\Models\User;
use App\Models\Batch;
use App\Models\GuideAssignment;
use App\Models\GuideHistory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MentorMappingArchiveController extends Controller
{
    /**
     * Display the list of archived imports.
     */
    public function index()
    {
        $archives = MentorMappingArchive::with(['importedBy'])
            ->latest()
            ->get();

        return view('admin.archives.index', compact('archives'));
    }

    /**
     * Display a specific archived import details.
     */
    public function show(MentorMappingArchive $archive)
    {
        $items = $archive->items()
            ->with(['student', 'batch', 'guide'])
            ->get();

        return view('admin.archives.show', compact('archive', 'items'));
    }

    /**
     * Restore an archived mentor mapping snapshot.
     */
    public function restore(Request $request, MentorMappingArchive $archive)
    {
        $request->validate([
            'confirmation_text' => 'required|string',
        ]);

        if ($request->confirmation_text !== 'RESTORE') {
            return redirect()->back()->with('error', 'Restore aborted: You must type the word "RESTORE" exactly to confirm.');
        }

        DB::beginTransaction();

        try {
            // 1. Take a snapshot of the CURRENT active database mapping state so it is archived before we replace it
            $currentFileName = "Snapshot Before Restoring Archive #" . $archive->id;
            MentorMappingArchive::archiveCurrentState(auth()->id(), $currentFileName, "Auto-saved snapshot before rolling back to Archive #{$archive->id} ({$archive->file_name}).");

            // 2. Restore student-guide-batch associations from the archived items
            $archive->items()->chunk(100, function ($items) {
                foreach ($items as $item) {
                    $student = User::find($item->student_id);
                    if ($student) {
                        $oldGuideId = $student->guide_id;
                        $student->update([
                            'batch_id' => $item->batch_id,
                            'guide_id' => $item->guide_id,
                        ]);

                        // Log guide assignment change if guide was altered
                        if ($oldGuideId != $item->guide_id) {
                            GuideAssignment::where('student_id', $student->id)
                                ->whereNull('unassigned_at')
                                ->update(['unassigned_at' => now()]);

                            if ($item->guide_id) {
                                GuideAssignment::create([
                                    'student_id' => $student->id,
                                    'guide_id' => $item->guide_id,
                                    'assigned_by' => auth()->id(),
                                    'assigned_at' => now(),
                                ]);
                            }

                            // Write to guide histories
                            GuideHistory::create([
                                    'student_id' => $student->id,
                                    'old_guide_id' => $oldGuideId,
                                    'new_guide_id' => $item->guide_id,
                                    'changed_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            });

            // 3. Restore batch guide assignments based on restored snapshot
            $batchGuides = $archive->items()
                ->whereNotNull('batch_id')
                ->whereNotNull('guide_id')
                ->select('batch_id', 'guide_id')
                ->distinct()
                ->get();

            foreach ($batchGuides as $bg) {
                $batch = Batch::find($bg->batch_id);
                if ($batch) {
                    $batch->update(['guide_id' => $bg->guide_id]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to restore mentor mapping archive: " . $e->getMessage());
            return redirect()->route('admin.mentor-mapping.archives.show', $archive->id)
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }

        // Audit Log
        $adminName = auth()->user()->name;
        $targetRecord = "Archive ID: {$archive->id}, File Name: {$archive->file_name}, Imported By: {$archive->importedBy?->name}, Date: {$archive->import_date}";
        AuditLog::create([
            'admin_name' => $adminName,
            'action' => 'Restored Mentor Mapping Archive',
            'target' => $targetRecord,
            'timestamp' => now(),
        ]);
        Log::info("Admin {$adminName} restored mentor mapping archive: {$targetRecord}");

        return redirect()->route('admin.dashboard', ['tab' => 'mentor_mapping'])
            ->with('success', "Mentor mappings successfully restored to state of import: {$archive->file_name} ({$archive->import_date->format('M d, Y')}).");
    }

    /**
     * Download CSV report of the archived mapping snapshot.
     */
    public function downloadReport(MentorMappingArchive $archive)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="mentor_mapping_archive_' . $archive->id . '.csv"',
        ];

        $callback = function () use ($archive) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Enrollment Number', 'Student Name', 'Batch', 'Mentor Name', 'Mentor Email']);

            $archive->items()->with(['guide'])->chunk(100, function ($items) use ($file) {
                foreach ($items as $item) {
                    fputcsv($file, [
                        $item->enrollment_number,
                        $item->student_name,
                        $item->batch_name ?? 'N/A',
                        $item->guide_name ?? 'N/A',
                        $item->guide?->email ?? 'N/A',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Compare two archived mentor mapping snapshots.
     */
    public function compare(MentorMappingArchive $archive, MentorMappingArchive $other)
    {
        $itemsA = $archive->items()->get()->keyBy('enrollment_number');
        $itemsB = $other->items()->get()->keyBy('enrollment_number');

        $added = [];
        $removed = [];
        $guideChanges = [];
        $batchChanges = [];

        foreach ($itemsB as $enrollment => $itemB) {
            if (!$itemsA->has($enrollment)) {
                $added[] = [
                    'enrollment' => $enrollment,
                    'name' => $itemB->student_name,
                    'batch' => $itemB->batch_name ?? 'N/A',
                    'guide' => $itemB->guide_name ?? 'N/A',
                ];
            } else {
                $itemA = $itemsA->get($enrollment);
                
                if ($itemA->guide_name !== $itemB->guide_name) {
                    $guideChanges[] = [
                        'enrollment' => $enrollment,
                        'name' => $itemB->student_name,
                        'old_guide' => $itemA->guide_name ?? 'None',
                        'new_guide' => $itemB->guide_name ?? 'None',
                    ];
                }

                if ($itemA->batch_name !== $itemB->batch_name) {
                    $batchChanges[] = [
                        'enrollment' => $enrollment,
                        'name' => $itemB->student_name,
                        'old_batch' => $itemA->batch_name ?? 'None',
                        'new_batch' => $itemB->batch_name ?? 'None',
                    ];
                }
            }
        }

        foreach ($itemsA as $enrollment => $itemA) {
            if (!$itemsB->has($enrollment)) {
                $removed[] = [
                    'enrollment' => $enrollment,
                    'name' => $itemA->student_name,
                    'batch' => $itemA->batch_name ?? 'N/A',
                    'guide' => $itemA->guide_name ?? 'N/A',
                ];
            }
        }

        return view('admin.archives.compare', compact('archive', 'other', 'added', 'removed', 'guideChanges', 'batchChanges'));
    }
}
