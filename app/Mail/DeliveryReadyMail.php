<?php

namespace App\Mail;

use App\Models\Delivery;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Ke klien: hasil dokumentasi siap diakses lewat portal. */
class DeliveryReadyMail extends Mailable
{
    public function __construct(public Delivery $delivery) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Hasil dokumentasi siap: '.$this->delivery->order->title);
    }

    public function content(): Content
    {
        $order = $this->delivery->order;

        return new Content(
            markdown: 'mail.delivery-ready',
            with: [
                'order' => $order,
                'portalUrl' => $this->delivery->portalUrl(),
                'expiresAt' => $this->delivery->expires_at,
                'fileCount' => count($this->delivery->files ?? []),
                'locked' => $this->delivery->downloadsLocked(),
            ],
        );
    }
}
