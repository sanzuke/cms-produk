<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Invoice;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Number;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Invoice';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('No. Invoice'),
                TextColumn::make('type')->label('Jenis')->formatStateUsing(fn (string $state): string => Invoice::TYPES[$state] ?? $state),
                TextColumn::make('amount')->label('Jumlah')->formatStateUsing(fn ($state): string => Number::currency((int) $state, 'IDR', 'id', 0)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Invoice::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'proof_submitted' => 'info',
                        'cancelled' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('due_date')->label('Jatuh tempo')->date('d M Y')->placeholder('-')
                    ->color(fn (Invoice $record): ?string => $record->isOverdue() ? 'danger' : null),
            ])
            ->recordUrl(fn (Invoice $record): ?string => Route::has('filament.admin.resources.invoices.edit')
                ? route('filament.admin.resources.invoices.edit', ['record' => $record->id])
                : null)
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
