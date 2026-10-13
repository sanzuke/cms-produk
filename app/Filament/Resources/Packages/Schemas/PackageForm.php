<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Paket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama paket')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, ?string $state, Set $set) => $operation === 'create'
                                ? $set('slug', Str::slug((string) $state))
                                : null),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        Textarea::make('description')
                            ->label('Deskripsi singkat')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Harga')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->helperText('Kosongkan untuk paket custom ("Hubungi kami").'),
                        TextInput::make('price_unit')
                            ->label('Satuan')
                            ->placeholder('mis. / bulan, / hari')
                            ->maxLength(50),
                        TextInput::make('price_note')
                            ->label('Catatan harga')
                            ->placeholder('mis. Belum termasuk mobilisasi lokasi terpencil')
                            ->maxLength(255),
                    ]),

                Section::make('Isi paket')
                    ->schema([
                        Repeater::make('features')
                            ->label('Fitur / isi paket')
                            ->simple(
                                TextInput::make('item')->required()->maxLength(255),
                            )
                            ->addActionLabel('Tambah fitur')
                            ->reorderable(),
                    ]),

                Section::make('Tampilan')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_popular')->label('Label "Populer"'),
                        Toggle::make('is_active')->label('Aktif (tampil di situs)')->default(true),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
                    ]),
            ]);
    }
}
