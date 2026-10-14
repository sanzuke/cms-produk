<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Concerns\SalesOnly;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    use SalesOnly;

    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Penjualan';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Invoice';

    protected static ?string $pluralModelLabel = 'Invoice';

    protected static ?string $recordTitleAttribute = 'number';

    /** Jumlah invoice yang bukti bayarnya menunggu diperiksa. */
    public static function getNavigationBadge(): ?string
    {
        $count = Invoice::where('status', 'proof_submitted')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
}
