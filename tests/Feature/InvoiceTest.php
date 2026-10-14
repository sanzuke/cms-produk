<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $o = []): Order
    {
        return Order::create($o + [
            'client_name' => 'Budi', 'client_company' => 'PT Contoh', 'client_email' => 'budi@contoh.test',
            'title' => 'Profil tambang', 'status' => 'awaiting_dp', 'total' => 80_000_000,
        ]);
    }

    private function actingAsSales(): User
    {
        $user = User::factory()->role(Role::Sales)->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_mark_paid_sets_fields_and_schedules_order_for_dp(): void
    {
        $this->actingAsSales();
        $order = $this->order();
        $invoice = $order->issueInvoice('dp');

        Livewire::test(ListInvoices::class)
            ->callTableAction('markPaid', $invoice, ['payment_ref' => 'TRF-123'])
            ->assertNotified();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('TRF-123', $invoice->payment_ref);
        $this->assertNotNull($invoice->paid_at);
        $this->assertSame('scheduled', $order->fresh()->status);
    }

    public function test_mark_paid_of_final_invoice_does_not_touch_order_status(): void
    {
        $this->actingAsSales();
        $order = $this->order(['status' => 'review']);
        $invoice = $order->issueInvoice('final');

        Livewire::test(EditInvoice::class, ['record' => $invoice->id])
            ->callAction('markPaid', ['payment_ref' => null]);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('review', $order->fresh()->status);
    }

    public function test_mark_paid_not_available_for_paid_or_cancelled(): void
    {
        $this->actingAsSales();
        $order = $this->order();
        $paid = $order->issueInvoice('dp');
        $paid->markPaid();
        $cancelled = $order->issueInvoice('final');
        $cancelled->update(['status' => 'cancelled']);

        Livewire::test(ListInvoices::class)
            ->assertTableActionHidden('markPaid', $paid)
            ->assertTableActionHidden('markPaid', $cancelled)
            ->assertTableActionHidden('cancelInvoice', $paid)
            ->assertTableActionHidden('cancelInvoice', $cancelled);
    }

    public function test_reject_proof_returns_to_unpaid_and_deletes_file(): void
    {
        Storage::fake('local');
        $this->actingAsSales();
        $invoice = $this->order()->issueInvoice('dp');
        Storage::disk('local')->put('payment-proofs/bukti.jpg', 'x');
        $invoice->update(['status' => 'proof_submitted', 'proof_path' => 'payment-proofs/bukti.jpg', 'notes' => 'Catatan lama']);

        Livewire::test(ListInvoices::class)
            ->callTableAction('rejectProof', $invoice, ['reason' => 'Nominal tidak sesuai'])
            ->assertHasNoTableActionErrors();

        $invoice->refresh();
        $this->assertSame('unpaid', $invoice->status);
        $this->assertNull($invoice->proof_path);
        $this->assertStringContainsString('Catatan lama', $invoice->notes);
        $this->assertStringContainsString('Nominal tidak sesuai', $invoice->notes);
        Storage::disk('local')->assertMissing('payment-proofs/bukti.jpg');
    }

    public function test_reject_proof_requires_reason_and_only_for_proof_submitted(): void
    {
        $this->actingAsSales();
        $invoice = $this->order()->issueInvoice('dp');

        Livewire::test(ListInvoices::class)->assertTableActionHidden('rejectProof', $invoice);

        $invoice->update(['status' => 'proof_submitted']);

        Livewire::test(ListInvoices::class)
            ->callTableAction('rejectProof', $invoice, ['reason' => ''])
            ->assertHasTableActionErrors(['reason' => 'required']);

        $this->assertSame('proof_submitted', $invoice->fresh()->status);
    }

    public function test_cancel_only_for_unpaid_and_proof_submitted(): void
    {
        $this->actingAsSales();
        $order = $this->order();
        $unpaid = $order->issueInvoice('dp');
        $proof = $order->issueInvoice('final');
        $proof->update(['status' => 'proof_submitted']);
        $paid = $order->issueInvoice('final', 1_000_000);
        $paid->markPaid();

        Livewire::test(ListInvoices::class)
            ->callTableAction('cancelInvoice', $unpaid)
            ->callTableAction('cancelInvoice', $proof)
            ->assertTableActionHidden('cancelInvoice', $paid);

        $this->assertSame('cancelled', $unpaid->fresh()->status);
        $this->assertSame('cancelled', $proof->fresh()->status);
        $this->assertSame('paid', $paid->fresh()->status);
    }

    public function test_pdf_renders_number_amount_and_paid_stamp(): void
    {
        config(['site.bank_name' => 'BCA', 'site.bank_account' => '1234567890', 'site.bank_holder' => 'PT Origya']);
        $invoice = $this->order()->issueInvoice('dp');

        $html = view('pdf.invoice', ['invoice' => $invoice->load('order')])->render();
        $this->assertStringContainsString($invoice->number, $html);
        $this->assertStringContainsString('40.000.000', $html);
        $this->assertStringContainsString('1234567890', $html);
        $this->assertStringNotContainsString('LUNAS', $html);

        $this->assertStringStartsWith('%PDF', InvoiceMail::renderPdf($invoice)->output());

        $invoice->markPaid('REF');
        $this->assertStringContainsString('LUNAS', view('pdf.invoice', ['invoice' => $invoice->fresh('order')])->render());
    }

    public function test_pdf_warns_when_bank_account_not_configured(): void
    {
        config(['site.bank_name' => '', 'site.bank_account' => '', 'site.bank_holder' => '']);
        $invoice = $this->order()->issueInvoice('dp');

        $this->assertStringContainsString('Rekening pembayaran belum diatur', view('pdf.invoice', ['invoice' => $invoice->load('order')])->render());
    }

    public function test_download_pdf_action_streams_pdf(): void
    {
        $this->actingAsSales();
        $invoice = $this->order()->issueInvoice('dp');

        Livewire::test(ListInvoices::class)
            ->callTableAction('downloadPdf', $invoice)
            ->assertFileDownloaded($invoice->number.'.pdf');
    }

    public function test_send_to_client_mails_pdf_and_sets_sent_at(): void
    {
        Mail::fake();
        $this->actingAsSales();
        $invoice = $this->order()->issueInvoice('dp');

        Livewire::test(ListInvoices::class)->callTableAction('sendToClient', $invoice);

        Mail::assertSent(InvoiceMail::class, function (InvoiceMail $mail) use ($invoice) {
            $attachments = $mail->attachments();

            return $mail->hasTo('budi@contoh.test')
                && $mail->invoice->is($invoice)
                && count($attachments) === 1
                && str_contains($mail->render(), Number::currency(40_000_000, 'IDR', 'id', 0))
                && $mail->envelope()->subject !== '';
        });
        $this->assertNotNull($invoice->fresh()->sent_at);
    }

    public function test_send_without_client_email_notifies_and_sends_nothing(): void
    {
        Mail::fake();
        $this->actingAsSales();
        $invoice = $this->order(['client_email' => ''])->issueInvoice('dp');

        Livewire::test(ListInvoices::class)->callTableAction('sendToClient', $invoice)->assertNotified();

        Mail::assertNothingSent();
        $this->assertNull($invoice->fresh()->sent_at);
    }

    public function test_send_failure_is_reported_without_setting_sent_at(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $this->actingAsSales();
        $invoice = $this->order()->issueInvoice('dp');

        Livewire::test(ListInvoices::class)->callTableAction('sendToClient', $invoice)->assertNotified();

        $this->assertNull($invoice->fresh()->sent_at);
    }

    public function test_create_requires_order_and_positive_amount(): void
    {
        $this->actingAsSales();
        $order = $this->order();

        Livewire::test(CreateInvoice::class)
            ->fillForm(['type' => 'dp', 'amount' => 0, 'status' => 'unpaid'])
            ->call('create')
            ->assertHasFormErrors(['order_id' => 'required', 'amount' => 'min']);

        Livewire::test(CreateInvoice::class)
            ->fillForm(['order_id' => $order->id, 'type' => 'dp', 'amount' => 25_000_000, 'status' => 'unpaid'])
            ->call('create')
            ->assertHasNoFormErrors();

        $invoice = Invoice::firstOrFail();
        $this->assertSame(25_000_000, $invoice->amount);
        $this->assertSame($order->id, $invoice->order_id);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{4}$/', $invoice->number);
    }

    public function test_money_fields_locked_for_paid_invoice(): void
    {
        $this->actingAsSales();
        $invoice = $this->order()->issueInvoice('dp');
        $invoice->markPaid();

        Livewire::test(EditInvoice::class, ['record' => $invoice->id])
            ->assertFormFieldDisabled('amount')
            ->assertFormFieldDisabled('type')
            ->assertFormFieldDisabled('due_date');
    }

    public function test_setting_status_paid_via_form_records_paid_at_and_schedules_order(): void
    {
        $this->actingAsSales();
        $order = $this->order();
        $invoice = $order->issueInvoice('dp');

        Livewire::test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm(['status' => 'paid'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNotNull($invoice->fresh()->paid_at);
        $this->assertSame('scheduled', $order->fresh()->status);
    }

    public function test_navigation_badge_counts_proof_submitted(): void
    {
        $this->actingAsSales();
        $order = $this->order();
        $order->issueInvoice('dp')->update(['status' => 'proof_submitted']);
        $order->issueInvoice('final', 1_000_000);

        $this->assertSame('1', \App\Filament\Resources\Invoices\InvoiceResource::getNavigationBadge());
    }

    public function test_sales_and_owner_can_open_invoices_but_editor_cannot(): void
    {
        foreach ([Role::Sales, Role::Owner] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->get('/admin/invoices')
                ->assertOk();
        }

        $this->actingAs(User::factory()->role(Role::Editor)->create())
            ->get('/admin/invoices')
            ->assertForbidden();
    }
}
