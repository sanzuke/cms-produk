<?php

namespace App\Filament\Resources\Inquiries\Tables;

use App\Models\Inquiry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Masuk')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('name')->label('Nama')->searchable()
                    ->description(fn (Inquiry $record) => $record->company),
                TextColumn::make('service.title')->label('Layanan')->placeholder('-'),
                TextColumn::make('site')->label('Site')->searchable()->toggleable(),
                TextColumn::make('needed_date')->label('Dibutuhkan')->date('d M Y')->sortable()->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Inquiry::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('assignee.name')->label('PIC')->placeholder('-')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Inquiry::STATUSES),
                SelectFilter::make('service_id')->label('Layanan')->relationship('service', 'title'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
