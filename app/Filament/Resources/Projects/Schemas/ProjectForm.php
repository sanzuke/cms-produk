<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProjectForm
{
    public const CATEGORIES = ['foto' => 'Foto', 'video' => 'Video', 'aerial' => 'Aerial / Drone'];

    public const COMMODITIES = ['batubara' => 'Batubara', 'nikel' => 'Nikel', 'emas' => 'Emas', 'lainnya' => 'Lainnya'];

    public const WORK_AREAS = [
        'pit' => 'Pit', 'hauling' => 'Hauling road', 'plant' => 'Plant',
        'pelabuhan' => 'Pelabuhan / jetty', 'reklamasi' => 'Reklamasi',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi proyek')
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
                        Select::make('category')->label('Jenis')->options(self::CATEGORIES)->required()->native(false),
                        Select::make('commodity')->label('Komoditas')->options(self::COMMODITIES)->native(false),
                        Select::make('work_area')->label('Area kerja')->options(self::WORK_AREAS)->native(false),
                        TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100),
                        TextInput::make('specs')
                            ->label('Spesifikasi pekerjaan')
                            ->placeholder('mis. 2 hari, 1 fotografer + 1 pilot drone')
                            ->columnSpanFull(),
                    ]),

                Section::make('Privasi klien')
                    ->description('Nama klien dan lokasi hanya tampil di situs publik bila toggle dinyalakan (perlu izin tertulis klien).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('client_name')->label('Nama klien'),
                        Toggle::make('show_client')->label('Tampilkan nama klien di publik')->inline(false),
                        TextInput::make('location')->label('Lokasi / site'),
                        Toggle::make('show_location')->label('Tampilkan lokasi di publik')->inline(false),
                    ]),

                Section::make('Studi kasus')
                    ->schema([
                        Textarea::make('challenge')->label('Tantangan')->rows(3),
                        Textarea::make('approach')->label('Pendekatan')->rows(3),
                        Textarea::make('result')->label('Hasil')->rows(3),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('cover')
                            ->label('Foto cover')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('projects')
                            ->maxSize(5120),
                        FileUpload::make('gallery')
                            ->label('Galeri')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('projects/gallery')
                            ->maxSize(5120)
                            ->maxFiles(30),
                    ]),

                Section::make('Publikasi')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->options(['draft' => 'Draft', 'published' => 'Terbit'])
                            ->default('draft')
                            ->required()
                            ->native(false),
                        Toggle::make('featured')->label('Unggulan (beranda)')->inline(false),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
                    ]),
            ]);
    }
}
