<?php

namespace App\Support;

use App\Mail\InquiryReceivedMail;
use App\Mail\NewInquiryMail;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InquiryNotifier
{
    /**
     * Kirim notifikasi setelah respons ke pengunjung terkirim: tidak butuh queue worker
     * (cocok shared hosting) dan kegagalan SMTP tidak membuat form gagal; hanya dilaporkan ke log.
     */
    public static function notifyStaff(Inquiry $inquiry): void
    {
        $recipients = self::recipients();

        if ($recipients === []) {
            return;
        }

        dispatch(function () use ($inquiry, $recipients): void {
            try {
                Mail::to($recipients)->send(new NewInquiryMail($inquiry));
            } catch (Throwable $e) {
                report($e);
            }
        })->afterResponse();
    }

    /** Konfirmasi ke calon klien; pola pengiriman sama dengan notifyStaff (setelah respons, gagal = log). */
    public static function confirmToRequester(Inquiry $inquiry): void
    {
        dispatch(function () use ($inquiry): void {
            try {
                Mail::to($inquiry->email, $inquiry->name)->send(new InquiryReceivedMail($inquiry));
            } catch (Throwable $e) {
                report($e);
            }
        })->afterResponse();
    }

    /** @return list<string> Penerima dari pengaturan; fallback ke email kontak situs. */
    public static function recipients(): array
    {
        $configured = array_values(array_filter((array) config('site.notification_emails'), 'filled'));

        if ($configured !== []) {
            return $configured;
        }

        return filled(config('site.email')) ? [config('site.email')] : [];
    }
}
