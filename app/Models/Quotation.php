<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'inquiry_id', 'client_name', 'client_company', 'client_email', 'client_phone', 'title',
    'items', 'mobilization', 'discount', 'notes', 'terms', 'valid_until',
    'status', 'sent_at', 'accepted_at', 'created_by',
])]
class Quotation extends Model
{
    use HasDocumentNumber;

    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Terkirim',
        'accepted' => 'Disetujui',
        'rejected' => 'Ditolak',
        'expired' => 'Kedaluwarsa',
    ];

    public static function numberPrefix(): string
    {
        return 'QUO';
    }

    protected static function booted(): void
    {
        // Total selalu dihitung ulang dari item agar tidak bisa menyimpang.
        static::saving(function (self $q): void {
            $q->total = $q->computeTotal();
        });
    }

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'mobilization' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function subtotal(): int
    {
        return (int) round(collect($this->items ?? [])->sum(
            fn (array $i) => (float) ($i['qty'] ?? 0) * (int) ($i['unit_price'] ?? 0)
        ));
    }

    public function computeTotal(): int
    {
        return max(0, $this->subtotal() + (int) $this->mobilization - (int) $this->discount);
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->endOfDay()->isPast() && in_array($this->status, ['draft', 'sent'], true);
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
