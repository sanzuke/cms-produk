<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Number;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data klien')
                    ->columns(2)
                    ->schema([
                        TextInput::make('client_name')->label('Nama klien')->required()->maxLength(255),
                        TextInput::make('client_company')->label('Perusahaan')->maxLength(255),
                        TextInput::make('client_email')->label('Email')->email()->required()->maxLength(255),
                        TextInput::make('client_phone')->label('Telepon / WhatsApp')->tel()->maxLength(30),
                    ]),

                Section::make('Proyek')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('Judul proyek')->required()->maxLength(255)->columnSpanFull(),
                        TextInput::make('site')->label('Site / lokasi')->maxLength(255)->columnSpanFull(),
                        DatePicker::make('scheduled_start')->label('Mulai')->native(false)->displayFormat('d M Y')->live(),
                        DatePicker::make('scheduled_end')->label('Selesai')->native(false)->displayFormat('d M Y')
                            ->afterOrEqual('scheduled_start'),
                        Textarea::make('notes')->label('Catatan')->rows(4)->columnSpanFull(),
                    ]),

                Section::make('Status & pembayaran')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(Order::STATUSES)
                            ->default('awaiting_dp')
                            ->required()
                            ->native(false),
                        TextInput::make('total')
                            ->label('Total (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->disabled(fn (?Order $record): bool => filled($record?->quotation_id))
                            ->helperText(fn (?Order $record): ?string => filled($record?->quotation_id)
                                ? 'Total mengikuti penawaran dan tidak dapat diubah di sini.'
                                : 'Isi manual untuk order tanpa penawaran.'),
                        TextEntry::make('quotation_link')
                            ->label('Penawaran')
                            ->state(fn (Order $record): string => $record->quotation?->number ?? '-')
                            ->url(fn (Order $record): ?string => $record->quotation_id && Route::has('filament.admin.resources.quotations.edit')
                                ? route('filament.admin.resources.quotations.edit', ['record' => $record->quotation_id])
                                : null)
                            ->visible(fn (?Order $record): bool => filled($record?->quotation_id)),
                        TextEntry::make('inquiry_link')
                            ->label('Inquiry')
                            ->state(fn (Order $record): string => $record->inquiry?->reference() ?? '-')
                            ->url(fn (Order $record): ?string => $record->inquiry_id && Route::has('filament.admin.resources.inquiries.edit')
                                ? route('filament.admin.resources.inquiries.edit', ['record' => $record->inquiry_id])
                                : null)
                            ->visible(fn (?Order $record): bool => filled($record?->inquiry_id)),
                        TextEntry::make('paid_info')
                            ->label('Terbayar / sisa')
                            ->state(fn (Order $record): string => Number::currency($record->paidAmount(), 'IDR', 'id', 0)
                                .' / '.Number::currency($record->outstanding(), 'IDR', 'id', 0))
                            ->visible(fn (?Order $record): bool => $record !== null),
                    ]),

                Section::make('Checklist persiapan')
                    ->schema([
                        Repeater::make('checklist')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('label')->label('Tugas')->required()->maxLength(255)->columnSpan(3),
                                Toggle::make('done')->label('Selesai')->default(false)->inline(false),
                            ])
                            ->columns(4)
                            ->reorderable()
                            ->addActionLabel('Tambah item')
                            ->default(fn (): array => array_map(fn (string $l): array => ['label' => $l, 'done' => false], Order::DEFAULT_CHECKLIST)),
                    ]),
            ]);
    }
}
