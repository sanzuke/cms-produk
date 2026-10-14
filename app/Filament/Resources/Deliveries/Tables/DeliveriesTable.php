<?php

namespace App\Filament\Resources\Deliveries\Tables;

use App\Filament\Resources\Deliveries\DeliveryActions;
use App\Models\Delivery;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveriesTable
{
    public static function status(Delivery $delivery): string
    {
        if ($delivery->expires_at !== null && $delivery->expires_at->isPast()) {
            return 'Kedaluwarsa';
        }

        return $delivery->isActive() ? 'Aktif' : 'Draft';
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('order'))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order.number')->label('Order')->searchable()->sortable(),
                TextColumn::make('order.client_name')->label('Klien')->searchable(),
                TextColumn::make('files')
                    ->label('Berkas')
                    ->state(fn (Delivery $d) => count($d->files ?? []))
                    ->suffix(' berkas'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Delivery $d) => self::status($d))
                    ->color(fn (string $state) => match ($state) {
                        'Aktif' => 'success',
                        'Kedaluwarsa' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('expires_at')->label('Kedaluwarsa')->date('d M Y')->sortable()->placeholder('Tanpa batas'),
                TextColumn::make('download_count')->label('Unduhan')->numeric()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    DeliveryActions::copyLink(),
                    DeliveryActions::sendReady(),
                    DeliveryActions::extend(),
                    DeliveryActions::regenerateToken(),
                    DeliveryActions::delete(),
                ]),
            ]);
    }
}
