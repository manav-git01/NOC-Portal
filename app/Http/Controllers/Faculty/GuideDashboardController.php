<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\InternshipApplication;

class GuideDashboardController extends Controller
{
    /**
     * Display the Guide / Mentor Faculty Dashboard.
     * Student-centric monitoring portal with search & filters.
     */
    public function index(Request $request)
    {
        $guide = auth()->user();

        // Base query: all students assigned to this guide
        $baseQuery = User::where('guide_id', $guide->id);

        // Total assigned students (unfiltered count for the stat card)
        $totalStudents = $baseQuery->count();

        // Build filtered query
        $query = User::where('guide_id', $guide->id)
            ->with(['internshipApplications' => function ($q) {
                $q->latest()->with(['approvals.approver', 'noc']);
            }]);

        // Search filter: name, enrollment number, or email
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

        $students = $query->orderBy('name')->get();

        return view('faculty.guide-dashboard', compact(
            'students',
            'totalStudents'
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
