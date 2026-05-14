<?php

namespace App\Mail;

use App\Models\InternshipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NocGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    /**
     * Create a new message instance.
     */
    public function __construct(InternshipApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Internship NOC has been Generated',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.noc-generated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->application->noc && $this->application->noc->pdf_path) {
            // Check if file exists before attaching
            $filePath = $this->application->noc->pdf_path;
            if (Storage::disk('public')->exists($filePath)) {
                // Use fromStorageDisk to specify the disk explicitly
                $attachments[] = Attachment::fromStorageDisk('public', $filePath)
                    ->as('NOC_' . $this->application->noc->noc_number . '.pdf')
                    ->withMime('application/pdf');
            }
        }
        
        return $attachments;
    }
}
