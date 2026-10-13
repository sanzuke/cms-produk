<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Filament\Resources\Projects\Schemas\ProjectForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('cover')->label('Cover')->disk('public')->square(),
                TextColumn::make('title')->label('Judul')->searchable()->sortable(),
                TextColumn::make('category')->label('Jenis')->badge()
                    ->formatStateUsing(fn (string $state) => ProjectForm::CATEGORIES[$state] ?? $state),
                TextColumn::make('commodity')->label('Komoditas')
                    ->formatStateUsing(fn (?string $state) => ProjectForm::COMMODITIES[$state] ?? $state),
                TextColumn::make('year')->label('Tahun')->sortable(),
                IconColumn::make('featured')->label('Unggulan')->boolean(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'published' ? 'Terbit' : 'Draft')
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('category')->label('Jenis')->options(ProjectForm::CATEGORIES),
                SelectFilter::make('commodity')->label('Komoditas')->options(ProjectForm::COMMODITIES),
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
