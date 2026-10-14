<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name', 'company', 'position', 'email', 'phone', 'service_id', 'site', 'commodity',
    'needed_date', 'duration', 'brief', 'attachments', 'status', 'assigned_to', 'internal_notes',
])]
class Inquiry extends Model
{
    /** Urutan pipeline sales sesuai PLAN §6.10. */
    public const STATUSES = [
        'new' => 'Baru',
        'contacted' => 'Dihubungi',
        'survey' => 'Survei',
        'quoted' => 'Penawaran',
        'won' => 'Menang',
        'lost' => 'Kalah',
    ];

    protected function casts(): array
    {
        return [
            'needed_date' => 'date',
            'attachments' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Nomor referensi yang ditampilkan ke calon klien, mis. RSV-2026-0007. */
    public function reference(): string
    {
        return sprintf('RSV-%s-%04d', $this->created_at->format('Y'), $this->id);
    }
}
