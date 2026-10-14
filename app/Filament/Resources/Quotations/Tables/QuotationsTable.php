<?php

namespace App\Filament\Resources\Quotations\Tables;

use App\Filament\Resources\Quotations\Actions\QuotationActions;
use App\Models\Quotation;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuotationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('Nomor')->searchable()->sortable(),
                TextColumn::make('client_name')->label('Klien')->searchable()
                    ->description(fn (Quotation $record) => $record->client_company),
                TextColumn::make('title')->label('Judul')->searchable()->limit(40)->toggleable(),
                TextColumn::make('total')->label('Total')->money('IDR', locale: 'id')->sortable(),
                TextColumn::make('valid_until')->label('Berlaku sampai')->date('d M Y')->sortable()
                    ->badge()
                    ->color(fn (Quotation $record) => $record->isExpired() ? 'danger' : 'gray'),
                // Kedaluwarsa hanya tampilan: status di database tidak diubah otomatis.
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, Quotation $record) => Quotation::STATUSES[$record->isExpired() ? 'expired' : $state] ?? $state)
                    ->color(fn (string $state, Quotation $record) => $record->isExpired() ? 'danger' : match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected', 'expired' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Dibuat')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(Quotation::STATUSES),
                Filter::make('lewat_masa_berlaku')
                    ->label('Lewat masa berlaku')
                    ->query(fn (Builder $query) => $query
                        ->whereIn('status', ['draft', 'sent'])
                        ->whereDate('valid_until', '<', today())),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    QuotationActions::download(),
                    QuotationActions::send(),
                    QuotationActions::accept(),
                    QuotationActions::reject(),
                ]),
            ]);
    }
}
