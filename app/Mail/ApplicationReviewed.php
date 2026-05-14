<?php

namespace App\Mail;

use App\Models\InternshipApplication;
use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationReviewed extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $approval;

    /**
     * Create a new message instance.
     */
    public function __construct(InternshipApplication $application, Approval $approval)
    {
        $this->application = $application;
        $this->approval = $approval;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = $this->approval->status === 'approved' ? 'Approved' : 'Rejected';
        return new Envelope(
            subject: "Your Internship NOC Application has been {$status}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-reviewed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
