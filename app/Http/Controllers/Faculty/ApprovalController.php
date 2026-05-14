<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternshipApplication;
use App\Models\Approval;
use App\Models\User;
use App\Mail\ApplicationReviewed;
use Illuminate\Support\Facades\Mail;

class ApprovalController extends Controller
{
    public function show(InternshipApplication $application)
    {
        return view('faculty.applications.show', compact('application'));
    }

    public function approve(Request $request, InternshipApplication $application)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Update application status
        $application->update([
            'status' => 'faculty_approved',
            'faculty_remarks' => $validated['remarks'] ?? null,
            'faculty_reviewed_at' => now(),
        ]);

        // Create approval record
        $approval = Approval::create([
            'application_id' => $application->id,
            'approver_id' => auth()->id(),
            'approver_role' => 'faculty',
            'status' => 'approved',
            'remarks' => $validated['remarks'] ?? null,
            'approved_at' => now(),
        ]);

        // Send email to student
        Mail::to($application->user->email)->send(new ApplicationReviewed($application, $approval));

        // Send email to higher faculty members
        $higherFacultyMembers = User::whereHas('role', function($query) {
            $query->where('name', 'higher_faculty');
        })->get();
        foreach ($higherFacultyMembers as $higherFaculty) {
            Mail::to($higherFaculty->email)->send(new ApplicationReviewed($application, $approval));
        }

        return redirect()->route('dashboard')
            ->with('success', 'Application approved successfully!');
    }

    public function reject(Request $request, InternshipApplication $application)
    {
        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        // Update application status
        $application->update([
            'status' => 'faculty_rejected',
            'faculty_remarks' => $validated['remarks'],
            'faculty_reviewed_at' => now(),
        ]);

        // Create approval record
        $approval = Approval::create([
            'application_id' => $application->id,
            'approver_id' => auth()->id(),
            'approver_role' => 'faculty',
            'status' => 'rejected',
            'remarks' => $validated['remarks'],
            'approved_at' => now(),
        ]);

        // Send email to student
        Mail::to($application->user->email)->send(new ApplicationReviewed($application, $approval));

        return redirect()->route('dashboard')
            ->with('success', 'Application rejected.');
    }
}
