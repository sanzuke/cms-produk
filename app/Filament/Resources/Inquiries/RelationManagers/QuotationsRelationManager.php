<?php

namespace App\Filament\Resources\Inquiries\RelationManagers;

use App\Filament\Resources\Quotations\QuotationResource;
use App\Models\Quotation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'quotations';

    protected static ?string $title = 'Penawaran';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Quotation $record) => QuotationResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Belum ada penawaran')
            ->emptyStateDescription('Gunakan tombol "Buat penawaran" di atas.')
            ->columns([
                TextColumn::make('number')->label('Nomor'),
                TextColumn::make('title')->label('Judul')->limit(40),
                TextColumn::make('total')->label('Total')->money('IDR', locale: 'id'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (string $state) => Quotation::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected', 'expired' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Dibuat')->since(),
            ]);
    }
}
