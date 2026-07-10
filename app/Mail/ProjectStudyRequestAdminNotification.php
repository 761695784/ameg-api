<?php

namespace App\Mail;

use App\Models\ProjectStudyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectStudyRequestAdminNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ProjectStudyRequest $projectStudyRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle demande d\'étude de projet — ' . $this->projectStudyRequest->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.project-study-requests.admin',
            with: [
                'projectStudyRequest' => $this->projectStudyRequest,
                'documents' => $this->projectStudyRequest->documents,
            ],
        );
    }
}
