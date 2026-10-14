<?php

namespace App\Models\Concerns;

/**
 * Nomor dokumen berbasis id: PREFIX-YYYY-0001 (mis. QUO-2026-0007).
 * Dibuat setelah insert agar unik tanpa race condition penghitung terpisah.
 */
trait HasDocumentNumber
{
    abstract public static function numberPrefix(): string;

    protected static function bootHasDocumentNumber(): void
    {
        static::created(function (self $model): void {
            if ($model->number === null) {
                $model->number = sprintf('%s-%s-%04d', static::numberPrefix(), $model->created_at->format('Y'), $model->id);
                $model->saveQuietly();
            }
        });
    }
}
