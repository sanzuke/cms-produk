<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceActions;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        $refresh = fn () => $this->refreshFormData(['status', 'paid_at', 'payment_ref', 'notes', 'proof_path', 'sent_at']);

        return array_map(
            fn ($action) => $action->record(fn () => $this->getRecord())->after($refresh),
            InvoiceActions::all(),
        );
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return InvoiceForm::normalize($data);
    }
}
