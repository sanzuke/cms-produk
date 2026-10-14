<?php

namespace App\Filament\Resources\Deliveries;

use App\Mail\DeliveryReadyMail;
use App\Models\Delivery;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/** Aksi yang dipakai bersama oleh tabel dan halaman edit Hasil klien. */
class DeliveryActions
{
    public static function copyLink(): Action
    {
        return Action::make('copyLink')
            ->label('Salin link portal')
            ->icon('heroicon-o-link')
            ->color('gray')
            ->modalHeading('Link portal klien')
            ->modalDescription('Bagikan link ini ke klien. Siapa pun yang memegang link bisa membuka portal, jadi kirim hanya ke klien yang bersangkutan.')
            ->fillForm(fn (Delivery $record) => ['url' => $record->portalUrl()])
            ->schema([
                TextInput::make('url')
                    ->label('Link')
                    ->readOnly()
                    ->extraInputAttributes(['onfocus' => 'this.select()']),
            ])
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }

    public static function regenerateToken(): Action
    {
        return Action::make('regenerateToken')
            ->label('Buat ulang link')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Buat ulang link portal?')
            ->modalDescription('Link lama langsung tidak berlaku. Kirim ulang link baru ke klien.')
            ->action(function (Delivery $record): void {
                $record->update(['access_token' => Delivery::newToken()]);

                Notification::make()->title('Link baru dibuat; link lama sudah tidak berlaku')->success()->send();
            });
    }

    public static function sendReady(): Action
    {
        return Action::make('sendReady')
            ->label('Kirim email hasil siap')
            ->icon('heroicon-o-envelope')
            ->requiresConfirmation()
            ->modalDescription(fn (Delivery $record) => 'Email berisi link portal akan dikirim ke '.($record->order->client_email ?: '(email klien kosong)').'.')
            ->visible(fn (Delivery $record) => $record->isActive())
            ->action(function (Delivery $record): void {
                $order = $record->order;

                if (blank($order->client_email)) {
                    Notification::make()->title('Email klien belum diisi pada order')->danger()->send();

                    return;
                }

                try {
                    Mail::to($order->client_email, $order->client_name)->send(new DeliveryReadyMail($record));
                } catch (Throwable $e) {
                    report($e);
                    Notification::make()->title('Email gagal dikirim')->body('Periksa pengaturan SMTP, lalu coba lagi.')->danger()->send();

                    return;
                }

                Notification::make()->title("Email terkirim ke {$order->client_email}")->success()->send();
            });
    }

    public static function extend(): Action
    {
        return Action::make('extend')
            ->label('Perpanjang 30 hari')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->visible(fn (Delivery $record) => $record->expires_at !== null)
            ->action(function (Delivery $record, $livewire): void {
                $from = $record->expires_at->isFuture() ? $record->expires_at : now();
                $record->update(['expires_at' => $from->copy()->addDays(30)]);

                if (method_exists($livewire, 'refreshFormData')) {
                    $livewire->refreshFormData(['expires_at']);
                }

                Notification::make()->title('Berlaku sampai '.$record->expires_at->translatedFormat('d F Y'))->success()->send();
            });
    }

    public static function delete(): \Filament\Actions\DeleteAction
    {
        return \Filament\Actions\DeleteAction::make()
            ->after(fn (Delivery $record) => Storage::disk('local')->deleteDirectory('deliveries/'.$record->order_id));
    }
}
