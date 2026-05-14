<?php

namespace App\Http\Controllers\HigherFaculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternshipApplication;
use App\Models\Approval;
use App\Models\Noc;
use App\Mail\ApplicationReviewed;
use App\Mail\NocGenerated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class NocController extends Controller
{
    public function show(InternshipApplication $application)
    {
        return view('higher-faculty.applications.show', compact('application'));
    }

    public function approve(Request $request, InternshipApplication $application)
    {
        // Check if application is already approved or NOC already generated
        if (in_array($application->status, ['higher_faculty_approved', 'noc_generated'])) {
            return redirect()->route('dashboard')
                ->with('info', 'This application has already been approved.');
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Update application status and remarks
        $application->update([
            'higher_faculty_remarks' => $validated['remarks'] ?? null,
            'higher_faculty_reviewed_at' => now(),
        ]);

        // Create approval record
        $approval = Approval::create([
            'application_id' => $application->id,
            'approver_id' => auth()->id(),
            'approver_role' => 'higher_faculty',
            'status' => 'approved',
            'remarks' => $validated['remarks'] ?? null,
            'approved_at' => now(),
        ]);

        // Generate NOC (this will also update status to 'noc_generated')
        $this->generateNoc($application);

        // Reload application to get updated status
        $application->refresh();

        // Send approval email to student
        Mail::to($application->user->email)->send(new ApplicationReviewed($application, $approval));

        return redirect()->route('dashboard')
            ->with('success', 'Application approved and NOC generated successfully!');
    }

    public function reject(Request $request, InternshipApplication $application)
    {
        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        // Update application status
        $application->update([
            'status' => 'higher_faculty_rejected',
            'higher_faculty_remarks' => $validated['remarks'],
            'higher_faculty_reviewed_at' => now(),
        ]);

        // Create approval record
        $approval = Approval::create([
            'application_id' => $application->id,
            'approver_id' => auth()->id(),
            'approver_role' => 'higher_faculty',
            'status' => 'rejected',
            'remarks' => $validated['remarks'],
            'approved_at' => now(),
        ]);

        // Send rejection email to student
        Mail::to($application->user->email)->send(new ApplicationReviewed($application, $approval));

        return redirect()->route('dashboard')
            ->with('success', 'Application rejected.');
    }

    private function generateNoc(InternshipApplication $application)
    {
        \Log::info('Starting NOC generation for application ID: ' . $application->id);
        
        // Check if NOC already exists for this application
        $existingNoc = Noc::where('application_id', $application->id)->first();
        
        if ($existingNoc) {
            // Check if NOC has valid PDF file
            $hasValidPdf = !empty($existingNoc->pdf_path) 
                && Storage::disk('public')->exists($existingNoc->pdf_path);
            
            if ($hasValidPdf) {
                // NOC already exists with valid PDF, just update application status and return
            $application->update(['status' => 'noc_generated']);
                \Log::info('NOC already exists with valid PDF for application ID: ' . $application->id . ', status updated to noc_generated');
            return;
            } else {
                // NOC exists but PDF is missing or invalid, delete it and regenerate
                \Log::warning('NOC exists but PDF is missing/invalid for application ID: ' . $application->id . ', regenerating...');
                $existingNoc->delete();
            }
        }

        // Generate unique NOC number
        $nocNumber = 'NOC-' . date('Y') . '-' . str_pad($application->id, 6, '0', STR_PAD_LEFT);
        \Log::info('Generated NOC number: ' . $nocNumber);

        // Create NOC record FIRST (before generating PDF)
        $noc = Noc::create([
            'application_id' => $application->id,
            'noc_number' => $nocNumber,
            'pdf_path' => '', // Will be updated after PDF generation
            'generated_at' => now(),
            'generated_by' => auth()->id(),
        ]);
        \Log::info('NOC record created with ID: ' . $noc->id);

        // Load the generated_by_user relationship
        $noc->load('generated_by_user');

        // Prepare image paths and convert to base64 for DomPDF (works without GD extension)
        $charusatLogoPath = public_path('images/charusart_logo.jpeg');
        $cspitLogoPath = public_path('images/cspit_logo.png');
        $tpoSignaturePath = public_path('images/tpo_signature.jpeg');
        
        // Read and encode images to base64 with error handling
        $charusatLogoBase64 = null;
        $cspitLogoBase64 = null;
        $tpoSignatureBase64 = null;
        
        // Helper function to safely read and encode image
        $readImage = function($path, $imageName) {
            \Log::info("Attempting to load {$imageName} from: {$path}");
            
            if (!file_exists($path)) {
                \Log::error("{$imageName} NOT FOUND at: {$path}");
                return null;
            }
            
            \Log::info("{$imageName} file exists. Checking readability...");
            
            if (!is_readable($path)) {
                \Log::error("{$imageName} exists but is NOT READABLE at: {$path}. File may be blocked by Windows security.");
                \Log::error("SOLUTION: Right-click the file → Properties → Check 'Unblock' → OK");
                return null;
            }
            
            \Log::info("{$imageName} is readable. Reading file contents...");
            
            $content = file_get_contents($path);
            if ($content === false) {
                \Log::error("FAILED to read {$imageName} from: {$path}. File may be blocked by Windows security.");
                \Log::error("SOLUTION: Right-click the file → Properties → Check 'Unblock' → OK");
                return null;
            }
            
            if (empty($content)) {
                \Log::error("{$imageName} file is EMPTY at: {$path}");
                return null;
            }
            
            \Log::info("{$imageName} content read successfully: " . strlen($content) . " bytes");
            
            $base64 = base64_encode($content);
            if (empty($base64)) {
                \Log::error("FAILED to encode {$imageName} to base64");
                return null;
            }
            
            \Log::info("{$imageName} encoded to base64 successfully: " . strlen($base64) . " bytes");
            return $base64;
        };
        
        // Read all images
        $charusatLogoBase64 = $readImage($charusatLogoPath, 'CHARUSAT logo');
        $cspitLogoBase64 = $readImage($cspitLogoPath, 'CSPIT logo');
        $tpoSignatureBase64 = $readImage($tpoSignaturePath, 'TPO signature');
        
        // Log detailed summary
        \Log::info('=== IMAGE LOADING SUMMARY ===');
        \Log::info('CHARUSAT Logo: ' . ($charusatLogoBase64 ? '✓ LOADED (' . strlen($charusatLogoBase64) . ' bytes)' : '✗ FAILED'));
        \Log::info('CSPIT Logo: ' . ($cspitLogoBase64 ? '✓ LOADED (' . strlen($cspitLogoBase64) . ' bytes)' : '✗ FAILED'));
        \Log::info('TPO Signature: ' . ($tpoSignatureBase64 ? '✓ LOADED (' . strlen($tpoSignatureBase64) . ' bytes)' : '✗ FAILED'));
        \Log::info('=============================');
        
        if (!$charusatLogoBase64 || !$cspitLogoBase64 || !$tpoSignatureBase64) {
            \Log::error('SOME IMAGES FAILED TO LOAD! Check logs above for details.');
            \Log::error('If files are blocked, run: .\unblock-images.ps1 or unblock manually in Properties.');
        }

        // Prepare data for PDF
        $data = [
            'application' => $application,
            'student' => $application->user,
            'noc' => $noc,
            'charusatLogo' => $charusatLogoBase64,
            'cspitLogo' => $cspitLogoBase64,
            'tpoSignature' => $tpoSignatureBase64,
        ];
        
        // Debug: Log what we're passing to the view
        \Log::info('=== DATA PASSED TO PDF VIEW ===');
        \Log::info('charusatLogo: ' . ($charusatLogoBase64 ? 'SET (' . strlen($charusatLogoBase64) . ' bytes)' : 'NULL'));
        \Log::info('cspitLogo: ' . ($cspitLogoBase64 ? 'SET (' . strlen($cspitLogoBase64) . ' bytes)' : 'NULL'));
        \Log::info('tpoSignature: ' . ($tpoSignatureBase64 ? 'SET (' . strlen($tpoSignatureBase64) . ' bytes)' : 'NULL'));
        \Log::info('================================');

        try {
            // Generate PDF with options for better image support
        $pdf = Pdf::loadView('pdf.noc', $data);
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isPhpEnabled', true);
            $pdf->setOption('isJavascriptEnabled', false);
            $pdf->setOption('defaultFont', 'DejaVu Sans');
        
        // Save PDF
        $filename = 'noc_' . $application->id . '_' . time() . '.pdf';
        $path = 'nocs/' . $filename;
            
            $pdfContent = $pdf->output();
            if (empty($pdfContent)) {
                throw new \Exception('PDF generation produced empty content.');
            }
            
            $saved = Storage::disk('public')->put($path, $pdfContent);
            if (!$saved) {
                throw new \Exception('Failed to save PDF to storage.');
            }
            
        \Log::info('PDF saved to: ' . $path);

        // Update NOC record with PDF path
        $noc->update(['pdf_path' => $path]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate NOC PDF for application ID: ' . $application->id . ' - ' . $e->getMessage());
            // Delete the NOC record if PDF generation failed
            $noc->delete();
            throw new \Exception('Failed to generate NOC PDF: ' . $e->getMessage());
        }

        // Update application status to noc_generated
        $application->update(['status' => 'noc_generated']);
        \Log::info('Application status updated to noc_generated for ID: ' . $application->id);

        // Reload application with NOC relationship and user
        $application->load(['noc', 'user']);

        // Refresh the NOC to get the updated pdf_path
        $application->noc->refresh();

        // Send NOC generated email to student with PDF attachment
        try {
            Mail::to($application->user->email)->send(new NocGenerated($application));
            \Log::info('NOC email sent to: ' . $application->user->email);
        } catch (\Exception $e) {
            // Log the error but don't fail the entire process
            \Log::error('Failed to send NOC email: ' . $e->getMessage());
        }
    }

    public function downloadNoc(InternshipApplication $application)
    {
        // Load NOC relationship if not already loaded
        if (!$application->relationLoaded('noc')) {
            $application->load('noc');
        }

        if (!$application->noc) {
            return redirect()->route('higher-faculty.applications.show', $application)
                ->with('error', 'NOC has not been generated yet for this application.');
        }

        $pdfPath = $application->noc->pdf_path;

        // Validate pdf_path exists and is not empty
        if (empty($pdfPath) || trim($pdfPath) === '') {
            return redirect()->route('higher-faculty.applications.show', $application)
                ->with('error', 'NOC PDF file path is not set. Please regenerate the NOC.');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($pdfPath)) {
            return redirect()->route('higher-faculty.applications.show', $application)
                ->with('error', 'NOC PDF file not found in storage. Please regenerate the NOC.');
        }

        try {
            return Storage::disk('public')->download($pdfPath);
        } catch (\Exception $e) {
            \Log::error('Failed to download NOC for application ID: ' . $application->id . ' - ' . $e->getMessage());
            return redirect()->route('higher-faculty.applications.show', $application)
                ->with('error', 'Failed to download NOC. Please try again.');
        }
    }
}
