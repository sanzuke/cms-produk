<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\Actions\OrderActions;
use App\Models\Order;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        $money = fn (int|float|null $n): string => Number::currency((int) $n, 'IDR', 'id', 0);

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withSum(['invoices as paid_sum' => fn (Builder $q) => $q->where('status', 'paid')], 'amount'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('No. Order')->searchable()->sortable(),
                TextColumn::make('client_name')->label('Klien')->searchable(['client_name', 'client_company'])
                    ->description(fn (Order $record): ?string => $record->client_company),
                TextColumn::make('title')->label('Proyek')->searchable()->limit(40)
                    ->description(fn (Order $record): ?string => $record->site),
                TextColumn::make('schedule')
                    ->label('Jadwal')
                    ->state(function (Order $record): string {
                        if (! $record->scheduled_start) {
                            return '-';
                        }

                        $start = $record->scheduled_start->translatedFormat('d M Y');

                        return $record->scheduled_end && ! $record->scheduled_end->isSameDay($record->scheduled_start)
                            ? $start.' – '.$record->scheduled_end->translatedFormat('d M Y')
                            : $start;
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('scheduled_start', $direction)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => self::statusColor($state)),
                TextColumn::make('total')->label('Total')->formatStateUsing($money)->sortable(),
                TextColumn::make('paid_sum')->label('Terbayar')->formatStateUsing($money),
                TextColumn::make('outstanding')
                    ->label('Sisa')
                    ->state(fn (Order $record): int => max(0, $record->total - (int) $record->paid_sum))
                    ->formatStateUsing($money)
                    ->color(fn (Order $record): ?string => $record->status !== 'cancelled' && $record->total > (int) $record->paid_sum ? 'warning' : null),
                TextColumn::make('checklist_progress')
                    ->label('Checklist')
                    ->state(function (Order $record): string {
                        $items = collect($record->checklist ?? []);

                        return $items->where('done', true)->count().'/'.$items->count();
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(Order::STATUSES),
                Filter::make('this_week')
                    ->label('Jadwal minggu ini')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('scheduled_start')
                        ->where('scheduled_start', '<=', now()->endOfWeek()->toDateString())
                        ->whereRaw('COALESCE(scheduled_end, scheduled_start) >= ?', [now()->startOfWeek()->toDateString()])),
                Filter::make('unpaid')
                    ->label('Belum lunas')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', '!=', 'cancelled')
                        ->whereRaw("total > (select coalesce(sum(amount), 0) from invoices where invoices.order_id = orders.id and invoices.status = 'paid')")),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make(OrderActions::make()),
            ]);
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'awaiting_dp' => 'warning',
            'scheduled' => 'info',
            'shooting' => 'primary',
            'editing' => 'gray',
            'review' => 'info',
            'done' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }
}
