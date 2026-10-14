<?php

namespace App\Mail;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Notifikasi ke staf saat ada permintaan penawaran baru. */
class NewInquiryMail extends Mailable
{
    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $who = $this->inquiry->company ? "{$this->inquiry->name} ({$this->inquiry->company})" : $this->inquiry->name;

        return new Envelope(
            subject: "Inquiry baru: {$who}",
            // Staf cukup menekan "Balas" untuk menghubungi calon klien.
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-inquiry',
            with: [
                'inquiry' => $this->inquiry->loadMissing('service'),
                'adminUrl' => InquiryResource::getUrl('edit', ['record' => $this->inquiry]),
            ],
        );
    }
}
