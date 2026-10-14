<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\Actions\QuotationActions;
use App\Filament\Resources\Quotations\QuotationResource;
use App\Filament\Resources\Quotations\Schemas\QuotationForm;
use App\Models\Quotation;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            QuotationActions::download(),
            QuotationActions::send(),
            QuotationActions::accept(),
            QuotationActions::reject(),
            // Penawaran yang sudah jadi order tidak dihapus agar riwayatnya tetap ada.
            DeleteAction::make()->hidden(fn (Quotation $record) => $record->status === 'accepted'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Item tidak ikut dikirim saat form terkunci (field disabled).
        if (array_key_exists('items', $data)) {
            $data['items'] = QuotationForm::normalizeItems($data['items'] ?? []);
        }

        return $data;
    }
}
