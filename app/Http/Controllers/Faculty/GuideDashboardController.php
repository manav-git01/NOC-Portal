<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Batch;
use App\Models\InternshipApplication;

class GuideDashboardController extends Controller
{
    /**
     * Display the Guide Dashboard landing page.
     * Shows overview stats and batch cards.
     */
    public function index(Request $request)
    {
        $guide = auth()->user();

        // All students assigned to this guide
        $allStudents = User::where('guide_id', $guide->id);
        $totalStudents = (clone $allStudents)->count();

        // Find all batches that have students assigned to this guide
        $batchIds = User::where('guide_id', $guide->id)
            ->whereNotNull('batch_id')
            ->distinct('batch_id')
            ->pluck('batch_id');

        $batches = Batch::whereIn('id', $batchIds)->orderBy('name')->get();
        $totalBatches = $batches->count();

        // Compute per-batch student counts
        $batchStudentCounts = User::where('guide_id', $guide->id)
            ->whereNotNull('batch_id')
            ->selectRaw('batch_id, count(*) as count')
            ->groupBy('batch_id')
            ->pluck('count', 'batch_id');

        // Application stats across ALL assigned students
        $studentIds = User::where('guide_id', $guide->id)->pluck('id');

        $totalApplications = InternshipApplication::whereIn('user_id', $studentIds)->count();

        $pendingApplications = InternshipApplication::whereIn('user_id', $studentIds)
            ->whereIn('status', ['pending', 'pending_higher'])
            ->count();

        $approvedApplications = InternshipApplication::whereIn('user_id', $studentIds)
            ->whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'noc_generated'])
            ->count();

        $nocGenerated = InternshipApplication::whereIn('user_id', $studentIds)
            ->whereHas('noc')
            ->count();

        return view('faculty.guide-dashboard', compact(
            'totalStudents',
            'totalBatches',
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'nocGenerated',
            'batches',
            'batchStudentCounts'
        ));
    }

    /**
     * Display students for a specific batch.
     * Full student directory with search & filters.
     */
    public function showBatch(Request $request, Batch $batch)
    {
        $guide = auth()->user();

        // Verify this guide has students in this batch
        $hasStudents = User::where('guide_id', $guide->id)
            ->where('batch_id', $batch->id)
            ->exists();

        if (!$hasStudents) {
            abort(403, 'You do not have students assigned in this batch.');
        }

        // Base query: students assigned to this guide in this batch
        $baseQuery = User::where('guide_id', $guide->id)
            ->where('batch_id', $batch->id);

        $totalStudents = (clone $baseQuery)->count();

        // Build filtered query
        $query = (clone $baseQuery)
            ->with(['internshipApplications' => function ($q) {
                $q->latest()->with(['approvals.approver', 'noc']);
            }]);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('enrollment_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Application status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');

            if ($status === 'not_applied') {
                $query->whereDoesntHave('internshipApplications');
            } else {
                $query->whereHas('internshipApplications', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }
        }

        // NOC status filter
        if ($request->filled('noc_status') && $request->input('noc_status') !== 'all') {
            $nocStatus = $request->input('noc_status');

            switch ($nocStatus) {
                case 'not_requested':
                    $query->where(function ($q) {
                        $q->whereDoesntHave('internshipApplications')
                          ->orWhereHas('internshipApplications', function ($subQ) {
                              $subQ->where('noc_requested', false)->orWhereNull('noc_requested');
                          });
                    });
                    break;
                case 'requested':
                    $query->whereHas('internshipApplications', function ($q) {
                        $q->where('noc_requested', true)
                          ->whereDoesntHave('noc');
                    });
                    break;
                case 'generated':
                    $query->whereHas('internshipApplications', function ($q) {
                        $q->whereHas('noc');
                    });
                    break;
            }
        }

        $students = $query->orderBy('enrollment_number')->get();

        // Application stats for this batch
        $batchStudentIds = User::where('guide_id', $guide->id)
            ->where('batch_id', $batch->id)
            ->pluck('id');

        $batchApplications = InternshipApplication::whereIn('user_id', $batchStudentIds)->count();
        $batchApproved = InternshipApplication::whereIn('user_id', $batchStudentIds)
            ->whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'noc_generated'])
            ->count();
        $batchPending = InternshipApplication::whereIn('user_id', $batchStudentIds)
            ->whereIn('status', ['pending', 'pending_higher'])
            ->count();

        return view('faculty.guide-batch-students', compact(
            'batch',
            'students',
            'totalStudents',
            'batchApplications',
            'batchApproved',
            'batchPending'
        ));
    }

    /**
     * Display a read-only application details page for the guide.
     * Guides can view but NOT approve/reject/modify.
     */
    public function showApplication(InternshipApplication $application)
    {
        $guide = auth()->user();

        // Ensure this application belongs to a student assigned to this guide
        if ($application->user->guide_id !== $guide->id) {
            abort(403, 'Unauthorized. This student is not assigned to you.');
        }

        // Eager load relationships for the details view
        $application->load([
            'user',
            'approvals' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'approvals.approver',
            'noc',
        ]);

        return view('faculty.guide-application-details', compact('application'));
    }
}
