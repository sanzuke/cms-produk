<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'inquiry_id', 'quotation_id', 'package_id', 'client_name', 'client_company', 'client_email', 'client_phone',
    'title', 'site', 'scheduled_start', 'scheduled_end', 'status', 'checklist', 'notes', 'total', 'created_by',
])]
class Order extends Model
{
    use HasDocumentNumber;

    /** Urutan = alur kerja (PLAN §6.12). */
    public const STATUSES = [
        'awaiting_dp' => 'Menunggu DP',
        'scheduled' => 'Dijadwalkan',
        'shooting' => 'Shooting',
        'editing' => 'Editing',
        'review' => 'Review klien',
        'done' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const DEFAULT_CHECKLIST = [
        'Izin masuk lokasi', 'Induksi K3', 'Izin terbang drone', 'Cek cuaca & jadwal cadangan', 'Peralatan siap',
    ];

    public static function numberPrefix(): string
    {
        return 'ORD';
    }

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (empty($order->checklist)) {
                $order->checklist = array_map(fn (string $l) => ['label' => $l, 'done' => false], self::DEFAULT_CHECKLIST);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'date',
            'scheduled_end' => 'date',
            'checklist' => 'array',
            'total' => 'integer',
        ];
    }

    /**
     * Penawaran disetujui klien → order baru berstatus "Menunggu DP".
     * Idempotent: bila order untuk penawaran ini sudah ada, dikembalikan apa adanya.
     */
    public static function createFromQuotation(Quotation $quotation, ?User $by = null): self
    {
        return DB::transaction(function () use ($quotation, $by): self {
            if ($existing = $quotation->order()->first()) {
                return $existing;
            }

            $quotation->forceFill(['status' => 'accepted', 'accepted_at' => $quotation->accepted_at ?? now()])->save();
            $quotation->inquiry?->update(['status' => 'won']);

            return self::create([
                'inquiry_id' => $quotation->inquiry_id,
                'quotation_id' => $quotation->id,
                'client_name' => $quotation->client_name,
                'client_company' => $quotation->client_company,
                'client_email' => $quotation->client_email,
                'client_phone' => $quotation->client_phone,
                'title' => $quotation->title,
                'site' => $quotation->inquiry?->site,
                'status' => 'awaiting_dp',
                'total' => $quotation->total,
                'created_by' => $by?->id,
            ]);
        });
    }

    /**
     * Terbitkan invoice. dp = persentase dari pengaturan; final = sisa tagihan; full = seluruhnya.
     * Jumlah bisa ditimpa untuk kasus khusus. Melempar DomainException bila tidak valid.
     */
    public function issueInvoice(string $type, ?int $amount = null, ?int $dueDays = 7): Invoice
    {
        if (! in_array($type, ['dp', 'final', 'full'], true)) {
            throw new \DomainException("Tipe invoice tidak dikenal: {$type}");
        }
        if ($type === 'dp' && $this->invoices()->where('type', 'dp')->where('status', '!=', 'cancelled')->exists()) {
            throw new \DomainException('Invoice DP untuk order ini sudah ada.');
        }

        $billed = (int) $this->invoices()->where('status', '!=', 'cancelled')->sum('amount');

        $amount ??= match ($type) {
            'dp' => (int) round($this->total * max(0, min(100, (int) config('site.dp_percent', 50))) / 100),
            'final' => $this->total - $billed,
            'full' => $this->total,
        };

        if ($amount <= 0) {
            throw new \DomainException('Jumlah invoice harus lebih dari nol (order mungkin sudah ditagih penuh).');
        }

        return $this->invoices()->create([
            'type' => $type,
            'amount' => $amount,
            'due_date' => $dueDays !== null ? now()->addDays($dueDays)->toDateString() : null,
            'status' => 'unpaid',
        ]);
    }

    public function paidAmount(): int
    {
        return (int) $this->invoices()->where('status', 'paid')->sum('amount');
    }

    public function outstanding(): int
    {
        return max(0, $this->total - $this->paidAmount());
    }

    /** True bila ada invoice pelunasan/penuh yang belum lunas (dipakai untuk menahan unduhan hasil). */
    public function hasUnpaidFinal(): bool
    {
        return $this->invoices()->whereIn('type', ['final', 'full'])->whereIn('status', ['unpaid', 'proof_submitted'])->exists()
            || $this->outstanding() > 0;
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OrderComment::class)->latest();
    }
}
