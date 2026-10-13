<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public const TYPES = ['article' => 'Artikel', 'news' => 'Berita'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Konten')
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
                        Select::make('type')->label('Tipe')->options(self::TYPES)->default('article')->required()->native(false),
                        TextInput::make('category')->label('Kategori')->maxLength(60),
                        Textarea::make('excerpt')->label('Ringkasan')->rows(2)->maxLength(500)->columnSpanFull(),
                        FileUpload::make('cover')
                            ->label('Cover')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('posts')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                        RichEditor::make('body')->label('Isi')->columnSpanFull(),
                    ]),

                Section::make('Publikasi')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options(['draft' => 'Draft', 'published' => 'Terbit'])
                            ->default('draft')
                            ->required()
                            ->native(false),
                        DateTimePicker::make('publish_at')
                            ->label('Jadwal terbit')
                            ->helperText('Kosong = langsung terbit saat status Terbit. Isi tanggal mendatang untuk menjadwalkan.'),
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
