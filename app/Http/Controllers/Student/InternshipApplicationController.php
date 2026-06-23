<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternshipApplication;
use App\Models\User;
use App\Mail\ApplicationSubmitted;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class InternshipApplicationController extends Controller
{
    public function create()
    {
        return view('student.applications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Company Information
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_website' => 'required|url|max:255',
            'branch_address' => 'required|string',
            'number_of_employees' => 'required|string|max:255',
            'branch_locations' => 'nullable|string',
            'head_office_address' => 'nullable|string',
            
            // Contact Person Details
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            
            // HR Details
            'hr_name' => 'nullable|string|max:255',
            'hr_phone' => 'nullable|string|max:20',
            'hr_email' => 'nullable|email|max:255',
            
            // Company Work Details
            'technology' => 'required|string|max:255',
            'current_project' => 'nullable|string',
            'clients' => 'nullable|string',
            'how_did_you_get_company' => 'required|string|max:255',
            'reason_to_select_company' => 'required|string',
            
            // Internship Details
            'internship_position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'internship_description' => 'nullable|string',
            
            // Documents
            'company_letter' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        // Handle company letter upload
        if ($request->hasFile('company_letter')) {
            $companyLetterPath = $request->file('company_letter')->store('company_letters', 'public');
            $validated['company_letter_path'] = $companyLetterPath;
        }

        // Create application
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';
        $validated['submitted_at'] = now();

        $application = InternshipApplication::create($validated);

        // Send email notification to all faculty members
        $facultyMembers = User::whereHas('role', function($query) {
            $query->where('name', 'faculty');
        })->get();
        
        foreach ($facultyMembers as $faculty) {
            if (env('MAIL_NOTIFICATIONS_ENABLED', true)) {
                Mail::to($faculty->email)->send(new ApplicationSubmitted($application));
            }
        }

        return redirect()->route('dashboard')
            ->with('success', 'Internship application submitted successfully!');
    }

    public function show(InternshipApplication $application)
    {
        // Ensure student can only view their own applications
        if ($application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('student.applications.show', compact('application'));
    }

    public function requestNoc(InternshipApplication $application)
    {
        if ($application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$application->canRequestNoc()) {
            return redirect()->route('student.applications.show', $application)
                ->with('error', 'NOC cannot be requested for this application at this time.');
        }

        if ($application->status === 'faculty_approved') {
            $application->update([
                'noc_requested' => true,
                'status' => 'pending_higher'
            ]);
        } elseif ($application->status === 'faculty_rejected') {
            $application->update(['noc_requested' => true]);
        }

        $higherFacultyMembers = User::whereHas('role', function($query) {
            $query->where('name', 'higher_faculty');
        })->get();

        foreach ($higherFacultyMembers as $higherFaculty) {
            if (env('MAIL_NOTIFICATIONS_ENABLED', true)) {
                Mail::to($higherFaculty->email)->send(new \App\Mail\NocRequested($application));
            }
        }

        return redirect()->route('student.applications.show', $application)
            ->with('success', 'NOC request has been submitted to higher faculty for review!');
    }

    public function downloadNoc(InternshipApplication $application)
    {
        // Ensure student can only download their own NOC
        if ($application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Load NOC relationship if not already loaded
        if (!$application->relationLoaded('noc')) {
            $application->load('noc');
        }

        if (!$application->noc) {
            return redirect()->route('student.applications.show', $application)
                ->with('error', 'NOC has not been generated yet for this application.');
        }

        $pdfPath = $application->noc->pdf_path;

        // Validate pdf_path exists and is not empty
        if (empty($pdfPath) || trim($pdfPath) === '') {
            return redirect()->route('student.applications.show', $application)
                ->with('error', 'NOC PDF file path is not set. Please contact the administrator to regenerate the NOC.');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($pdfPath)) {
            return redirect()->route('student.applications.show', $application)
                ->with('error', 'NOC PDF file not found in storage. The file may have been deleted. Please contact the administrator.');
        }

        try {
            return Storage::disk('public')->download($pdfPath);
        } catch (\Exception $e) {
            \Log::error('Failed to download NOC for application ID: ' . $application->id . ' - ' . $e->getMessage());
            return redirect()->route('student.applications.show', $application)
                ->with('error', 'Failed to download NOC. Please try again or contact the administrator.');
        }
    }
}
