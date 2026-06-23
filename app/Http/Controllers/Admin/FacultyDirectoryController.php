<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Batch;
use App\Models\GuideAssignment;
use App\Models\AuditLog;
use App\Models\InternshipApplication;
use App\Models\Noc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacultyDirectoryController extends Controller
{
    /**
     * Faculty listing
     */
    public function index()
    {
        $studentRole = Role::where('name', 'student')->first() ?? (object)['id' => 1];
        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        $faculty = User::whereIn('role_id', [$facultyRole->id, $higherFacultyRole->id])
            ->with(['permissions'])
            ->withCount('students')
            ->orderBy('name')->get();

        foreach ($faculty as $fac) {
            $fac->batches_count = User::where('guide_id', $fac->id)
                ->where('role_id', $studentRole->id)
                ->whereNotNull('batch_id')
                ->distinct('batch_id')
                ->count('batch_id');
        }

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

        // Placeholder defaults for view compatibility
        $students = collect();
        $batches = Batch::orderBy('name')->get();
        $departments = collect();
        $semesters = collect();
        $activeTab = 'faculty_directory';

        return view('admin.dashboard', compact(
            'faculty',
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
     * Add faculty manually (directory only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required|string|max:255|unique:users,faculty_id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'department' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
        ]);

        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];

        // Create faculty record
        $faculty = User::create([
            'faculty_id' => $request->faculty_id,
            'name' => $request->name,
            'email' => $request->email,
            'department' => $request->department,
            'designation' => $request->designation,
            'role_id' => $facultyRole->id,
            'phone' => 'N/A',
            'password' => Hash::make(\Illuminate\Support\Str::random(16)),
            'account_status' => 'inactive', // directory record
            'status' => 'Active',
        ]);

        AuditLog::log(
            "Created directory faculty record: {$faculty->name} ({$faculty->faculty_id})",
            "Faculty ID: {$faculty->id}",
            "faculty_create",
            ['faculty' => $faculty->only(['id', 'name', 'faculty_id', 'email', 'department', 'designation'])]
        );

        return redirect()->route('admin.faculty-directory.index', ['tab' => 'faculty_directory'])->with('success', 'Faculty record added to directory successfully.');
    }

    /**
     * Edit faculty
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'faculty_id' => 'required|string|max:255|unique:users,faculty_id,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'department' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
        ]);

        $user->update([
            'faculty_id' => $request->faculty_id,
            'name' => $request->name,
            'email' => $request->email,
            'department' => $request->department,
            'designation' => $request->designation,
        ]);

        AuditLog::log(
            "Updated faculty record: {$user->name} ({$user->faculty_id})",
            "Faculty ID: {$user->id}",
            "faculty_update",
            ['faculty' => $user->only(['id', 'name', 'faculty_id', 'email', 'department', 'designation'])]
        );

        return redirect()->route('admin.faculty-directory.index', ['tab' => 'faculty_directory'])->with('success', 'Faculty record updated successfully.');
    }

    /**
     * Deactivate faculty (toggle account status)
     */
    public function deactivate(Request $request, User $user)
    {
        $oldStatus = $user->account_status;
        $newStatus = $oldStatus === 'active' ? 'inactive' : 'active';

        $user->update([
            'account_status' => $newStatus,
        ]);

        AuditLog::log(
            "Toggled faculty account status: {$user->name} ({$oldStatus} -> {$newStatus})",
            "Faculty ID: {$user->id}",
            "faculty_deactivate",
            ['faculty_id' => $user->id, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );

        $msg = $newStatus === 'active' ? 'Faculty account activated successfully.' : 'Faculty account deactivated successfully.';
        return redirect()->back()->with('success', $msg);
    }

    /**
     * Delete faculty (with confirmation)
     */
    public function destroy(Request $request, User $user)
    {
        $request->validate([
            'confirmation_text' => 'required|string',
        ]);

        if ($request->confirmation_text !== $user->name) {
            return redirect()->route('admin.faculty-directory.index', ['tab' => 'faculty_directory'])
                ->with('error', 'Faculty deletion aborted: Deletion confirmation text did not match the Faculty Name.');
        }

        // Dependency Check
        $hasStudents = User::where('guide_id', $user->id)->exists();
        $hasActiveAssignments = GuideAssignment::where('guide_id', $user->id)->whereNull('unassigned_at')->exists();
        $hasSpecialRights = $user->hasPermission('approval_faculty') || $user->hasPermission('noc_authority');

        if ($hasStudents || $hasActiveAssignments || $hasSpecialRights) {
            return redirect()->route('admin.faculty-directory.index', ['tab' => 'faculty_directory'])
                ->with('error', 'This faculty cannot be deleted because active assignments exist. Please reassign responsibilities first.');
        }

        $name = $user->name;
        $email = $user->email;
        $facultyId = $user->faculty_id;

        // Clean up permissions
        $user->permissions()->delete();
        $user->delete();

        AuditLog::log(
            "Deleted faculty record: {$name} ({$facultyId})",
            "Faculty Email: {$email}",
            "faculty_delete",
            ['name' => $name, 'email' => $email, 'faculty_id' => $facultyId]
        );

        return redirect()->route('admin.faculty-directory.index', ['tab' => 'faculty_directory'])->with('success', 'Faculty record deleted successfully.');
    }
}
