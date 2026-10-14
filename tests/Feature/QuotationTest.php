<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Inquiries\Pages\EditInquiry;
use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Filament\Resources\Inquiries\RelationManagers\QuotationsRelationManager;
use App\Filament\Resources\Quotations\Pages\CreateQuotation;
use App\Filament\Resources\Quotations\Pages\EditQuotation;
use App\Filament\Resources\Quotations\Pages\ListQuotations;
use App\Mail\QuotationMail;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    private function sales(): User
    {
        return tap(User::factory()->role(Role::Sales)->create(), fn ($u) => $this->actingAs($u));
    }

    private function quotation(array $o = []): Quotation
    {
        return Quotation::create($o + [
            'client_name' => 'Budi', 'client_company' => 'PT Contoh', 'client_email' => 'budi@contoh.test', 'title' => 'Profil tambang',
            'items' => [
                ['description' => 'Hari kerja foto', 'qty' => 2, 'unit' => 'hari', 'unit_price' => 15_000_000],
                ['description' => 'Video 3 menit', 'qty' => 1, 'unit' => 'paket', 'unit_price' => 40_000_000],
            ],
            'mobilization' => 5_000_000, 'discount' => 1_000_000,
            'valid_until' => now()->addDays(14), 'status' => 'draft',
        ]);
    }

    public function test_pdf_renders_with_number_total_and_bank_block(): void
    {
        config(['site.bank_name' => 'Bank Uji', 'site.bank_account' => '1234567890', 'site.bank_holder' => 'PT Strata']);
        $q = $this->quotation();

        $html = view('pdf.quotation', ['quotation' => $q])->render();
        $this->assertStringContainsString($q->number, $html);
        $this->assertStringContainsString("Rp\u{00A0}74.000.000", $html);
        $this->assertStringContainsString('1234567890', $html);

        $output = QuotationMail::pdf($q)->output();
        $this->assertStringStartsWith('%PDF', $output);
        $this->assertSame('QUO-'.now()->format('Y').'-0001.pdf', QuotationMail::filename($q));
    }

    public function test_pdf_omits_bank_block_when_not_configured(): void
    {
        config(['site.bank_account' => '']);

        $html = view('pdf.quotation', ['quotation' => $this->quotation()])->render();

        $this->assertStringNotContainsString('Transfer ke', $html);
    }

    public function test_download_action_streams_pdf(): void
    {
        $this->sales();
        $q = $this->quotation();

        Livewire::test(ListQuotations::class)
            ->callTableAction('download', $q)
            ->assertFileDownloaded($q->number.'.pdf');
    }

    public function test_send_action_emails_pdf_and_marks_sent(): void
    {
        Mail::fake();
        $this->sales();
        $q = $this->quotation();

        Livewire::test(ListQuotations::class)->callTableAction('send', $q);

        Mail::assertSent(QuotationMail::class, function (QuotationMail $mail) use ($q) {
            $attachment = $mail->attachments()[0];

            return $mail->hasTo('budi@contoh.test')
                && $attachment->as === $q->number.'.pdf'
                && $attachment->mime === 'application/pdf'
                && str_starts_with($attachment->attachWith(fn () => '', fn ($data) => $data()), '%PDF');
        });
        $q->refresh();
        $this->assertSame('sent', $q->status);
        $this->assertNotNull($q->sent_at);
    }

    public function test_send_failure_keeps_status_and_shows_error(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $this->sales();
        $q = $this->quotation();

        Livewire::test(ListQuotations::class)
            ->callTableAction('send', $q)
            ->assertNotified('Email gagal dikirim');

        $this->assertSame('draft', $q->fresh()->status);
        $this->assertNull($q->fresh()->sent_at);
    }

    public function test_actions_are_hidden_when_not_applicable(): void
    {
        $this->sales();
        $accepted = $this->quotation(['status' => 'accepted']);
        $rejected = $this->quotation(['status' => 'rejected']);
        $sent = $this->quotation(['status' => 'sent']);

        Livewire::test(ListQuotations::class)
            ->assertTableActionHidden('send', $accepted)
            ->assertTableActionHidden('accept', $accepted)
            ->assertTableActionHidden('reject', $accepted)
            ->assertTableActionHidden('send', $rejected)
            ->assertTableActionHidden('accept', $rejected)
            ->assertTableActionVisible('send', $sent)
            ->assertTableActionVisible('accept', $sent)
            ->assertTableActionVisible('reject', $sent)
            ->assertTableActionVisible('download', $accepted);
    }

    public function test_accept_creates_exactly_one_order(): void
    {
        $user = $this->sales();
        $inquiry = Inquiry::create(['name' => 'Budi', 'email' => 'budi@contoh.test', 'status' => 'quoted']);
        $q = $this->quotation(['inquiry_id' => $inquiry->id, 'status' => 'sent']);

        Livewire::test(ListQuotations::class)->callTableAction('accept', $q);

        $this->assertSame(1, Order::count());
        $order = Order::first();
        $this->assertSame($q->id, $order->quotation_id);
        $this->assertSame($user->id, $order->created_by);
        $this->assertSame(74_000_000, $order->total);
        $this->assertSame('accepted', $q->fresh()->status);
        $this->assertSame('won', $inquiry->fresh()->status);

        // Klik kedua tidak mungkin: aksi sudah tersembunyi dan order tetap satu.
        Livewire::test(ListQuotations::class)->assertTableActionHidden('accept', $q->fresh());
        $this->assertSame($order->id, Order::sole()->id);
    }

    public function test_reject_marks_inquiry_lost_only_without_other_open_quotation(): void
    {
        $this->sales();
        $inquiry = Inquiry::create(['name' => 'Budi', 'email' => 'budi@contoh.test', 'status' => 'quoted']);
        $first = $this->quotation(['inquiry_id' => $inquiry->id, 'status' => 'sent']);
        $second = $this->quotation(['inquiry_id' => $inquiry->id, 'status' => 'sent']);

        $list = Livewire::test(ListQuotations::class)->callTableAction('reject', $first);
        $this->assertSame('rejected', $first->fresh()->status);
        $this->assertSame('quoted', $inquiry->fresh()->status, 'masih ada penawaran lain yang berjalan');

        $list->callTableAction('reject', $second);
        $this->assertSame('lost', $inquiry->fresh()->status);
    }

    public function test_reject_does_not_touch_won_inquiry_or_standalone_quotation(): void
    {
        $this->sales();
        $won = Inquiry::create(['name' => 'Ani', 'email' => 'ani@contoh.test', 'status' => 'won']);
        $a = $this->quotation(['inquiry_id' => $won->id, 'status' => 'sent']);
        $standalone = $this->quotation(['status' => 'draft']);

        Livewire::test(ListQuotations::class)
            ->callTableAction('reject', $a)
            ->callTableAction('reject', $standalone);

        $this->assertSame('won', $won->fresh()->status);
        $this->assertSame('rejected', $standalone->fresh()->status);
    }

    public function test_create_quotation_from_inquiry_prefills_and_moves_to_quoted(): void
    {
        $user = $this->sales();
        $service = Service::create(['slug' => 'aerial', 'title' => 'Fotografi Aerial', 'summary' => 's', 'body' => 'b', 'status' => 'published']);
        $inquiry = Inquiry::create([
            'name' => 'Budi', 'company' => 'PT Contoh', 'email' => 'budi@contoh.test', 'phone' => '0812',
            'service_id' => $service->id, 'status' => 'survey',
        ]);

        Livewire::test(EditInquiry::class, ['record' => $inquiry->getRouteKey()])
            ->callAction('createQuotation')
            ->assertRedirect();

        $q = Quotation::sole();
        $this->assertSame($inquiry->id, $q->inquiry_id);
        $this->assertSame(['Budi', 'PT Contoh', 'budi@contoh.test', '0812'], [$q->client_name, $q->client_company, $q->client_email, $q->client_phone]);
        $this->assertSame('Fotografi Aerial', $q->title);
        $this->assertSame('draft', $q->status);
        $this->assertSame($user->id, $q->created_by);
        $this->assertSame(config('site.quotation_terms'), $q->terms);
        $this->assertTrue($q->valid_until->isSameDay(now()->addDays(config('site.quotation_valid_days'))));
        $this->assertNotNull($q->number);
        $this->assertSame('quoted', $inquiry->fresh()->status);
    }

    public function test_create_from_inquiry_uses_default_title_and_keeps_later_statuses(): void
    {
        $this->sales();
        $new = Inquiry::create(['name' => 'Budi', 'email' => 'b@contoh.test', 'status' => 'new']);
        $won = Inquiry::create(['name' => 'Won', 'email' => 'w@contoh.test', 'status' => 'won']);
        $quoted = Inquiry::create(['name' => 'Quo', 'email' => 'q@contoh.test', 'status' => 'quoted']);

        Livewire::test(ListInquiries::class)
            ->assertTableActionHidden('createQuotation', $won)
            ->callTableAction('createQuotation', $new)
            ->callTableAction('createQuotation', $quoted);

        $this->assertSame('Penawaran dokumentasi', Quotation::where('inquiry_id', $new->id)->value('title'));
        $this->assertSame('quoted', $new->fresh()->status);
        $this->assertSame('quoted', $quoted->fresh()->status);
        $this->assertSame(0, Quotation::where('inquiry_id', $won->id)->count());
    }

    public function test_inquiry_page_lists_its_quotations(): void
    {
        $this->sales();
        $inquiry = Inquiry::create(['name' => 'Budi', 'email' => 'b@contoh.test']);
        $mine = $this->quotation(['inquiry_id' => $inquiry->id]);
        $other = $this->quotation();

        Livewire::test(QuotationsRelationManager::class, ['ownerRecord' => $inquiry, 'pageClass' => EditInquiry::class])
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_form_creates_quotation_with_normalized_items_and_total(): void
    {
        $user = $this->sales();

        Livewire::test(CreateQuotation::class)
            ->fillForm([
                'client_name' => 'Budi', 'client_email' => 'budi@contoh.test', 'title' => 'Dokumentasi',
                'items' => [
                    ['description' => 'Foto', 'qty' => '2.5', 'unit' => 'hari', 'unit_price' => '1000000'],
                    ['description' => 'Video', 'qty' => '1', 'unit' => 'paket', 'unit_price' => '500000'],
                ],
                'mobilization' => 100_000, 'discount' => 50_000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $q = Quotation::sole();
        $this->assertSame(3_050_000, $q->total);
        $this->assertSame(2.5, $q->items[0]['qty']);
        $this->assertSame(1_000_000, $q->items[0]['unit_price']);
        $this->assertTrue(array_is_list($q->items));
        $this->assertSame($user->id, $q->created_by);
        $this->assertSame(config('site.quotation_terms'), $q->terms);
    }

    public function test_form_defaults_and_validation(): void
    {
        $this->sales();

        Livewire::test(CreateQuotation::class)
            ->assertFormSet(['terms' => config('site.quotation_terms'), 'status' => 'draft'])
            ->fillForm(['client_name' => 'Budi', 'client_email' => 'bukan-email', 'title' => ''])
            ->call('create')
            ->assertHasFormErrors(['client_email', 'title']);

        Livewire::test(CreateQuotation::class)
            ->fillForm([
                'client_name' => 'Budi', 'client_email' => 'budi@contoh.test', 'title' => 'X',
                'items' => [['description' => 'Foto', 'qty' => 1, 'unit' => 'hari', 'unit_price' => 1000]],
                'discount' => 5000,
            ])
            ->call('create')
            ->assertHasFormErrors(['discount']);

        $this->assertSame(0, Quotation::count());
    }

    public function test_accepted_quotation_money_fields_cannot_be_changed(): void
    {
        $this->sales();
        $q = $this->quotation(['status' => 'accepted']);

        Livewire::test(EditQuotation::class, ['record' => $q->getRouteKey()])
            ->assertFormFieldDisabled('items')
            ->assertFormFieldDisabled('mobilization')
            ->assertFormFieldDisabled('discount')
            ->assertFormFieldDisabled('status')
            ->fillForm(['mobilization' => 99_000_000, 'notes' => 'Catatan baru'])
            ->call('save');

        $q->refresh();
        $this->assertSame(5_000_000, $q->mobilization);
        $this->assertSame(74_000_000, $q->total);
        $this->assertSame('accepted', $q->status);
        $this->assertSame('Catatan baru', $q->notes);
    }

    public function test_edit_page_actions_save_form_changes_before_sending(): void
    {
        Mail::fake();
        $this->sales();
        $q = $this->quotation();

        Livewire::test(EditQuotation::class, ['record' => $q->getRouteKey()])
            ->fillForm(['mobilization' => 6_000_000])
            ->callAction('send');

        $q->refresh();
        $this->assertSame(6_000_000, $q->mobilization);
        $this->assertSame(75_000_000, $q->total);
        $this->assertSame('sent', $q->status);
        Mail::assertSent(QuotationMail::class, fn (QuotationMail $m) => $m->quotation->total === 75_000_000);
    }

    public function test_expired_is_display_only(): void
    {
        $this->sales();
        $old = $this->quotation(['valid_until' => now()->subDays(3), 'status' => 'sent']);
        $fresh = $this->quotation(['status' => 'sent']);

        $this->assertTrue($old->isExpired());
        Livewire::test(ListQuotations::class)
            ->assertCanSeeTableRecords([$old, $fresh])
            ->assertTableColumnFormattedStateSet('status', 'Kedaluwarsa', $old)
            ->assertTableColumnFormattedStateSet('status', 'Terkirim', $fresh)
            ->filterTable('lewat_masa_berlaku')
            ->assertCanSeeTableRecords([$old])
            ->assertCanNotSeeTableRecords([$fresh]);
        $this->assertSame('sent', $old->fresh()->status);
    }

    public function test_editor_is_forbidden_and_sales_allowed(): void
    {
        $this->actingAs(User::factory()->role(Role::Editor)->create());
        $this->get('/admin/quotations')->assertForbidden();

        $this->actingAs(User::factory()->role(Role::Sales)->create());
        $this->get('/admin/quotations')->assertOk();
        $this->get('/admin/quotations/create')->assertOk();

        $this->actingAs(User::factory()->role(Role::Owner)->create());
        $this->get('/admin/quotations')->assertOk();
    }
}
