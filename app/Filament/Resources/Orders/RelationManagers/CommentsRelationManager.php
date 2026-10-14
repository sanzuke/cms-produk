<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Komentar klien';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('body')->label('Balasan')->required()->rows(4)->maxLength(2000)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('author')
                    ->label('Pengirim')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'client' ? 'Klien' : 'Staf')
                    ->color(fn (string $state): string => $state === 'client' ? 'info' : 'success'),
                TextColumn::make('author_name')->label('Nama'),
                TextColumn::make('body')->label('Pesan')->wrap(),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Balas sebagai staf')
                    ->modalHeading('Balas klien')
                    ->createAnother(false)
                    ->mutateDataUsing(fn (array $data): array => $data + [
                        'author' => 'staff',
                        'author_name' => auth()->user()?->name ?? 'Staf',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
