<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\SalesOnly;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/** Kalender bulanan jadwal order (PLAN §6.12). Dirender di server, tanpa pustaka JS. */
class ScheduleCalendar extends Page
{
    use SalesOnly;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Penjualan';

    protected static ?int $navigationSort = 35;

    protected static ?string $navigationLabel = 'Jadwal';

    protected static ?string $title = 'Jadwal Proyek';

    protected static ?string $slug = 'jadwal';

    protected string $view = 'filament.pages.schedule-calendar';

    public int $year;

    public int $month;

    public function mount(): void
    {
        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
    }

    public function previousMonth(): void
    {
        $this->shift(-1);
    }

    public function nextMonth(): void
    {
        $this->shift(1);
    }

    public function today(): void
    {
        $this->mount();
    }

    private function shift(int $months): void
    {
        $date = $this->monthStart()->addMonthsNoOverflow($months);
        $this->year = $date->year;
        $this->month = $date->month;
    }

    private function monthStart(): CarbonImmutable
    {
        return CarbonImmutable::create(max(1970, min(2200, $this->year)), max(1, min(12, $this->month)), 1)->startOfDay();
    }

    /** Order aktif yang rentang jadwalnya beririsan dengan [$from, $to]. */
    private function ordersBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('scheduled_start')
            ->where('scheduled_start', '<=', $to->toDateString())
            ->whereRaw('COALESCE(scheduled_end, scheduled_start) >= ?', [$from->toDateString()])
            ->orderBy('scheduled_start')
            ->get();
    }

    protected function getViewData(): array
    {
        $first = $this->monthStart();
        $gridStart = $first->startOfWeek();
        $gridEnd = $first->endOfMonth()->endOfWeek();

        $orders = $this->ordersBetween($gridStart, $gridEnd);

        $weeks = [];
        for ($day = $gridStart; $day <= $gridEnd; $day = $day->addDay()) {
            $key = $day->toDateString();
            $weeks[(int) floor($day->diffInDays($gridStart) / 7)][] = [
                'date' => $day,
                'inMonth' => $day->month === $first->month,
                'isToday' => $day->isToday(),
                'chips' => $orders->filter(fn (Order $o) => $o->scheduled_start->toDateString() <= $key
                    && ($o->scheduled_end ?? $o->scheduled_start)->toDateString() >= $key)
                    ->map(fn (Order $o) => $this->chip($o))->values(),
            ];
        }

        return [
            'monthLabel' => $first->translatedFormat('F Y'),
            'weekdays' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            'weeks' => $weeks,
            'thisWeek' => $this->ordersBetween(CarbonImmutable::now()->startOfWeek(), CarbonImmutable::now()->endOfWeek())
                ->map(fn (Order $o) => $this->chip($o) + [
                    'range' => $o->scheduled_start->translatedFormat('d M')
                        .($o->scheduled_end && ! $o->scheduled_end->isSameDay($o->scheduled_start) ? ' – '.$o->scheduled_end->translatedFormat('d M') : ''),
                    'client' => $o->client_company ?: $o->client_name,
                ]),
            'legend' => collect(Order::STATUSES)->except('cancelled')->map(fn (string $label, string $status) => [
                'label' => $label,
                'color' => OrdersTable::statusColor($status),
            ]),
        ];
    }

    /** @return array{id:int,title:string,number:?string,status:string,statusLabel:string,color:string,url:string} */
    private function chip(Order $order): array
    {
        return [
            'id' => $order->id,
            'title' => $order->title,
            'number' => $order->number,
            'statusLabel' => Order::STATUSES[$order->status] ?? $order->status,
            'color' => OrdersTable::statusColor($order->status),
            'url' => Route::has('filament.admin.resources.orders.edit')
                ? route('filament.admin.resources.orders.edit', ['record' => $order->id])
                : '#',
        ];
    }
}
