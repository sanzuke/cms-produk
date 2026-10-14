<?php

namespace App\Mail;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Penawaran harga ke klien, dengan PDF terlampir. */
class QuotationMail extends Mailable
{
    public function __construct(public Quotation $quotation) {}

    /** Dokumen PDF penawaran; dipakai juga oleh aksi unduh di admin. */
    public static function pdf(Quotation $quotation): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.quotation', ['quotation' => $quotation])->setPaper('a4');
    }

    public static function filename(Quotation $quotation): string
    {
        return ($quotation->number ?: 'Penawaran').'.pdf';
    }

    public function envelope(): Envelope
    {
        $contact = config('site.email');

        return new Envelope(
            subject: "Penawaran {$this->quotation->number}: {$this->quotation->title}",
            replyTo: filled($contact) ? [new Address($contact, config('site.name'))] : [],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.quotation', with: ['quotation' => $this->quotation]);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => self::pdf($this->quotation)->output(), self::filename($this->quotation))
                ->withMime('application/pdf'),
        ];
    }
}
