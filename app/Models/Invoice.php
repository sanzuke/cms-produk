<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'type', 'amount', 'due_date', 'status', 'paid_at', 'payment_ref', 'proof_path', 'notes', 'sent_at',
])]
class Invoice extends Model
{
    use HasDocumentNumber;

    public const TYPES = ['dp' => 'DP', 'final' => 'Pelunasan', 'full' => 'Pembayaran penuh'];

    public const STATUSES = [
        'unpaid' => 'Belum dibayar',
        'proof_submitted' => 'Bukti diunggah',
        'paid' => 'Lunas',
        'cancelled' => 'Dibatalkan',
    ];

    public static function numberPrefix(): string
    {
        return 'INV';
    }

    protected static function booted(): void
    {
        // DP/pembayaran penuh lunas → order "Menunggu DP" otomatis jadi "Dijadwalkan".
        static::saved(function (self $invoice): void {
            if ($invoice->status === 'paid' && in_array($invoice->type, ['dp', 'full'], true)) {
                $invoice->order()->where('status', 'awaiting_dp')->update(['status' => 'scheduled']);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function markPaid(?string $reference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => $this->paid_at ?? now(),
            'payment_ref' => $reference ?? $this->payment_ref,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null && $this->due_date->endOfDay()->isPast() && in_array($this->status, ['unpaid', 'proof_submitted'], true);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
