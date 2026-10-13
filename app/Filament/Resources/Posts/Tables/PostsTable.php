<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Posts\Schemas\PostForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('cover')->label('Cover')->disk('public')->square(),
                TextColumn::make('title')->label('Judul')->searchable()->limit(60),
                TextColumn::make('type')->label('Tipe')->badge()
                    ->formatStateUsing(fn (string $state) => PostForm::TYPES[$state] ?? $state),
                TextColumn::make('category')->label('Kategori')->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'published' ? 'Terbit' : 'Draft')
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('publish_at')->label('Terbit')->dateTime('d M Y H:i')->sortable()->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipe')->options(PostForm::TYPES),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Terbit']),
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
