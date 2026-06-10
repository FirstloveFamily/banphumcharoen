<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagerDailySummary extends Mailable
{
    use Queueable, SerializesModels;

    public $summaryData;

    /**
     * Create a new message instance.
     */
    public function __construct($summaryData)
    {
        $this->summaryData = $summaryData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'รายงานสรุปผลการดำเนินงานประจำวัน - ' . now()->format('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.manager-summary',
        );
    }
}
