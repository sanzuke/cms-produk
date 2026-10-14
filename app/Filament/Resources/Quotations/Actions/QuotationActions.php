<?php

namespace App\Filament\Resources\Quotations\Actions;

use App\Filament\Resources\Quotations\QuotationResource;
use App\Mail\QuotationMail;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Quotation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Throwable;

/** Aksi penawaran yang dipakai bersama oleh tabel, halaman edit, dan inquiry. */
class QuotationActions
{
    private const OPEN = ['draft', 'sent'];

    private const SETTLEABLE = ['draft', 'sent', 'expired'];

    /**
     * Di halaman edit, simpan dulu perubahan di form agar PDF/email/persetujuan
     * selalu sesuai dengan yang terlihat di layar; lalu muat ulang record.
     */
    private static function syncUnsaved(Quotation $record, mixed $livewire): Quotation
    {
        if ($livewire instanceof EditRecord && $livewire->getRecord()->is($record)) {
            $livewire->save(shouldRedirect: false, shouldSendSavedNotification: false);
            $record = $livewire->getRecord();
        }

        return $record->refresh();
    }

    private static function refreshPage(mixed $livewire): void
    {
        if ($livewire instanceof EditRecord) {
            $livewire->refreshFormData(['status', 'sent_at']);
        }
    }

    public static function download(): Action
    {
        return Action::make('download')
            ->label('Unduh PDF')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->action(function (Quotation $record, $livewire) {
                $record = self::syncUnsaved($record, $livewire);

                return response()->streamDownload(
                    fn () => print (QuotationMail::pdf($record)->output()),
                    QuotationMail::filename($record),
                );
            });
    }

    public static function send(): Action
    {
        return Action::make('send')
            ->label('Kirim ke klien')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('info')
            ->visible(fn (Quotation $record) => in_array($record->status, self::OPEN, true))
            ->requiresConfirmation()
            ->modalHeading('Kirim penawaran ke klien?')
            ->modalDescription(fn (Quotation $record) => "PDF {$record->number} akan dikirim ke {$record->client_email}.")
            ->action(function (Quotation $record, $livewire): void {
                $record = self::syncUnsaved($record, $livewire);

                try {
                    Mail::to($record->client_email)->send(new QuotationMail($record));
                } catch (Throwable $e) {
                    report($e);
                    Notification::make()->danger()->title('Email gagal dikirim')
                        ->body('Penawaran belum dikirim dan statusnya tidak berubah. Periksa pengaturan email lalu coba lagi.')
                        ->send();

                    return;
                }

                $record->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
                self::refreshPage($livewire);

                Notification::make()->success()->title('Penawaran terkirim')
                    ->body("Dikirim ke {$record->client_email}.")->send();
            });
    }

    public static function accept(): Action
    {
        return Action::make('accept')
            ->label('Setujui & buat order')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (Quotation $record) => in_array($record->status, self::SETTLEABLE, true))
            ->requiresConfirmation()
            ->modalHeading('Setujui penawaran dan buat order?')
            ->modalDescription('Penawaran ditandai disetujui, inquiry menjadi Menang, dan sebuah order baru dibuat.')
            ->action(function (Action $action, Quotation $record, $livewire): void {
                $record = self::syncUnsaved($record, $livewire);
                $order = Order::createFromQuotation($record, auth()->user());
                self::refreshPage($livewire);

                $notification = Notification::make()->success()->title("Order {$order->number} dibuat")
                    ->body('Penawaran disetujui. Lanjutkan dengan invoice DP.');

                if (Route::has('filament.admin.resources.orders.edit')) {
                    $url = route('filament.admin.resources.orders.edit', ['record' => $order->id]);
                    $notification->actions([Action::make('openOrder')->label('Buka order')->url($url)]);
                    $action->redirect($url);
                }

                $notification->send();
            });
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label('Tolak')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Quotation $record) => in_array($record->status, self::SETTLEABLE, true))
            ->requiresConfirmation()
            ->modalHeading('Tandai penawaran ditolak?')
            ->action(function (Quotation $record, $livewire): void {
                DB::transaction(function () use ($record): void {
                    $record->forceFill(['status' => 'rejected'])->save();

                    // Inquiry dianggap kalah hanya bila tidak ada penawaran lain yang masih berjalan.
                    $inquiry = $record->inquiry;
                    if ($inquiry && $inquiry->status !== 'won' && ! $inquiry->quotations()
                        ->whereKeyNot($record->getKey())
                        ->whereIn('status', ['draft', 'sent', 'accepted'])
                        ->exists()) {
                        $inquiry->update(['status' => 'lost']);
                    }
                });
                self::refreshPage($livewire);

                Notification::make()->warning()->title('Penawaran ditandai ditolak')->send();
            });
    }

    /** Dipakai di baris tabel dan header halaman edit Inquiry. */
    public static function createFromInquiry(): Action
    {
        return Action::make('createQuotation')
            ->label('Buat penawaran')
            ->icon(Heroicon::OutlinedDocumentPlus)
            ->color('primary')
            ->visible(fn (Inquiry $record) => ! in_array($record->status, ['won', 'lost'], true))
            ->action(function (Action $action, Inquiry $record): void {
                $quotation = DB::transaction(function () use ($record): Quotation {
                    $record->loadMissing('service');
                    $title = $record->service?->title ?: 'Penawaran dokumentasi';

                    $quotation = Quotation::create([
                        'inquiry_id' => $record->id,
                        'client_name' => $record->name,
                        'client_company' => $record->company,
                        'client_email' => $record->email,
                        'client_phone' => $record->phone,
                        'title' => $title,
                        'items' => [['description' => $title, 'qty' => 1, 'unit' => 'paket', 'unit_price' => 0]],
                        'mobilization' => 0,
                        'discount' => 0,
                        'terms' => config('site.quotation_terms'),
                        'valid_until' => now()->addDays((int) config('site.quotation_valid_days', 14)),
                        'status' => 'draft',
                        'created_by' => auth()->id(),
                    ]);

                    if (in_array($record->status, ['new', 'contacted', 'survey'], true)) {
                        $record->update(['status' => 'quoted']);
                    }

                    return $quotation;
                });

                $action->redirect(QuotationResource::getUrl('edit', ['record' => $quotation]));
            });
    }
}
