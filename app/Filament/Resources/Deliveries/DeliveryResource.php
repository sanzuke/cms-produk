<?php

namespace App\Filament\Resources\Deliveries;

use App\Filament\Concerns\SalesOnly;
use App\Filament\Resources\Deliveries\Pages\CreateDelivery;
use App\Filament\Resources\Deliveries\Pages\EditDelivery;
use App\Filament\Resources\Deliveries\Pages\ListDeliveries;
use App\Filament\Resources\Deliveries\Schemas\DeliveryForm;
use App\Filament\Resources\Deliveries\Tables\DeliveriesTable;
use App\Models\Delivery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliveryResource extends Resource
{
    use SalesOnly;

    protected static ?string $model = Delivery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Penjualan';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Hasil klien';

    protected static ?string $modelLabel = 'Hasil klien';

    protected static ?string $pluralModelLabel = 'Hasil klien';

    public static function form(Schema $schema): Schema
    {
        return DeliveryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveries::route('/'),
            'create' => CreateDelivery::route('/create'),
            'edit' => EditDelivery::route('/{record}/edit'),
        ];
    }
}
