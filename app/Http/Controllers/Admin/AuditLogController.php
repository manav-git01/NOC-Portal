<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Role;
use App\Models\Batch;
use App\Models\InternshipApplication;
use App\Models\Noc;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs
     */
    public function index(Request $request)
    {
        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        $query = AuditLog::orderBy('timestamp', 'desc');

        // Filter by action type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by start date
        if ($request->filled('start_date')) {
            $query->whereDate('timestamp', '>=', $request->start_date);
        }

        // Filter by end date
        if ($request->filled('end_date')) {
            $query->whereDate('timestamp', '<=', $request->end_date);
        }

        // Search text filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('admin_name', 'like', "%$search%")
                  ->orWhere('action', 'like', "%$search%")
                  ->orWhere('target', 'like', "%$search%");
            });
        }

        $auditLogs = $query->paginate(50)->withQueryString();

        // Get distinct action types for filter dropdown
        $actionTypes = AuditLog::whereNotNull('action_type')
            ->distinct()
            ->pluck('action_type');

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

        // Placeholders
        $students = collect();
        $batches = Batch::orderBy('name')->get();
        $departments = collect();
        $semesters = collect();
        $activeTab = 'audit_logs';

        return view('admin.dashboard', compact(
            'auditLogs',
            'actionTypes',
            'students',
            'batches',
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
}
