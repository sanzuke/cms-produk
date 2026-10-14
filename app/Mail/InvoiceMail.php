<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Invoice (PDF terlampir) untuk klien. */
class InvoiceMail extends Mailable
{
    public function __construct(public Invoice $invoice) {}

    public static function renderPdf(Invoice $invoice): DomPdf
    {
        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice->loadMissing('order')])->setPaper('a4');
    }

    public static function pdfFilename(Invoice $invoice): string
    {
        return ($invoice->number ?: 'INVOICE-'.$invoice->id).'.pdf';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Invoice {$this->invoice->number} — ".config('site.name'));
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice',
            with: ['invoice' => $this->invoice->loadMissing('order')],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => self::renderPdf($this->invoice)->output(), self::pdfFilename($this->invoice))
                ->withMime('application/pdf'),
        ];
    }
}
