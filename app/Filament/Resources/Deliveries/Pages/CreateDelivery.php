<?php

namespace App\Filament\Resources\Deliveries\Pages;

use App\Filament\Resources\Deliveries\DeliveryResource;
use App\Filament\Resources\Deliveries\Schemas\DeliveryForm;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DeliveryForm::toModelData($data, null);
    }
}
