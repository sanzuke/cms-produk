<?php

namespace Tests\Feature;

use App\Mail\InquiryReceivedMail;
use App\Mail\NewInquiryMail;
use App\Models\Inquiry;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        $service = Service::firstOrCreate(['slug' => 's'], ['title' => 'Aerial Drone', 'status' => 'published']);

        return $override + [
            'name' => 'Budi', 'company' => 'PT Contoh', 'email' => 'budi@contoh.test', 'phone' => '081234567890',
            'site' => 'Site A', 'needed_date' => now()->addWeek()->toDateString(),
            'service_id' => $service->id, 'consent' => '1', 'brief' => 'Butuh foto udara pit.',
        ];
    }

    public function test_staff_are_notified_with_reply_to_set_to_requester(): void
    {
        Mail::fake();
        config(['site.notification_emails' => ['sales@brand.test', 'owner@brand.test']]);

        $this->post('/pesan', $this->payload())->assertRedirect(route('order.thanks'));

        Mail::assertSent(NewInquiryMail::class, function (NewInquiryMail $mail) {
            return $mail->hasTo('sales@brand.test')
                && $mail->hasTo('owner@brand.test')
                && $mail->hasReplyTo('budi@contoh.test')
                && str_contains($mail->render(), 'Butuh foto udara pit.')
                && str_contains($mail->render(), 'Aerial Drone');
        });
    }

    public function test_falls_back_to_contact_email_when_no_recipients_configured(): void
    {
        Mail::fake();
        config(['site.notification_emails' => [], 'site.email' => 'kontak@brand.test']);

        $this->post('/pesan', $this->payload());

        Mail::assertSent(NewInquiryMail::class, fn ($mail) => $mail->hasTo('kontak@brand.test'));
    }

    public function test_requester_gets_confirmation_with_reference_number(): void
    {
        Mail::fake();
        config(['site.notification_emails' => ['sales@brand.test']]);

        $this->post('/pesan', $this->payload());

        $ref = Inquiry::firstOrFail()->reference();
        Mail::assertSent(InquiryReceivedMail::class, function (InquiryReceivedMail $mail) use ($ref) {
            return $mail->hasTo('budi@contoh.test')
                && str_contains($mail->render(), $ref)
                && str_contains($mail->envelope()->subject, $ref);
        });
        // Email internal tidak boleh sampai ke klien.
        Mail::assertSent(NewInquiryMail::class, fn ($m) => ! $m->hasTo('budi@contoh.test'));
    }

    public function test_mail_failure_does_not_break_submission(): void
    {
        config(['site.notification_emails' => ['sales@brand.test']]);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->post('/pesan', $this->payload())->assertRedirect(route('order.thanks'));
        $this->assertSame(1, Inquiry::count());
    }

    public function test_no_mail_for_invalid_or_honeypot_submissions(): void
    {
        Mail::fake();

        $this->post('/pesan', ['name' => ''])->assertSessionHasErrors();
        $this->post('/pesan', $this->payload(['website' => 'spam']));

        Mail::assertNothingSent();
    }
}
