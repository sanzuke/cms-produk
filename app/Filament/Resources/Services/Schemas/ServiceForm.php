<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi utama')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
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
                        Textarea::make('summary')
                            ->label('Ringkasan (1 kalimat manfaat)')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        FileUpload::make('cover')
                            ->label('Foto utama')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('services')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),

                Section::make('Isi halaman layanan')
                    ->schema([
                        TagsInput::make('use_cases')
                            ->label('Cocok untuk (use-case)')
                            ->placeholder('Ketik lalu Enter'),
                        Repeater::make('deliverables')
                            ->label('Yang Anda terima')
                            ->simple(
                                TextInput::make('item')->required()->maxLength(255),
                            )
                            ->addActionLabel('Tambah deliverable')
                            ->reorderable(),
                        Textarea::make('requirements')
                            ->label('Proses & prasyarat lokasi')
                            ->rows(4),
                        Repeater::make('faq')
                            ->label('FAQ layanan')
                            ->schema([
                                TextInput::make('q')->label('Pertanyaan')->required(),
                                Textarea::make('a')->label('Jawaban')->rows(2)->required(),
                            ])
                            ->addActionLabel('Tambah FAQ')
                            ->collapsible()
                            ->reorderable(),
                    ]),

                Section::make('Harga & publikasi')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_from')
                            ->label('Harga mulai dari')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->helperText('Kosongkan bila harga hanya via penawaran.'),
                        Select::make('status')
                            ->options(['draft' => 'Draft', 'published' => 'Terbit'])
                            ->default('draft')
                            ->required()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),

                Section::make('SEO')
                    ->collapsed()
                    ->schema([
                        TextInput::make('seo_title')->label('Judul SEO')->maxLength(255),
                        Textarea::make('seo_description')->label('Deskripsi SEO')->rows(2)->maxLength(300),
                    ]),
            ]);
    }
}
