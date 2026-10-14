<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        // Invoice lunas/dibatalkan: nominal & rujukan tidak boleh diubah lagi.
        $locked = fn (?Invoice $record): bool => in_array($record?->status, ['paid', 'cancelled'], true);

        return $schema->components([
            Section::make('Tagihan')->columns(2)->schema([
                Select::make('order_id')
                    ->label('Order')
                    ->relationship('order', 'number')
                    ->getOptionLabelFromRecordUsing(fn (Order $order): string => $order->number.' — '.$order->client_name.($order->client_company ? " ({$order->client_company})" : ''))
                    ->searchable(['number', 'client_name', 'client_company', 'title'])
                    ->preload()
                    ->required()
                    ->disabledOn('edit')
                    ->columnSpanFull(),
                Select::make('type')
                    ->label('Jenis')
                    ->options(Invoice::TYPES)
                    ->required()
                    ->default('dp')
                    ->disabled($locked),
                TextInput::make('amount')
                    ->label('Jumlah')
                    ->prefix('Rp')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->required()
                    ->disabled($locked),
                DatePicker::make('due_date')
                    ->label('Jatuh tempo')
                    ->disabled($locked),
                Select::make('status')
                    ->label('Status')
                    ->options(Invoice::STATUSES)
                    ->required()
                    ->default('unpaid'),
            ]),
            Section::make('Pembayaran')->columns(2)->schema([
                TextInput::make('payment_ref')->label('Referensi pembayaran')->maxLength(255),
                FileUpload::make('proof_path')
                    ->label('Bukti pembayaran')
                    ->disk('local')
                    ->directory('payment-proofs')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120)
                    ->downloadable()
                    ->openable(),
                Textarea::make('notes')->label('Catatan')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    /** Status "lunas" yang diset lewat form tetap mencatat waktu bayar; selain itu waktu bayar dikosongkan. */
    public static function normalize(array $data): array
    {
        if (($data['status'] ?? null) === 'paid') {
            $data['paid_at'] ??= now();
        } elseif (array_key_exists('status', $data)) {
            $data['paid_at'] = null;
        }

        return $data;
    }
}
