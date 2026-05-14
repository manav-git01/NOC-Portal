<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternshipApplication;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isStudent()) {
            return $this->studentDashboard();
        } elseif ($user->isFaculty()) {
            return $this->facultyDashboard();
        } elseif ($user->isHigherFaculty()) {
            return $this->higherFacultyDashboard();
        }
        
        abort(403, 'Unauthorized access.');
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
    }
}
