<?php

namespace App\Filament\Resources\Deliveries\Pages;

use App\Filament\Resources\Deliveries\DeliveryActions;
use App\Filament\Resources\Deliveries\DeliveryResource;
use App\Filament\Resources\Deliveries\Schemas\DeliveryForm;
use Filament\Resources\Pages\EditRecord;

class EditDelivery extends EditRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeliveryActions::copyLink(),
            DeliveryActions::sendReady(),
            DeliveryActions::extend(),
            DeliveryActions::regenerateToken(),
            DeliveryActions::delete(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return DeliveryForm::toFormData($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return DeliveryForm::toModelData($data, $this->getRecord());
    }
}
