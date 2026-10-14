<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Widgets\ContentOverview;
use App\Filament\Widgets\SalesOverview;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_widgets_are_visible_per_role(): void
    {
        $this->actingAs(User::factory()->role(Role::Owner)->create());
        $this->assertTrue(SalesOverview::canView());
        $this->assertTrue(ContentOverview::canView());

        $this->actingAs(User::factory()->role(Role::Sales)->create());
        $this->assertTrue(SalesOverview::canView());
        $this->assertFalse(ContentOverview::canView());

        $this->actingAs(User::factory()->role(Role::Editor)->create());
        $this->assertFalse(SalesOverview::canView());
        $this->assertTrue(ContentOverview::canView());
    }

    public function test_sales_numbers_count_only_what_they_claim(): void
    {
        $this->actingAs(User::factory()->role(Role::Sales)->create());

        Inquiry::create(['name' => 'A', 'email' => 'a@x.test', 'status' => 'new']);
        Inquiry::create(['name' => 'B', 'email' => 'b@x.test', 'status' => 'won']);

        $order = Order::create(['client_name' => 'C', 'client_email' => 'c@x.test', 'title' => 'T', 'total' => 100_000_000]);
        Order::create(['client_name' => 'D', 'client_email' => 'd@x.test', 'title' => 'T2', 'total' => 1, 'status' => 'done']);

        $paidThisMonth = $order->invoices()->create(['type' => 'dp', 'amount' => 30_000_000, 'status' => 'paid', 'paid_at' => now()]);
        $order->invoices()->create(['type' => 'final', 'amount' => 20_000_000, 'status' => 'paid', 'paid_at' => now()->subMonths(2)]);
        $order->invoices()->create(['type' => 'final', 'amount' => 5_000_000, 'status' => 'unpaid']);
        $order->invoices()->create(['type' => 'final', 'amount' => 7_000_000, 'status' => 'proof_submitted']);
        $order->invoices()->create(['type' => 'final', 'amount' => 9_000_000, 'status' => 'cancelled']);

        $this->assertSame('paid', $paidThisMonth->status);
        $idr = fn (int $n) => \Illuminate\Support\Number::currency($n, 'IDR', 'id', 0);

        Livewire::test(SalesOverview::class)
            ->assertSee($idr(30_000_000)) // pendapatan bulan ini: hanya yang lunas bulan ini
            ->assertSee($idr(12_000_000)) // tertunda: unpaid + proof_submitted, tanpa cancelled
            ->assertSee('2 invoice belum lunas')
            ->assertDontSee($idr(50_000_000));
    }

    public function test_content_overview_counts_drafts_and_scheduled(): void
    {
        $this->actingAs(User::factory()->role(Role::Editor)->create());

        Post::create(['slug' => 'a', 'title' => 'A', 'status' => 'draft']);
        Post::create(['slug' => 'b', 'title' => 'B', 'status' => 'published', 'publish_at' => now()->addWeek()]);
        Post::create(['slug' => 'c', 'title' => 'C', 'status' => 'published', 'publish_at' => now()->subDay()]);

        Livewire::test(ContentOverview::class)->assertSee('Artikel draft')->assertSee('1 terjadwal terbit');
    }

    public function test_dashboard_loads_for_every_role(): void
    {
        foreach (Role::cases() as $role) {
            $this->actingAs(User::factory()->role($role)->create())->get('/admin')->assertOk();
        }
    }
}
