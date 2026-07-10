<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestAdminNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public QuoteRequest $quoteRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle demande de devis — ' . $this->quoteRequest->first_name . ' ' . $this->quoteRequest->last_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quote-requests.admin',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'items' => $this->quoteRequest->items,
            ],
        );
    }
}
