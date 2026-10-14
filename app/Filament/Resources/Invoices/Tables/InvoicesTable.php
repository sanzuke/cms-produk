<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Filament\Resources\Invoices\InvoiceActions;
use App\Models\Invoice;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('order'))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('number')->label('Nomor')->searchable()->sortable(),
                TextColumn::make('order.number')
                    ->label('Order')
                    ->description(fn (Invoice $record): ?string => $record->order?->client_name)
                    ->searchable(['orders.number', 'orders.client_name', 'orders.client_company']),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Invoice::TYPES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'dp' => 'info',
                        'final' => 'primary',
                        default => 'success',
                    }),
                TextColumn::make('amount')->label('Jumlah')->money('IDR', locale: 'id')->sortable(),
                TextColumn::make('due_date')
                    ->label('Jatuh tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Invoice $record): ?string => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn (Invoice $record) => $record->isOverdue() ? 'bold' : null),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Invoice::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'proof_submitted' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')->label('Dibayar')->date('d M Y')->placeholder('-')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Invoice::STATUSES),
                SelectFilter::make('type')->label('Jenis')->options(Invoice::TYPES),
                Filter::make('overdue')
                    ->label('Jatuh tempo terlewat')
                    ->query(fn (Builder $query) => $query
                        ->whereIn('status', ['unpaid', 'proof_submitted'])
                        ->whereDate('due_date', '<', today())),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make(InvoiceActions::all()),
            ]);
    }
}
