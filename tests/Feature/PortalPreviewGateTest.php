<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalPreviewGateTest extends TestCase
{
    use RefreshDatabase;

    private function deliveryWithPhoto(): Delivery
    {
        Storage::fake('local');
        $path = UploadedFile::fake()->image('hasil.jpg', 2400, 1600)->storeAs('deliveries/1', 'hasil.jpg', 'local');

        $order = Order::create(['client_name' => 'Budi', 'client_email' => 'budi@x.test', 'title' => 'Aerial', 'total' => 10_000_000]);
        $order->issueInvoice('full');

        return Delivery::create([
            'order_id' => $order->id,
            'published_at' => now()->subHour(),
            'files' => [['path' => $path, 'name' => 'hasil.jpg', 'size' => 1]],
        ]);
    }

    public function test_locked_client_only_gets_a_small_preview_never_the_original(): void
    {
        $delivery = $this->deliveryWithPhoto();
        $url = route('portal.file', [$delivery->access_token, 0, 'preview' => 1]);

        $res = $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        [$w, $h] = getimagesizefromstring($res->streamedContent());

        $this->assertLessThanOrEqual(640, max($w, $h));
        $this->assertSame(0, $delivery->fresh()->download_count);
        $this->get(route('portal.file', [$delivery->access_token, 0]))->assertForbidden();
    }

    public function test_paid_client_gets_the_original_in_preview_and_download(): void
    {
        $delivery = $this->deliveryWithPhoto();
        $delivery->order->invoices()->first()->markPaid();

        $res = $this->get(route('portal.file', [$delivery->access_token, 0, 'preview' => 1]))->assertOk();
        [$w] = getimagesizefromstring($res->streamedContent());
        $this->assertSame(2400, $w);

        $this->get(route('portal.file', [$delivery->access_token, 0]))->assertOk();
        $this->assertSame(1, $delivery->fresh()->download_count);
    }
}
