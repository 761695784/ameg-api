<?php

namespace App\Mail;

use App\Models\ProjectStudyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectStudyRequestClientAcknowledgment extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ProjectStudyRequest $projectStudyRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nous avons bien reçu votre demande d\'étude de projet — AMEG International',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.project-study-requests.client',
            with: [
                'projectStudyRequest' => $this->projectStudyRequest,
            ],
        );
    }
}
