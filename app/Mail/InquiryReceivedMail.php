<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Konfirmasi otomatis ke calon klien bahwa permintaan penawaran sudah diterima. */
class InquiryReceivedMail extends Mailable
{
    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $contact = config('site.email');

        return new Envelope(
            subject: 'Permintaan Anda sudah kami terima ('.$this->inquiry->reference().')',
            replyTo: filled($contact) ? [new Address($contact, config('site.name'))] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.inquiry-received',
            with: ['inquiry' => $this->inquiry->loadMissing('service'), 'reference' => $this->inquiry->reference()],
        );
    }
}
