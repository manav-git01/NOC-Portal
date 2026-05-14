<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Noc;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class RegenerateNocs extends Command
{
    protected $signature = 'app:regenerate-nocs';

    protected $description = 'Regenerate all NOCs with the updated template format';

    public function handle()
    {
        $nocs = Noc::all();
        $total = $nocs->count();

        if ($total === 0) {
            $this->info('No NOCs found to regenerate.');
            return 0;
        }

        $this->info("Regenerating {$total} NOC(s)...");

        $count = 0;
        foreach ($nocs as $noc) {
            try {
                $application = $noc->application;
                if (!$application) {
                    $this->warn("Application not found for NOC ID {$noc->id}, skipping...");
                    continue;
                }

                // Load images (same as in NocController)
                $charusatLogoPath = public_path('images/charusart_logo.jpeg');
                $cspitLogoPath = public_path('images/cspit_logo.png');
                $tpoSignaturePath = public_path('images/tpo_signature.jpeg');
                
                $readImage = function($path) {
                    if (!file_exists($path) || !is_readable($path)) {
                        return null;
                    }
                    $content = @file_get_contents($path);
                    return $content !== false && !empty($content) ? base64_encode($content) : null;
                };
                
                $data = [
                    'application' => $application,
                    'student' => $application->user,
                    'noc' => $noc,
                    'charusatLogo' => $readImage($charusatLogoPath),
                    'cspitLogo' => $readImage($cspitLogoPath),
                    'tpoSignature' => $readImage($tpoSignaturePath),
                ];

                $pdf = Pdf::loadView('pdf.noc', $data);
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('isRemoteEnabled', true);
                $pdf->setOption('isHtml5ParserEnabled', true);
                
                $filename = 'noc_' . $application->id . '_' . time() . '.pdf';
                $path = 'nocs/' . $filename;
                Storage::disk('public')->put($path, $pdf->output());

                $noc->update(['pdf_path' => $path]);
                
                $count++;
                $this->line("✓ Regenerated NOC {$count}/{$total} (ID: {$noc->id})");
            } catch (\Exception $e) {
                $this->error("Failed to regenerate NOC ID {$noc->id}: {$e->getMessage()}");
            }
        }

        $this->info("\nRegenereration complete! {$count}/{$total} NOCs updated successfully.");
        return 0;
    }
}
