<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Batch;
use App\Models\AuditLog;
use App\Models\InternshipApplication;
use App\Models\Noc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountManagementController extends Controller
{
    /**
     * Account Management dashboard
     */
    public function index()
    {
        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        $registeredStudents = User::where('role_id', $studentRole->id)
            ->where('account_status', 'active')
            ->orderBy('name')
            ->get();

        $registeredFaculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->where('account_status', 'active')
            ->orderBy('name')
            ->get();

        $pendingUsers = User::where('account_status', 'pending')
            ->with('role')
            ->orderBy('created_at', 'desc')
            ->get();

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
        $activeTab = 'account_management';

        return view('admin.dashboard', compact(
            'registeredStudents',
            'registeredFaculty',
            'pendingUsers',
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

    /**
     * Activate a pending user
     */
    public function activate(User $user)
    {
        $user->update([
            'account_status' => 'active',
        ]);

        AuditLog::log(
            "Activated user account: {$user->name} ({$user->email})",
            "User ID: {$user->id}",
            "account_activate",
            ['user_id' => $user->id, 'email' => $user->email]
        );

        return redirect()->back()->with('success', 'User account activated successfully.');
    }

    /**
     * Deactivate an active user account
     */
    public function deactivate(User $user)
    {
        $user->update([
            'account_status' => 'inactive',
        ]);

        AuditLog::log(
            "Deactivated/Suspended user account: {$user->name} ({$user->email})",
            "User ID: {$user->id}",
            "account_deactivate",
            ['user_id' => $user->id, 'email' => $user->email]
        );

        return redirect()->back()->with('success', 'User account deactivated successfully.');
    }
}
