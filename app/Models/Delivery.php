<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['order_id', 'access_token', 'expires_at', 'published_at', 'files', 'favorites', 'require_full_payment', 'download_count'])]
class Delivery extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $delivery): void {
            $delivery->access_token ??= self::newToken();
        });
    }

    public static function newToken(): string
    {
        return Str::random(48);
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'files' => 'array',
            'favorites' => 'array',
            'require_full_payment' => 'boolean',
            'download_count' => 'integer',
        ];
    }

    /** Portal hanya bisa dibuka bila sudah diterbitkan dan belum kedaluwarsa. */
    public function isActive(): bool
    {
        return $this->published_at !== null
            && $this->published_at->isPast()
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /** Unduhan hasil ditahan sampai lunas bila require_full_payment menyala. */
    public function downloadsLocked(): bool
    {
        return $this->require_full_payment && $this->order->hasUnpaidFinal();
    }

    public function portalUrl(): string
    {
        return route('portal.show', $this->access_token);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
