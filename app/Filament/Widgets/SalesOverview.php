<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

/** Ringkasan penjualan di dasbor (PLAN §6.1) untuk owner dan sales. */
class SalesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected ?string $heading = 'Penjualan';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(Role::Owner, Role::Sales) ?? false;
    }

    protected function getStats(): array
    {
        $idr = fn (int $n) => Number::currency($n, 'IDR', 'id', 0);

        $openInvoices = Invoice::whereIn('status', ['unpaid', 'proof_submitted']);
        $revenue = (int) Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        return [
            Stat::make('Inquiry baru', Inquiry::where('status', 'new')->count())
                ->description('Belum ditindaklanjuti')
                ->color('warning'),
            Stat::make('Order berjalan', Order::whereNotIn('status', ['done', 'cancelled'])->count())
                ->description('Belum selesai atau dibatalkan'),
            Stat::make('Jadwal minggu ini', Order::whereNotIn('status', ['done', 'cancelled'])
                ->whereBetween('scheduled_start', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                ->count())
                ->description(now()->startOfWeek()->translatedFormat('d M').' – '.now()->endOfWeek()->translatedFormat('d M')),
            Stat::make('Pembayaran tertunda', $idr((int) (clone $openInvoices)->sum('amount')))
                ->description((clone $openInvoices)->count().' invoice belum lunas')
                ->color('danger'),
            Stat::make('Bukti perlu diverifikasi', Invoice::where('status', 'proof_submitted')->count())
                ->description('Diunggah klien lewat portal')
                ->color('info'),
            Stat::make('Pendapatan bulan ini', $idr($revenue))
                ->description('Invoice lunas '.now()->translatedFormat('F Y'))
                ->color('success'),
        ];
    }
}
