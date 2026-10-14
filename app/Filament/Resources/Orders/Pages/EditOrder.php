<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\Actions\OrderActions;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        [$dp, $final, $full, $advance, $cancel] = OrderActions::make();

        // Status dan catatan berubah lewat aksi; segarkan form agar simpan berikutnya tidak menimpa dengan data lama.
        $refresh = fn (self $livewire) => $livewire->refreshFormData(['status', 'notes']);

        return [
            $advance->after($refresh),
            ActionGroup::make([$dp, $final, $full])->label('Invoice')->icon('heroicon-o-banknotes')->button(),
            $cancel->after($refresh),
        ];
    }
}
