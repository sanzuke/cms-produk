<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\QuotationResource;
use App\Filament\Resources\Quotations\Schemas\QuotationForm;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['items'] = QuotationForm::normalizeItems($data['items'] ?? []);
        $data['created_by'] = auth()->id();

        return $data;
    }
}
