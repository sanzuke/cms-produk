<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Pages\ScheduleCalendar;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\InvoicesRelationManager;
use App\Models\Order;
use App\Models\OrderComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function sales(): User
    {
        return tap(User::factory()->role(Role::Sales)->create(['name' => 'Sari Sales']), fn ($u) => $this->actingAs($u));
    }

    private function order(array $o = []): Order
    {
        return Order::create($o + [
            'client_name' => 'Budi', 'client_company' => 'PT Contoh', 'client_email' => 'budi@contoh.test',
            'title' => 'Profil tambang', 'status' => 'awaiting_dp', 'total' => 100_000_000,
        ]);
    }

    public function test_editor_gets_403_and_sales_can_open_order_and_calendar_pages(): void
    {
        $order = $this->order();

        $this->actingAs(User::factory()->role(Role::Editor)->create());
        $this->get('/admin/orders')->assertForbidden();
        $this->get("/admin/orders/{$order->id}/edit")->assertForbidden();
        $this->get('/admin/jadwal')->assertForbidden();

        $this->actingAs(User::factory()->role(Role::Sales)->create());
        $this->get('/admin/orders')->assertOk()->assertSee('Profil tambang');
        $this->get("/admin/orders/{$order->id}/edit")->assertOk();
        $this->get('/admin/jadwal')->assertOk();
    }

    public function test_dp_action_creates_dp_invoice_and_is_hidden_afterwards(): void
    {
        $this->sales();
        config(['site.dp_percent' => 40]);
        $order = $this->order();

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->assertActionVisible('issueDp')
            ->callAction('issueDp')
            ->assertNotified()
            ->assertActionHidden('issueDp');

        $invoice = $order->invoices()->sole();
        $this->assertSame('dp', $invoice->type);
        $this->assertSame(40_000_000, $invoice->amount);
    }

    public function test_dp_action_is_visible_again_when_dp_invoice_cancelled(): void
    {
        $this->sales();
        $order = $this->order();
        $order->issueInvoice('dp')->update(['status' => 'cancelled']);

        Livewire::test(EditOrder::class, ['record' => $order->id])->assertActionVisible('issueDp');
    }

    public function test_final_and_full_actions_create_matching_invoices(): void
    {
        $this->sales();
        $order = $this->order();
        $order->issueInvoice('dp', 30_000_000);

        Livewire::test(EditOrder::class, ['record' => $order->id])->callAction('issueFinal');
        $this->assertSame(70_000_000, $order->invoices()->where('type', 'final')->sole()->amount);

        $other = $this->order(['title' => 'Lain']);
        Livewire::test(EditOrder::class, ['record' => $other->id])->callAction('issueFull');
        $this->assertSame(100_000_000, $other->invoices()->where('type', 'full')->sole()->amount);
    }

    public function test_domain_exception_is_shown_as_notification_instead_of_crashing(): void
    {
        $this->sales();
        $order = $this->order(['total' => 0]);

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->callAction('issueFinal')
            ->assertNotified('Invoice tidak dibuat')
            ->assertHasNoActionErrors();

        $this->assertSame(0, $order->invoices()->count());
    }

    public function test_table_row_action_creates_invoice_too(): void
    {
        $this->sales();
        $order = $this->order();

        Livewire::test(ListOrders::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('issueFull')->table($order))
            ->assertNotified();

        $this->assertSame('full', $order->invoices()->sole()->type);
    }

    public function test_advance_from_awaiting_dp_requires_payment_or_override_reason(): void
    {
        $user = $this->sales();
        $order = $this->order(['notes' => 'Catatan awal']);

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->callAction('advance', [])->assertHasActionErrors(['override_reason' => 'required']);
        $this->assertSame('awaiting_dp', $order->fresh()->status);

        Livewire::test(EditOrder::class, ['record' => $order->id])->callAction('advance', ['override_reason' => 'Klien lama, DP menyusul'])->assertHasNoActionErrors();

        $fresh = $order->fresh();
        $this->assertSame('scheduled', $fresh->status);
        $this->assertStringStartsWith('Catatan awal', $fresh->notes);
        $this->assertStringContainsString('Klien lama, DP menyusul', $fresh->notes);
        $this->assertStringContainsString($user->name, $fresh->notes);
    }

    public function test_advance_with_paid_dp_needs_no_reason_and_later_steps_are_sequential(): void
    {
        $this->sales();
        $order = $this->order();
        $order->issueInvoice('dp')->markPaid('TRF');
        $this->assertSame('scheduled', $order->fresh()->status, 'DP lunas menjadwalkan order otomatis');

        $order->refresh()->update(['status' => 'awaiting_dp']); // keadaan tak biasa: DP lunas tapi status mundur
        Livewire::test(EditOrder::class, ['record' => $order->id])->callAction('advance', []);
        $this->assertSame('scheduled', $order->fresh()->status);
        $this->assertNull($order->fresh()->notes);

        foreach (['shooting', 'editing', 'review', 'done'] as $expected) {
            Livewire::test(EditOrder::class, ['record' => $order->id])->callAction('advance', []);
            $this->assertSame($expected, $order->fresh()->status);
        }

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->assertActionHidden('advance')
            ->assertActionHidden('cancelOrder');
    }

    public function test_cancel_action_sets_cancelled_and_hides_further_actions(): void
    {
        $this->sales();
        $order = $this->order();

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->callAction('cancelOrder')
            ->assertActionHidden('advance')
            ->assertActionHidden('issueDp');

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_quotation_order_total_is_read_only_but_manual_order_total_is_editable(): void
    {
        $this->sales();
        $quotation = \App\Models\Quotation::create([
            'client_name' => 'Budi', 'client_email' => 'b@x.test', 'title' => 'T',
            'items' => [['description' => 'x', 'qty' => 1, 'unit' => 'pkt', 'unit_price' => 5_000_000]],
        ]);
        $fromQuotation = Order::createFromQuotation($quotation);
        $manual = $this->order();

        Livewire::test(EditOrder::class, ['record' => $fromQuotation->id])
            ->assertFormFieldIsDisabled('total')
            ->fillForm(['title' => 'Judul baru'])->call('save');
        $this->assertSame(5_000_000, $fromQuotation->fresh()->total);
        $this->assertSame('Judul baru', $fromQuotation->fresh()->title);

        Livewire::test(EditOrder::class, ['record' => $manual->id])
            ->assertFormFieldIsEnabled('total')
            ->fillForm(['total' => 7_000_000])->call('save');
        $this->assertSame(7_000_000, $manual->fresh()->total);
    }

    public function test_checklist_can_be_edited_and_saved(): void
    {
        $this->sales();
        $order = $this->order();
        $checklist = $order->fresh()->checklist;
        $checklist[0]['done'] = true;

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['checklist' => $checklist])->call('save')->assertHasNoFormErrors();

        $this->assertTrue($order->fresh()->checklist[0]['done']);
        $this->assertCount(5, $order->fresh()->checklist);
    }

    public function test_filters_unpaid_and_this_week(): void
    {
        $this->sales();
        $paid = $this->order(['title' => 'Lunas', 'scheduled_start' => now()->addMonths(3)]);
        $paid->issueInvoice('full')->markPaid();
        $unpaid = $this->order(['title' => 'Belum', 'scheduled_start' => now()->startOfWeek()->addDay(), 'scheduled_end' => now()->startOfWeek()->addDays(2)]);

        Livewire::test(ListOrders::class)
            ->filterTable('unpaid')
            ->assertCanSeeTableRecords([$unpaid])->assertCanNotSeeTableRecords([$paid]);

        Livewire::test(ListOrders::class)
            ->filterTable('this_week')
            ->assertCanSeeTableRecords([$unpaid])->assertCanNotSeeTableRecords([$paid]);
    }

    public function test_relation_managers_list_invoices_and_staff_reply_creates_staff_comment(): void
    {
        $user = $this->sales();
        $order = $this->order();
        $invoice = $order->issueInvoice('dp');
        OrderComment::create(['order_id' => $order->id, 'author' => 'client', 'author_name' => 'Budi', 'body' => 'Mohon revisi warna']);

        Livewire::test(InvoicesRelationManager::class, ['ownerRecord' => $order, 'pageClass' => EditOrder::class])
            ->assertCanSeeTableRecords([$invoice]);

        Livewire::test(CommentsRelationManager::class, ['ownerRecord' => $order, 'pageClass' => EditOrder::class])
            ->assertSee('Mohon revisi warna')
            ->callAction(\Filament\Actions\Testing\TestAction::make('create')->table(), ['body' => 'Sudah kami perbaiki'])
            ->assertHasNoActionErrors();

        $reply = $order->comments()->where('author', 'staff')->sole();
        $this->assertSame('Sari Sales', $reply->author_name);
        $this->assertSame('Sudah kami perbaiki', $reply->body);
    }

    public function test_calendar_lists_order_in_visible_month_and_not_outside_and_navigates(): void
    {
        $this->sales();
        $inMonth = $this->order(['title' => 'Shooting Bulan Ini', 'scheduled_start' => now()->startOfMonth()->addDays(9)]);
        $nextMonth = $this->order(['title' => 'Proyek Bulan Depan', 'scheduled_start' => now()->addMonthNoOverflow()->startOfMonth()->addDays(10), 'scheduled_end' => now()->addMonthNoOverflow()->startOfMonth()->addDays(12)]);
        $this->order(['title' => 'Proyek Batal', 'status' => 'cancelled', 'scheduled_start' => now()->startOfMonth()->addDays(9)]);
        $this->order(['title' => 'Belum Terjadwal']);

        $page = Livewire::test(ScheduleCalendar::class)
            ->assertSee('Shooting Bulan Ini')
            ->assertDontSee('Proyek Bulan Depan')
            ->assertDontSee('Proyek Batal')
            ->assertDontSee('Belum Terjadwal')
            ->assertSee(route('filament.admin.resources.orders.edit', ['record' => $inMonth->id]));

        $page->call('nextMonth')
            ->assertSee('Proyek Bulan Depan')
            ->assertDontSee('Shooting Bulan Ini');

        $page->call('previousMonth')->call('previousMonth')
            ->assertDontSee('Proyek Bulan Depan')
            ->assertDontSee('Shooting Bulan Ini');

        $page->call('today')->assertSee('Shooting Bulan Ini');
    }

    public function test_calendar_shows_multi_day_order_spanning_month_boundary_in_both_months(): void
    {
        $this->sales();
        $this->order(['title' => 'Lintas Bulan', 'scheduled_start' => now()->endOfMonth()->subDay(), 'scheduled_end' => now()->endOfMonth()->addDays(3)]);

        Livewire::test(ScheduleCalendar::class)->assertSee('Lintas Bulan')
            ->call('nextMonth')->assertSee('Lintas Bulan');
    }

    public function test_navigation_badge_counts_awaiting_dp_orders(): void
    {
        $this->order();
        $this->order(['status' => 'scheduled']);

        $this->assertSame('1', \App\Filament\Resources\Orders\OrderResource::getNavigationBadge());
    }
}
