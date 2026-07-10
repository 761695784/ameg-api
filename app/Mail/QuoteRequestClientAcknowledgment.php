<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestClientAcknowledgment extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public QuoteRequest $quoteRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nous avons bien reçu votre demande de devis — AMEG International',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quote-requests.client',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'items' => $this->quoteRequest->items,
            ],
        );
    }
}
