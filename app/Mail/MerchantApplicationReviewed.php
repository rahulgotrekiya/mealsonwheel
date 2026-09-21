<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MerchantApplicationReviewed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $merchant,
        public bool $approved,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->approved
                ? 'Your Meals on Wheels supplier account is ready'
                : 'About your Meals on Wheels supplier application',
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.merchant-application-reviewed');
    }
}
