<?php

namespace App\Filament\Resources\Invoices;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/** Aksi invoice yang dipakai bersama oleh tabel dan halaman edit. */
class InvoiceActions
{
    /** @return array<int, Action> */
    public static function all(): array
    {
        return [
            self::markPaid(),
            self::rejectProof(),
            self::send(),
            self::downloadPdf(),
            self::cancel(),
        ];
    }

    public static function markPaid(): Action
    {
        return Action::make('markPaid')
            ->label('Tandai lunas')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Invoice $record): bool => in_array($record->status, ['unpaid', 'proof_submitted'], true))
            ->modalHeading('Tandai invoice lunas')
            ->modalSubmitActionLabel('Tandai lunas')
            ->fillForm(fn (Invoice $record): array => ['payment_ref' => $record->payment_ref])
            ->schema([
                TextInput::make('payment_ref')->label('Referensi pembayaran (opsional)')->maxLength(255),
            ])
            ->action(function (Invoice $record, array $data): void {
                $orderBefore = $record->order()->value('status');

                $record->markPaid(filled($data['payment_ref'] ?? null) ? $data['payment_ref'] : null);

                $scheduled = $orderBefore === 'awaiting_dp' && $record->order()->value('status') === 'scheduled';

                Notification::make()
                    ->success()
                    ->title("Invoice {$record->number} ditandai lunas")
                    ->body($scheduled ? 'Order otomatis berpindah ke status Dijadwalkan.' : null)
                    ->send();
            });
    }

    public static function rejectProof(): Action
    {
        return Action::make('rejectProof')
            ->label('Tolak bukti')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Invoice $record): bool => $record->status === 'proof_submitted')
            ->modalHeading('Tolak bukti pembayaran')
            ->modalDescription('Bukti yang diunggah akan dihapus dan invoice kembali berstatus belum dibayar.')
            ->modalSubmitActionLabel('Tolak bukti')
            ->schema([
                Textarea::make('reason')->label('Alasan penolakan')->required()->rows(3)->maxLength(1000),
            ])
            ->action(function (Invoice $record, array $data): void {
                if ($record->proof_path) {
                    Storage::disk('local')->delete($record->proof_path);
                }

                $note = '['.now()->translatedFormat('d F Y').'] Bukti pembayaran ditolak: '.$data['reason'];

                $record->update([
                    'status' => 'unpaid',
                    'proof_path' => null,
                    'notes' => filled($record->notes) ? $record->notes."\n".$note : $note,
                ]);

                Notification::make()->success()->title("Bukti pembayaran {$record->number} ditolak")->send();
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancelInvoice')
            ->label('Batalkan invoice')
            ->icon('heroicon-o-no-symbol')
            ->color('gray')
            ->visible(fn (Invoice $record): bool => in_array($record->status, ['unpaid', 'proof_submitted'], true))
            ->requiresConfirmation()
            ->modalHeading('Batalkan invoice?')
            ->modalDescription('Invoice yang dibatalkan tidak dapat dibayar lagi dan tidak dihitung sebagai tagihan.')
            ->action(function (Invoice $record): void {
                if (! in_array($record->status, ['unpaid', 'proof_submitted'], true)) {
                    return;
                }

                $record->update(['status' => 'cancelled']);

                Notification::make()->success()->title("Invoice {$record->number} dibatalkan")->send();
            });
    }

    public static function downloadPdf(): Action
    {
        return Action::make('downloadPdf')
            ->label('Unduh PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(fn (Invoice $record) => response()->streamDownload(
                fn () => print(InvoiceMail::renderPdf($record)->output()),
                InvoiceMail::pdfFilename($record),
            ));
    }

    public static function send(): Action
    {
        return Action::make('sendToClient')
            ->label('Kirim ke klien')
            ->icon('heroicon-o-paper-airplane')
            ->visible(fn (Invoice $record): bool => $record->status !== 'cancelled')
            ->requiresConfirmation()
            ->modalHeading('Kirim invoice ke klien?')
            ->modalDescription(fn (Invoice $record): string => 'Invoice beserta PDF akan dikirim ke '.($record->order?->client_email ?: '(email klien belum diisi)').'.')
            ->action(function (Invoice $record): void {
                $email = $record->order?->client_email;

                if (blank($email)) {
                    Notification::make()->danger()->title('Email klien belum diisi pada order')->send();

                    return;
                }

                try {
                    Mail::to($email)->send(new InvoiceMail($record));
                } catch (Throwable $e) {
                    report($e);
                    Notification::make()->danger()->title('Email gagal dikirim')->body('Periksa pengaturan email lalu coba lagi.')->send();

                    return;
                }

                $record->update(['sent_at' => now()]);

                Notification::make()->success()->title("Invoice {$record->number} terkirim ke {$email}")->send();
            });
    }
}
