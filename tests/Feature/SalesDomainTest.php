<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesDomainTest extends TestCase
{
    use RefreshDatabase;

    private function quotation(array $o = []): Quotation
    {
        return Quotation::create($o + [
            'client_name' => 'Budi', 'client_company' => 'PT Contoh', 'client_email' => 'budi@contoh.test', 'title' => 'Profil tambang',
            'items' => [
                ['description' => 'Hari kerja foto', 'qty' => 2, 'unit' => 'hari', 'unit_price' => 15_000_000],
                ['description' => 'Video 3 menit', 'qty' => 1, 'unit' => 'paket', 'unit_price' => 40_000_000],
            ],
            'mobilization' => 5_000_000, 'discount' => 1_000_000,
        ]);
    }

    public function test_quotation_total_is_computed_and_numbered(): void
    {
        $q = $this->quotation();

        $this->assertSame(70_000_000, $q->subtotal());
        $this->assertSame(74_000_000, $q->total);
        $this->assertMatchesRegularExpression('/^QUO-\d{4}-0001$/', $q->number);

        $q->update(['discount' => 100_000_000]);
        $this->assertSame(0, $q->fresh()->total, 'total tidak boleh negatif');
    }

    public function test_accepted_quotation_creates_single_order_and_marks_inquiry_won(): void
    {
        $inquiry = Inquiry::create(['name' => 'Budi', 'email' => 'budi@contoh.test', 'site' => 'Site A', 'status' => 'quoted']);
        $q = $this->quotation(['inquiry_id' => $inquiry->id]);

        $order = Order::createFromQuotation($q);
        $again = Order::createFromQuotation($q->fresh());

        $this->assertSame($order->id, $again->id);
        $this->assertSame(1, Order::count());
        $this->assertSame('awaiting_dp', $order->status);
        $this->assertSame(74_000_000, $order->total);
        $this->assertSame('Site A', $order->site);
        $this->assertSame('accepted', $q->fresh()->status);
        $this->assertSame('won', $inquiry->fresh()->status);
        $this->assertCount(5, $order->checklist);
        $this->assertFalse($order->checklist[0]['done']);
    }

    public function test_invoice_amounts_dp_final_and_duplicate_dp_guard(): void
    {
        config(['site.dp_percent' => 30]);
        $order = Order::createFromQuotation($this->quotation());

        $dp = $order->issueInvoice('dp');
        $this->assertSame(22_200_000, $dp->amount);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-0001$/', $dp->number);

        $this->expectException(\DomainException::class);
        try {
            $order->issueInvoice('dp');
        } finally {
            $final = $order->issueInvoice('final');
            $this->assertSame(51_800_000, $final->amount);
            $this->assertSame(74_000_000, $dp->amount + $final->amount);
        }
    }

    public function test_cancelled_invoice_is_not_counted_and_full_billing_blocks_more(): void
    {
        $order = Order::createFromQuotation($this->quotation());
        $dp = $order->issueInvoice('dp');
        $dp->update(['status' => 'cancelled']);

        $this->assertSame(74_000_000, $order->issueInvoice('final')->amount);

        $this->expectException(\DomainException::class);
        $order->issueInvoice('final');
    }

    public function test_paying_dp_schedules_order_but_paying_final_does_not_change_status(): void
    {
        $order = Order::createFromQuotation($this->quotation());
        $dp = $order->issueInvoice('dp');

        $dp->markPaid('TRF-1');
        $this->assertSame('scheduled', $order->fresh()->status);
        $this->assertNotNull($dp->fresh()->paid_at);
        $this->assertSame('TRF-1', $dp->fresh()->payment_ref);

        $order->update(['status' => 'editing']);
        $order->issueInvoice('final')->markPaid();
        $this->assertSame('editing', $order->fresh()->status);
        $this->assertSame(0, $order->fresh()->outstanding());
    }

    public function test_delivery_active_window_and_payment_lock(): void
    {
        $order = Order::createFromQuotation($this->quotation());
        $delivery = Delivery::create(['order_id' => $order->id]);

        $this->assertSame(48, strlen($delivery->access_token));
        $this->assertFalse($delivery->isActive(), 'belum diterbitkan');

        $delivery->update(['published_at' => now()->subMinute(), 'expires_at' => now()->addDay()]);
        $this->assertTrue($delivery->fresh()->isActive());

        $delivery->update(['expires_at' => now()->subSecond()]);
        $this->assertFalse($delivery->fresh()->isActive(), 'kedaluwarsa');

        $delivery = $delivery->fresh();
        $this->assertTrue($delivery->downloadsLocked(), 'belum lunas → terkunci');
        $order->issueInvoice('full')->markPaid();
        $this->assertFalse($delivery->fresh()->downloadsLocked());

        $delivery->update(['require_full_payment' => false]);
        $this->assertFalse($delivery->fresh()->downloadsLocked());
    }
}
