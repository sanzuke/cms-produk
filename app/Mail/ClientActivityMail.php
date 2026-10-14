<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Route;

/** Notifikasi ke staf: klien mengunggah bukti bayar atau menulis komentar revisi di portal. */
class ClientActivityMail extends Mailable
{
    public const PROOF = 'proof';

    public const COMMENT = 'comment';

    public function __construct(
        public Order $order,
        public string $kind,
        public ?string $comment = null,
        public ?Invoice $invoice = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->kind === self::PROOF
            ? "Bukti bayar baru: {$this->invoice?->number} ({$this->order->number})"
            : "Komentar klien baru: {$this->order->number}";

        return new Envelope(
            subject: $subject,
            replyTo: filled($this->order->client_email) ? [new Address($this->order->client_email, $this->order->client_name)] : [],
        );
    }

    public function content(): Content
    {
        $adminUrl = Route::has('filament.admin.resources.orders.edit')
            ? route('filament.admin.resources.orders.edit', ['record' => $this->order->id])
            : url('/admin');

        $invoiceUrl = $this->invoice && Route::has('filament.admin.resources.invoices.edit')
            ? route('filament.admin.resources.invoices.edit', ['record' => $this->invoice->id])
            : null;

        return new Content(
            markdown: 'mail.client-activity',
            with: [
                'order' => $this->order,
                'kind' => $this->kind,
                'comment' => $this->comment,
                'invoice' => $this->invoice,
                'adminUrl' => $adminUrl,
                'invoiceUrl' => $invoiceUrl,
            ],
        );
    }
}
