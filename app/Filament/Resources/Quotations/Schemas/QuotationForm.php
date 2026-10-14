<?php

namespace App\Filament\Resources\Quotations\Schemas;

use App\Models\Inquiry;
use App\Models\Quotation;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Number;

class QuotationForm
{
    /** Status final: nilai uang dan status tidak boleh diubah lagi lewat form. */
    public static function isLocked(?Quotation $record): bool
    {
        return $record !== null && in_array($record->status, ['accepted', 'rejected'], true);
    }

    public static function rupiah(int|float $amount): string
    {
        return Number::currency((int) round($amount), 'IDR', 'id', 0);
    }

    /** Model sementara dari state form, supaya hitungan live memakai rumus yang sama dengan model. */
    private static function draft(Get $get): Quotation
    {
        return new Quotation([
            'items' => array_values((array) ($get('items') ?? [])),
            'mobilization' => max(0, (int) $get('mobilization')),
            'discount' => max(0, (int) $get('discount')),
        ]);
    }

    /** Rapikan item hasil form menjadi list `{description, qty, unit, unit_price}` bertipe tegas. */
    public static function normalizeItems(array $items): array
    {
        return collect($items)->map(fn (array $item) => [
            'description' => trim((string) ($item['description'] ?? '')),
            'qty' => (float) ($item['qty'] ?? 0),
            'unit' => trim((string) ($item['unit'] ?? '')),
            'unit_price' => (int) round((float) ($item['unit_price'] ?? 0)),
        ])->values()->all();
    }

    public static function configure(Schema $schema): Schema
    {
        $locked = fn (?Quotation $record): bool => self::isLocked($record);

        return $schema
            ->columns(1)
            ->components([
                Section::make('Klien')
                    ->columns(2)
                    ->schema([
                        Select::make('inquiry_id')
                            ->label('Inquiry asal')
                            ->relationship('inquiry', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Inquiry $record) => $record->reference().' · '.$record->name)
                            ->native(false)->searchable()->preload()->live()
                            ->disabled(fn (string $operation) => $operation !== 'create')
                            ->afterStateUpdated(function ($state, Set $set): void {
                                $inquiry = Inquiry::with('service')->find($state);
                                if (! $inquiry) {
                                    return;
                                }
                                $set('client_name', $inquiry->name);
                                $set('client_company', $inquiry->company);
                                $set('client_email', $inquiry->email);
                                $set('client_phone', $inquiry->phone);
                                $set('title', $inquiry->service?->title ?: 'Penawaran dokumentasi');
                            })
                            ->columnSpanFull(),
                        TextInput::make('client_name')->label('Nama')->required()->maxLength(255),
                        TextInput::make('client_company')->label('Perusahaan')->maxLength(255),
                        TextInput::make('client_email')->label('Email')->email()->required()->maxLength(255),
                        TextInput::make('client_phone')->label('WhatsApp / Telepon')->tel()->maxLength(30),
                    ]),

                Section::make('Penawaran')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')->label('Judul pekerjaan')->required()->maxLength(255)->columnSpan(3),
                        DatePicker::make('valid_until')->label('Berlaku sampai')
                            ->default(fn () => now()->addDays((int) config('site.quotation_valid_days', 14))),
                        Select::make('status')->native(false)->required()->default('draft')
                            ->options(fn (?Quotation $record) => collect(Quotation::STATUSES)
                                ->only(['draft', 'sent', 'expired', ...($record ? [$record->status] : [])])
                                ->all())
                            ->disabled($locked)
                            ->helperText('Disetujui/Ditolak diatur lewat tombol aksi.'),
                    ]),

                Section::make('Rincian biaya')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item')
                            ->addActionLabel('Tambah item')
                            ->minItems(1)
                            ->required()
                            ->default([['description' => '', 'qty' => 1, 'unit' => 'paket', 'unit_price' => 0]])
                            ->disabled($locked)
                            ->live()
                            ->columns(12)
                            ->schema([
                                TextInput::make('description')->label('Deskripsi')->required()->maxLength(255)->columnSpan(12),
                                TextInput::make('qty')->label('Qty')->numeric()->minValue(0.01)->default(1)->required()
                                    ->live(onBlur: true)->columnSpan(3),
                                TextInput::make('unit')->label('Satuan')->required()->maxLength(30)->default('paket')->columnSpan(3),
                                TextInput::make('unit_price')->label('Harga satuan')->numeric()->integer()->minValue(0)
                                    ->prefix('Rp')->default(0)->required()->live(onBlur: true)->columnSpan(6),
                                Text::make(fn (Get $get) => 'Jumlah: '.self::rupiah((float) $get('qty') * (int) $get('unit_price')))
                                    ->weight(FontWeight::SemiBold)->columnSpan(12),
                            ]),
                        TextInput::make('mobilization')->label('Mobilisasi')->numeric()->integer()->minValue(0)
                            ->prefix('Rp')->default(0)->required()->live(onBlur: true)->disabled($locked),
                        TextInput::make('discount')->label('Diskon')->numeric()->integer()->minValue(0)
                            ->prefix('Rp')->default(0)->required()->live(onBlur: true)->disabled($locked)
                            ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                $draft = self::draft($get);
                                if ((int) $value > $draft->subtotal() + $draft->mobilization) {
                                    $fail('Diskon melebihi subtotal + mobilisasi.');
                                }
                            }),
                        Text::make(fn (Get $get) => 'Subtotal: '.self::rupiah(self::draft($get)->subtotal())),
                        Text::make(fn (Get $get) => 'Mobilisasi: '.self::rupiah(self::draft($get)->mobilization)),
                        Text::make(fn (Get $get) => 'Diskon: – '.self::rupiah(self::draft($get)->discount)),
                        Text::make(fn (Get $get) => 'TOTAL: '.self::rupiah(self::draft($get)->computeTotal()))
                            ->weight(FontWeight::Bold)->size('lg'),
                    ]),

                Section::make('Catatan & syarat')
                    ->schema([
                        Textarea::make('notes')->label('Catatan untuk klien')->rows(3),
                        Textarea::make('terms')->label('Syarat & ketentuan')->rows(8)
                            ->default(fn () => config('site.quotation_terms')),
                    ]),
            ]);
    }
}
