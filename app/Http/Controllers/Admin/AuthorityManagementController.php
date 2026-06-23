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

class AuthorityManagementController extends Controller
{
    /**
     * Authority Management dashboard
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

        // Placeholders
        $students = collect();
        $batches = Batch::orderBy('name')->get();
        $departments = collect();
        $semesters = collect();
        $activeTab = 'authority_management';

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
     * Update faculty authorities
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:guide,approval_faculty,noc_authority',
        ]);

        $permissions = $request->input('permissions', []);

        $facultyRole = Role::where('name', 'faculty')->first() ?? (object)['id' => 2];
        $higherFacultyRole = Role::where('name', 'higher_faculty')->first() ?? (object)['id' => 3];

        $oldPermissions = $user->permissions->pluck('permission')->toArray();
        $oldRole = $user->role->name;

        DB::transaction(function() use ($user, $permissions, $facultyRole, $higherFacultyRole) {
            $user->syncPermissions($permissions);

            // Promotion/demotion logic based on noc_authority permission
            if ($user->hasPermission('noc_authority')) {
                $user->update([
                    'role_id' => $higherFacultyRole->id,
                ]);
            } else {
                $user->update([
                    'role_id' => $facultyRole->id,
                ]);
            }
        });

        // Refresh model relations
        $user->load(['permissions', 'role']);

        AuditLog::log(
            "Updated faculty authority: {$user->name}",
            "Faculty ID: {$user->id}",
            "permission_change",
            [
                'faculty_id' => $user->id,
                'old_permissions' => $oldPermissions,
                'new_permissions' => $permissions,
                'old_role' => $oldRole,
                'new_role' => $user->role->name
            ]
        );

        return redirect()->back()->with('success', "Faculty authority updated successfully to: " . $user->authority_display);
    }
}
