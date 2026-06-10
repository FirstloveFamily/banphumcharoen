<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployerExpiryAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $employer;
    public $expiringWorkers;

    /**
     * Create a new message instance.
     */
    public function __construct($employer, $expiringWorkers)
    {
        $this->employer = $employer;
        $this->expiringWorkers = $expiringWorkers;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'แจ้งเตือน: เอกสารแรงงานในสังกัดของท่านใกล้หมดอายุ - ' . $this->employer->company_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.employer-expiry-alert',
        );
    }
}
