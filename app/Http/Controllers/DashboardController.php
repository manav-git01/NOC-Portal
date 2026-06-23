<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternshipApplication;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->isStudent()) {
            return $this->studentDashboard();
        }
        
        if ($user->isFaculty() || $user->isHigherFaculty()) {
            $permissions = $user->permissions->pluck('permission')->toArray();
            
            // Fallback for faculty with no explicit permissions but having students
            if (empty($permissions) && $user->students()->exists()) {
                $permissions[] = 'guide';
            }
            
            // If they have multiple permissions, handle dashboard selection
            if (count($permissions) > 1) {
                if (session()->has('selected_dashboard')) {
                    $selected = session('selected_dashboard');
                    if ($selected === 'guide' && in_array('guide', $permissions)) {
                        return redirect()->route('faculty.guide-dashboard');
                    }
                    if ($selected === 'approval_faculty' && in_array('approval_faculty', $permissions)) {
                        return redirect()->route('faculty.approval-dashboard');
                    }
                    if ($selected === 'noc_authority' && in_array('noc_authority', $permissions)) {
                        return redirect()->route('higher-faculty.noc-dashboard');
                    }
                }
                
                return redirect()->route('select-dashboard');
            }
            
            // If they have exactly one permission, auto-route
            if (count($permissions) === 1) {
                $perm = $permissions[0];
                if ($perm === 'guide') {
                    return redirect()->route('faculty.guide-dashboard');
                }
                if ($perm === 'approval_faculty') {
                    return redirect()->route('faculty.approval-dashboard');
                }
                if ($perm === 'noc_authority') {
                    return redirect()->route('higher-faculty.noc-dashboard');
                }
            }
            
            // Default fallback if no permissions match
            return redirect()->route('faculty.guide-dashboard');
        }
        
        abort(403, 'Unauthorized access.');
    }
    
    public function showSelectDashboard()
    {
        $user = auth()->user();
        
        if (!$user->isFaculty() && !$user->isHigherFaculty()) {
            return redirect()->route('dashboard');
        }
        
        $permissions = $user->permissions->pluck('permission')->toArray();
        if (count($permissions) <= 1) {
            return redirect()->route('dashboard');
        }
        
        return view('dashboards.select-dashboard', compact('user', 'permissions'));
    }
    
    public function switchDashboard($dashboard)
    {
        $user = auth()->user();
        $permissions = $user->permissions->pluck('permission')->toArray();
        
        // Include 'guide' fallback if they have students
        if (empty($permissions) && $user->students()->exists()) {
            $permissions[] = 'guide';
        }
        
        if (in_array($dashboard, $permissions)) {
            session(['selected_dashboard' => $dashboard]);
            
            if ($dashboard === 'guide') {
                return redirect()->route('faculty.guide-dashboard');
            } elseif ($dashboard === 'approval_faculty') {
                return redirect()->route('faculty.approval-dashboard');
            } elseif ($dashboard === 'noc_authority') {
                return redirect()->route('higher-faculty.noc-dashboard');
            }
        }
        
        return redirect()->route('dashboard');
    }
    
    public function approvalDashboard()
    {
        return $this->facultyDashboard();
    }
    
    public function nocDashboard()
    {
        return $this->higherFacultyDashboard();
    }
    
    private function studentDashboard()
    {
        $applications = InternshipApplication::where('user_id', auth()->id())
            ->with('noc')
            ->latest()
            ->get();
            
        return view('dashboards.student', compact('applications'));
    }
    
    private function facultyDashboard()
    {
        $pendingApplications = InternshipApplication::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();
            
        $reviewedApplications = InternshipApplication::whereIn('status', ['faculty_approved', 'faculty_rejected', 'higher_faculty_approved', 'higher_faculty_rejected', 'noc_generated'])
            ->whereNotNull('faculty_reviewed_at')
            ->with('user')
            ->latest()
            ->take(10)
            ->get();
        
        // Count statistics for dashboard cards
        // Count all applications that were approved by faculty (including those that moved to higher stages)
        $approvedCount = InternshipApplication::whereIn('status', ['faculty_approved', 'higher_faculty_approved', 'higher_faculty_rejected', 'noc_generated'])
            ->whereNotNull('faculty_reviewed_at')
            ->where('status', '!=', 'faculty_rejected')
            ->count();
        
        $rejectedCount = InternshipApplication::where('status', 'faculty_rejected')->count();
        $totalReviewed = $approvedCount + $rejectedCount;
            
        return view('dashboards.faculty', compact('pendingApplications', 'reviewedApplications', 'approvedCount', 'rejectedCount', 'totalReviewed'));
    }
    
    private function higherFacultyDashboard()
    {
        $pendingApplications = InternshipApplication::where('status', 'pending_higher')
            ->with('user')
            ->latest()
            ->get();
            
        $approvedApplications = InternshipApplication::whereIn('status', ['higher_faculty_approved', 'noc_generated'])
            ->with('user', 'noc')
            ->latest()
            ->take(10)
            ->get();
        
        // Count statistics for dashboard cards
        // Count all applications approved by higher faculty (NOCs generated)
        $approvedCount = InternshipApplication::whereIn('status', ['higher_faculty_approved', 'noc_generated'])
            ->whereNotNull('higher_faculty_reviewed_at')
            ->count();
        
        $rejectedCount = InternshipApplication::where('status', 'higher_faculty_rejected')
            ->whereNotNull('higher_faculty_reviewed_at')
            ->count();
        
        $totalReviewed = $approvedCount + $rejectedCount;
            
        return view('dashboards.higher-faculty', compact('pendingApplications', 'approvedApplications', 'approvedCount', 'rejectedCount', 'totalReviewed'));
    }}
