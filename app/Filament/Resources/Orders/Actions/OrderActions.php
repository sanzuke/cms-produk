<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;

/** Aksi alur kerja order, dipakai bersama oleh tabel dan halaman edit. */
class OrderActions
{
    /** Urutan tahap yang boleh dimajukan (tanpa cancelled). */
    private const FLOW = ['awaiting_dp', 'scheduled', 'shooting', 'editing', 'review', 'done'];

    /** @return array<int, Action> */
    public static function make(): array
    {
        return [
            self::invoice('dp', 'Buat invoice DP', 'issueDp'),
            self::invoice('final', 'Buat invoice pelunasan', 'issueFinal'),
            self::invoice('full', 'Buat invoice penuh', 'issueFull'),
            self::advance(),
            self::cancel(),
        ];
    }

    public static function nextStatus(Order $order): ?string
    {
        $i = array_search($order->status, self::FLOW, true);

        return $i === false ? null : (self::FLOW[$i + 1] ?? null);
    }

    /** DP/invoice penuh yang sudah lunas membuka tahap "Dijadwalkan". */
    public static function hasPaidDownPayment(Order $order): bool
    {
        return $order->invoices()->where('status', 'paid')->whereIn('type', ['dp', 'full'])->exists();
    }

    private static function invoice(string $type, string $label, string $name): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon('heroicon-o-document-currency-dollar')
            ->color($type === 'dp' ? 'primary' : 'gray')
            ->requiresConfirmation()
            ->modalHeading($label)
            ->modalDescription('Invoice dibuat berdasarkan total order dan pengaturan DP.')
            ->visible(fn (Order $record): bool => $record->status !== 'cancelled'
                && ($type !== 'dp' || ! $record->invoices()->where('type', 'dp')->where('status', '!=', 'cancelled')->exists()))
            ->action(function (Order $record) use ($type): void {
                try {
                    $invoice = $record->issueInvoice($type);
                } catch (\DomainException $e) {
                    Notification::make()->danger()->title('Invoice tidak dibuat')->body($e->getMessage())->send();

                    return;
                }

                $notification = Notification::make()->success()->title("Invoice {$invoice->number} dibuat");

                if (Route::has('filament.admin.resources.invoices.edit')) {
                    $notification->actions([
                        Action::make('lihat')
                            ->label('Buka invoice')
                            ->url(route('filament.admin.resources.invoices.edit', ['record' => $invoice->id]))
                            ->button(),
                    ]);
                }

                $notification->send();
            });
    }

    private static function advance(): Action
    {
        return Action::make('advance')
            ->label('Maju ke tahap berikutnya')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('success')
            ->visible(fn (Order $record): bool => self::nextStatus($record) !== null)
            ->modalHeading(fn (Order $record): string => 'Maju ke "'.(Order::STATUSES[self::nextStatus($record)] ?? '').'"')
            ->modalDescription(fn (Order $record): ?string => self::needsOverride($record)
                ? 'Belum ada DP/pembayaran penuh yang lunas. Order hanya bisa dimajukan dengan alasan override yang akan dicatat di catatan order.'
                : null)
            ->requiresConfirmation()
            ->schema(fn (Order $record): array => self::needsOverride($record) ? [
                Textarea::make('override_reason')
                    ->label('Alasan override (DP belum lunas)')
                    ->required()
                    ->rows(3)
                    ->maxLength(500),
            ] : [])
            ->action(function (Order $record, array $data): void {
                $next = self::nextStatus($record);

                if ($next === null) {
                    return;
                }

                $reason = trim((string) ($data['override_reason'] ?? ''));

                if (self::needsOverride($record)) {
                    if ($reason === '') {
                        Notification::make()->danger()->title('Tidak bisa dimajukan')
                            ->body('DP belum lunas. Isi alasan override untuk melanjutkan.')->send();

                        return;
                    }

                    $line = sprintf('[Override DP %s oleh %s] %s', now()->translatedFormat('d F Y'), auth()->user()?->name ?? '-', $reason);
                    $record->notes = filled($record->notes) ? $record->notes."\n\n".$line : $line;
                }

                $record->status = $next;
                $record->save();

                Notification::make()->success()->title('Status order: '.Order::STATUSES[$next])->send();
            });
    }

    private static function needsOverride(Order $order): bool
    {
        return $order->status === 'awaiting_dp' && ! self::hasPaidDownPayment($order);
    }

    private static function cancel(): Action
    {
        return Action::make('cancelOrder')
            ->label('Batalkan order')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Batalkan order ini?')
            ->modalDescription('Status order menjadi "Dibatalkan" dan hilang dari kalender jadwal. Invoice yang sudah terbit tidak berubah.')
            ->visible(fn (Order $record): bool => ! in_array($record->status, ['done', 'cancelled'], true))
            ->action(function (Order $record): void {
                $record->update(['status' => 'cancelled']);

                Notification::make()->success()->title('Order dibatalkan')->send();
            });
    }
}
