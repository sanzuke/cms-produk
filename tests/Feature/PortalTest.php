<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Deliveries\Pages\CreateDelivery;
use App\Filament\Resources\Deliveries\Pages\EditDelivery;
use App\Filament\Resources\Deliveries\Pages\ListDeliveries;
use App\Filament\Resources\Deliveries\Schemas\DeliveryForm;
use App\Mail\ClientActivityMail;
use App\Mail\DeliveryReadyMail;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    /** PNG 1x1 valid, supaya deteksi MIME berbasis isi mengenalinya sebagai gambar. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['site.notification_emails' => ['staf@brand.test']]);
    }

    private function order(array $o = []): Order
    {
        return Order::create($o + [
            'client_name' => 'Budi', 'client_company' => 'PT Contoh', 'client_email' => 'budi@contoh.test',
            'title' => 'Profil tambang', 'site' => 'Site A', 'status' => 'review', 'total' => 10_000_000,
        ]);
    }

    /** Order lunas penuh → unduhan tidak terkunci. */
    private function paidOrder(): Order
    {
        $order = $this->order();
        $order->issueInvoice('full')->markPaid('TRX1');

        return $order;
    }

    private function delivery(Order $order, array $o = []): Delivery
    {
        $disk = Storage::disk('local');
        $disk->put("deliveries/{$order->id}/a.png", base64_decode(self::PNG));
        $disk->put("deliveries/{$order->id}/b.pdf", '%PDF-1.4 laporan');
        $disk->put("deliveries/{$order->id}/c.png", base64_decode(self::PNG));

        return Delivery::create($o + [
            'order_id' => $order->id,
            'published_at' => now()->subHour(),
            'expires_at' => now()->addDays(10),
            'require_full_payment' => true,
            'files' => [
                ['path' => "deliveries/{$order->id}/a.png", 'name' => 'foto-pit.png', 'size' => 70],
                ['path' => "deliveries/{$order->id}/b.pdf", 'name' => 'laporan.pdf', 'size' => 16],
                ['path' => "deliveries/{$order->id}/c.png", 'name' => 'foto-pit.png', 'size' => 70],
            ],
        ]);
    }

    // ---------- akses & header ----------

    public function test_unknown_unpublished_and_future_tokens_are_404(): void
    {
        $this->get('/klien/'.str_repeat('x', 48))->assertNotFound();

        $draft = $this->delivery($this->order(), ['published_at' => null]);
        $this->get("/klien/{$draft->access_token}")->assertNotFound();
        $this->get("/klien/{$draft->access_token}/zip")->assertNotFound();

        $future = $this->delivery($this->order(), ['published_at' => now()->addDay()]);
        $this->get("/klien/{$future->access_token}")->assertNotFound();
    }

    public function test_expired_portal_is_410_on_every_route_with_contact_info(): void
    {
        config(['site.email' => 'kontak@brand.test']);
        $delivery = $this->delivery($this->paidOrder(), ['expires_at' => now()->subMinute()]);

        $this->get("/klien/{$delivery->access_token}")
            ->assertStatus(410)
            ->assertSee('kedaluwarsa')
            ->assertSee('kontak@brand.test')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get("/klien/{$delivery->access_token}/zip")->assertStatus(410);
        $this->get("/klien/{$delivery->access_token}/berkas/0")->assertStatus(410);
        $this->post("/klien/{$delivery->access_token}/komentar", ['body' => 'Halo juga'])->assertStatus(410);
        $this->assertSame(0, $delivery->fresh()->download_count);
    }

    public function test_portal_pages_are_noindex_and_not_cacheable(): void
    {
        $delivery = $this->delivery($this->order());

        $response = $this->get("/klien/{$delivery->access_token}")->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false)
            ->assertSee('ORD-', false)
            ->assertSee('Profil tambang');

        // Path penyimpanan tidak pernah muncul di HTML.
        $response->assertDontSee('deliveries/', false);
    }

    public function test_page_shows_timeline_invoices_with_bank_info_and_files(): void
    {
        config(['site.bank_name' => 'Bank Contoh', 'site.bank_account' => '1234567890', 'site.bank_holder' => 'PT Strata']);
        $order = $this->order(['status' => 'editing']);
        $order->issueInvoice('dp');
        $delivery = $this->delivery($order);

        $this->get("/klien/{$delivery->access_token}")
            ->assertOk()
            ->assertSee('aria-current="step"', false)
            ->assertSee('Editing')
            ->assertSee('INV-', false)
            ->assertSee('Bank Contoh')
            ->assertSee('1234567890')
            ->assertSee('laporan.pdf')
            ->assertSee('?preview=1', false);
    }

    // ---------- unduhan ----------

    public function test_locked_order_blocks_downloads_but_allows_image_previews(): void
    {
        $order = $this->order(); // belum dibayar sama sekali → terkunci
        $delivery = $this->delivery($order);
        $this->assertTrue($delivery->downloadsLocked());
        $t = $delivery->access_token;

        $this->get("/klien/$t/berkas/0")->assertForbidden();
        $this->get("/klien/$t/berkas/1")->assertForbidden();
        $this->get("/klien/$t/zip")->assertForbidden();
        $this->get("/klien/$t/zip?favorit=1")->assertForbidden();
        $this->assertSame(0, $delivery->fresh()->download_count);

        $preview = $this->get("/klien/$t/berkas/0?preview=1")->assertOk();
        $this->assertSame('image/jpeg', $preview->headers->get('Content-Type'), 'terkunci → versi kecil (JPEG), bukan file asli');
        $this->assertStringContainsString('inline', $preview->headers->get('Content-Disposition'));
        $this->assertSame(0, $delivery->fresh()->download_count, 'pratinjau bukan unduhan');

        // Pratinjau hanya untuk gambar.
        $this->get("/klien/$t/berkas/1?preview=1")->assertNotFound();
    }

    public function test_unlocked_order_allows_downloads_and_counts_them(): void
    {
        $delivery = $this->delivery($this->paidOrder());
        $t = $delivery->access_token;
        $this->assertFalse($delivery->downloadsLocked());

        $file = $this->get("/klien/$t/berkas/1")->assertOk();
        $this->assertStringContainsString('attachment', $file->headers->get('Content-Disposition'));
        $this->assertStringContainsString('laporan.pdf', $file->headers->get('Content-Disposition'));
        $this->assertSame(1, $delivery->fresh()->download_count);

        $this->get("/klien/$t/zip")->assertOk();
        $this->assertSame(2, $delivery->fresh()->download_count);

        // Pratinjau tetap tidak dihitung.
        $this->get("/klien/$t/berkas/0?preview=1")->assertOk();
        $this->assertSame(2, $delivery->fresh()->download_count);
    }

    public function test_require_full_payment_off_never_locks(): void
    {
        $delivery = $this->delivery($this->order(), ['require_full_payment' => false]);

        $this->get("/klien/{$delivery->access_token}/berkas/1")->assertOk();
    }

    public function test_file_index_is_validated(): void
    {
        $delivery = $this->delivery($this->paidOrder());
        $t = $delivery->access_token;

        $this->get("/klien/$t/berkas/3")->assertNotFound();
        $this->get("/klien/$t/berkas/999")->assertNotFound();
        $this->get("/klien/$t/berkas/-1")->assertNotFound();
        $this->get("/klien/$t/berkas/abc")->assertNotFound();

        // Entri yang berkasnya hilang dari disk → 404, bukan error.
        Storage::disk('local')->delete($delivery->files[1]['path']);
        $this->get("/klien/$t/berkas/1")->assertNotFound();
    }

    public function test_zip_contains_expected_files_with_unique_names(): void
    {
        $delivery = $this->delivery($this->paidOrder());

        $response = $this->get("/klien/{$delivery->access_token}/zip")->assertOk();
        $this->assertStringContainsString('.zip', $response->headers->get('Content-Disposition'));

        $names = $this->zipNames($response->streamedContent());

        $this->assertEqualsCanonicalizing(['foto-pit.png', 'foto-pit (2).png', 'laporan.pdf'], $names);
    }

    public function test_favorites_zip_only_contains_favorites_and_empty_favorites_redirects(): void
    {
        $delivery = $this->delivery($this->paidOrder());
        $t = $delivery->access_token;

        $this->get("/klien/$t/zip?favorit=1")->assertRedirect("/klien/$t#berkas");
        $this->assertSame(0, $delivery->fresh()->download_count);

        $delivery->update(['favorites' => [$delivery->files[1]['path']]]);

        $names = $this->zipNames($this->get("/klien/$t/zip?favorit=1")->assertOk()->streamedContent());
        $this->assertSame(['laporan.pdf'], $names);
    }

    private function zipNames(string $content): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'ptest');
        file_put_contents($tmp, $content);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp));
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();
        unlink($tmp);

        return $names;
    }

    // ---------- bukti pembayaran ----------

    public function test_client_uploads_proof_replacing_previous_one_and_staff_is_notified(): void
    {
        Mail::fake();
        $order = $this->order();
        $invoice = $order->issueInvoice('dp');
        $delivery = $this->delivery($order);
        $t = $delivery->access_token;
        $url = "/klien/$t/invoice/{$invoice->id}/bukti";

        $this->post($url, ['proof' => UploadedFile::fake()->image('bukti1.jpg')])
            ->assertRedirect("/klien/$t#invoice-{$invoice->id}")
            ->assertSessionHas('portal_status');

        $invoice->refresh();
        $this->assertSame('proof_submitted', $invoice->status);
        $first = $invoice->proof_path;
        $this->assertStringStartsWith('payment-proofs/', $first);
        Storage::disk('local')->assertExists($first);

        // Unggah ulang boleh selama belum lunas, berkas lama dihapus.
        $this->post($url, ['proof' => UploadedFile::fake()->create('bukti2.pdf', 100, 'application/pdf')]);

        $invoice->refresh();
        $this->assertNotSame($first, $invoice->proof_path);
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($invoice->proof_path);

        Mail::assertSent(ClientActivityMail::class, fn (ClientActivityMail $m) => $m->hasTo('staf@brand.test')
            && $m->kind === ClientActivityMail::PROOF
            && str_contains($m->render(), $invoice->number));
    }

    public function test_proof_rejects_bad_mime_oversize_and_missing_file(): void
    {
        Mail::fake();
        $order = $this->order();
        $invoice = $order->issueInvoice('dp');
        $t = $this->delivery($order)->access_token;
        $url = "/klien/$t/invoice/{$invoice->id}/bukti";
        $bag = "proof-{$invoice->id}";

        $this->post($url, ['proof' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')])
            ->assertSessionHasErrors('proof', null, $bag);
        $this->post($url, ['proof' => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain')])
            ->assertSessionHasErrors('proof', null, $bag);
        $this->post($url, ['proof' => UploadedFile::fake()->create('besar.pdf', 5121, 'application/pdf')])
            ->assertSessionHasErrors('proof', null, $bag);
        $this->post($url, [])->assertSessionHasErrors('proof', null, $bag);

        $invoice->refresh();
        $this->assertSame('unpaid', $invoice->status);
        $this->assertNull($invoice->proof_path);
        Mail::assertNothingSent();
    }

    public function test_proof_cannot_target_other_orders_invoice_or_settled_invoices(): void
    {
        Mail::fake();
        $mine = $this->order();
        $t = $this->delivery($mine)->access_token;

        $other = $this->order(['client_name' => 'Orang Lain']);
        $foreign = $other->issueInvoice('dp');
        $paid = $mine->issueInvoice('dp');
        $paid->markPaid('TRX');
        $cancelled = $mine->issueInvoice('final');
        $cancelled->update(['status' => 'cancelled']);

        $file = fn () => ['proof' => UploadedFile::fake()->image('b.jpg')];

        $this->post("/klien/$t/invoice/{$foreign->id}/bukti", $file())->assertNotFound();
        $this->post("/klien/$t/invoice/99999/bukti", $file())->assertNotFound();
        $this->post("/klien/$t/invoice/{$paid->id}/bukti", $file())->assertSessionHasErrors('proof', null, "proof-{$paid->id}");
        $this->post("/klien/$t/invoice/{$cancelled->id}/bukti", $file())->assertSessionHasErrors('proof', null, "proof-{$cancelled->id}");

        $this->assertNull($foreign->fresh()->proof_path);
        $this->assertSame('unpaid', $foreign->fresh()->status);
        $this->assertSame('paid', $paid->fresh()->status);
        $this->assertSame('cancelled', $cancelled->fresh()->status);
        $this->assertNull($paid->fresh()->proof_path);
        Mail::assertNothingSent();
    }

    // ---------- favorit ----------

    public function test_favorite_toggles_by_index_and_ignores_unknown_files(): void
    {
        $delivery = $this->delivery($this->order());
        $t = $delivery->access_token;
        $path = $delivery->files[1]['path'];

        $this->post("/klien/$t/favorit", ['index' => 1])->assertRedirect("/klien/$t#berkas-1");
        $this->assertSame([$path], $delivery->fresh()->favorites);

        $this->postJson("/klien/$t/favorit", ['index' => 0])->assertOk()->assertJson(['favorite' => true, 'count' => 2]);

        $this->postJson("/klien/$t/favorit", ['index' => 1])->assertOk()->assertJson(['favorite' => false, 'count' => 1]);
        $this->assertSame([$delivery->files[0]['path']], $delivery->fresh()->favorites);

        $this->postJson("/klien/$t/favorit", ['index' => 42])->assertStatus(422);
        $this->postJson("/klien/$t/favorit", ['index' => '../../etc/passwd'])->assertStatus(422);
        $this->postJson("/klien/$t/favorit", [])->assertStatus(422);
        $this->assertCount(1, $delivery->fresh()->favorites);
    }

    // ---------- komentar ----------

    public function test_client_comment_is_stored_and_staff_notified(): void
    {
        Mail::fake();
        $order = $this->order();
        $t = $this->delivery($order)->access_token;

        $this->post("/klien/$t/komentar", ['body' => "  Tolong cerahkan foto ke-2.\nTerima kasih.  "])
            ->assertRedirect("/klien/$t#komentar")
            ->assertSessionHas('portal_status');

        $comment = OrderComment::sole();
        $this->assertSame($order->id, $comment->order_id);
        $this->assertSame('client', $comment->author);
        $this->assertSame('Budi', $comment->author_name);
        $this->assertStringStartsWith('Tolong cerahkan', $comment->body);

        Mail::assertSent(ClientActivityMail::class, fn (ClientActivityMail $m) => $m->hasTo('staf@brand.test')
            && $m->hasReplyTo('budi@contoh.test')
            && $m->kind === ClientActivityMail::COMMENT
            && str_contains($m->render(), 'Tolong cerahkan foto ke-2.'));

        // Terlihat di halaman portal (di-escape).
        OrderComment::create(['order_id' => $order->id, 'author' => 'staff', 'author_name' => 'Sari', 'body' => '<b>Siap</b> kami revisi']);
        $this->get("/klien/$t")->assertSee('Tolong cerahkan foto ke-2.')->assertSee('&lt;b&gt;Siap&lt;/b&gt; kami revisi', false);
    }

    public function test_comment_validation_and_throttle(): void
    {
        Mail::fake();
        $t = $this->delivery($this->order())->access_token;

        $this->post("/klien/$t/komentar", ['body' => 'ab'])->assertSessionHasErrors('body', null, 'comment');
        $this->post("/klien/$t/komentar", ['body' => str_repeat('x', 2001)])->assertSessionHasErrors('body', null, 'comment');
        $this->post("/klien/$t/komentar", [])->assertSessionHasErrors('body', null, 'comment');
        $this->assertSame(0, OrderComment::count());

        $this->post("/klien/$t/komentar", ['body' => str_repeat('x', 2000)]);
        $this->assertSame(1, OrderComment::count());

        // Batas 10 permintaan/menit untuk komentar (4 sudah terpakai di atas).
        for ($i = 0; $i < 6; $i++) {
            $this->post("/klien/$t/komentar", ['body' => 'komentar ke '.$i])->assertRedirect();
        }
        $this->post("/klien/$t/komentar", ['body' => 'terlalu sering'])->assertStatus(429);
    }

    // ---------- admin ----------

    public function test_send_ready_email_only_available_when_delivery_is_active(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->role(Role::Sales)->create());

        $active = $this->delivery($this->order());
        $draft = $this->delivery($this->order(), ['published_at' => null]);
        $expired = $this->delivery($this->order(), ['expires_at' => now()->subDay()]);

        Livewire::test(ListDeliveries::class)
            ->assertTableActionVisible('sendReady', $active)
            ->assertTableActionHidden('sendReady', $draft)
            ->assertTableActionHidden('sendReady', $expired)
            ->callTableAction('sendReady', $active)
            ->assertNotified();

        Mail::assertSent(DeliveryReadyMail::class, 1);
        Mail::assertSent(DeliveryReadyMail::class, fn (DeliveryReadyMail $m) => $m->hasTo('budi@contoh.test')
            && str_contains($m->render(), $active->portalUrl()));
    }

    public function test_send_ready_email_failure_is_reported_without_breaking_the_page(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        $this->actingAs(User::factory()->role(Role::Owner)->create());
        $delivery = $this->delivery($this->order());

        Livewire::test(ListDeliveries::class)
            ->callTableAction('sendReady', $delivery)
            ->assertNotified('Email gagal dikirim');
    }

    public function test_regenerate_token_kills_old_link_and_extend_adds_30_days(): void
    {
        $this->actingAs(User::factory()->role(Role::Sales)->create());
        $delivery = $this->delivery($this->order(), ['expires_at' => now()->addDays(5)]);
        $old = $delivery->access_token;

        Livewire::test(ListDeliveries::class)->callTableAction('regenerateToken', $delivery);

        $new = $delivery->fresh()->access_token;
        $this->assertNotSame($old, $new);
        $this->assertSame(48, strlen($new));
        $this->get("/klien/$old")->assertNotFound();
        $this->get("/klien/$new")->assertOk();

        Livewire::test(ListDeliveries::class)->callTableAction('extend', $delivery);
        $this->assertEqualsWithDelta(now()->addDays(35)->timestamp, $delivery->fresh()->expires_at->timestamp, 5);

        $delivery->update(['expires_at' => now()->subDays(3)]);
        Livewire::test(ListDeliveries::class)->callTableAction('extend', $delivery);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $delivery->fresh()->expires_at->timestamp, 5, 'kedaluwarsa dihitung dari hari ini');
    }

    public function test_copy_link_action_shows_the_portal_url(): void
    {
        $this->actingAs(User::factory()->role(Role::Sales)->create());
        $delivery = $this->delivery($this->order());

        Livewire::test(ListDeliveries::class)
            ->mountTableAction('copyLink', $delivery)
            ->assertSchemaStateSet(['url' => $delivery->portalUrl()]);
    }

    public function test_create_delivery_stores_uploaded_files_with_original_names(): void
    {
        $this->actingAs(User::factory()->role(Role::Sales)->create());
        $order = $this->order();
        $taken = $this->order();
        $this->delivery($taken);

        Livewire::test(CreateDelivery::class)
            ->fillForm([
                'order_id' => $order->id,
                'uploads' => [UploadedFile::fake()->image('Foto Pit Utara.jpg'), UploadedFile::fake()->createWithContent('laporan.pdf', '%PDF-1.4 laporan')],
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(['Foto Pit Utara.jpg', 'laporan.pdf'], array_column($delivery->files, 'name'));
        foreach ($delivery->files as $file) {
            $this->assertStringStartsWith("deliveries/{$order->id}/", $file['path']);
            Storage::disk('local')->assertExists($file['path']);
            $this->assertGreaterThan(0, $file['size']);
        }
        $this->assertNotNull($delivery->published_at);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $delivery->expires_at->timestamp, 120);

        // Order yang sudah punya hasil tidak bisa dipilih lagi.
        Livewire::test(CreateDelivery::class)
            ->fillForm(['order_id' => $taken->id])
            ->call('create')
            ->assertHasFormErrors(['order_id']);
    }

    public function test_saving_removes_dropped_files_and_prunes_favorites_and_unpublishes(): void
    {
        $delivery = $this->delivery($this->order());
        [$a, $b, $c] = array_column($delivery->files, 'path');
        $delivery->update(['favorites' => [$a, $b]]);

        $state = DeliveryForm::toFormData($delivery->fresh()->attributesToArray());
        $this->assertSame([$a, $b, $c], $state['uploads']);
        $this->assertTrue($state['is_published']);

        $state['uploads'] = [$b, 'deliveries/999/hack.png', "deliveries/{$delivery->order_id}/../../.env"];
        $state['is_published'] = false;
        $data = DeliveryForm::toModelData($state, $delivery->fresh());

        $this->assertSame([$b], array_column($data['files'], 'path'));
        $this->assertSame('laporan.pdf', $data['files'][0]['name']);
        $this->assertSame([$b], $data['favorites']);
        $this->assertNull($data['published_at']);
        $this->assertArrayNotHasKey('uploads', $data);
        Storage::disk('local')->assertMissing($a);
        Storage::disk('local')->assertMissing($c);
        Storage::disk('local')->assertExists($b);
    }

    public function test_edit_page_shows_favorites_and_comments_read_only(): void
    {
        $this->actingAs(User::factory()->role(Role::Sales)->create());
        $order = $this->order();
        $delivery = $this->delivery($order);
        $delivery->update(['favorites' => [$delivery->files[1]['path']]]);
        OrderComment::create(['order_id' => $order->id, 'author' => 'client', 'author_name' => 'Budi', 'body' => 'Mohon ganti warna langit']);

        Livewire::test(EditDelivery::class, ['record' => $delivery->getKey()])
            ->assertSee('laporan.pdf')
            ->assertSee('Mohon ganti warna langit')
            ->assertSee($delivery->portalUrl());
    }

    public function test_only_owner_and_sales_may_open_deliveries_in_admin(): void
    {
        $this->actingAs(User::factory()->role(Role::Editor)->create());
        $this->get('/admin/deliveries')->assertForbidden();
        $this->get('/admin/deliveries/create')->assertForbidden();

        foreach ([Role::Owner, Role::Sales] as $role) {
            $this->actingAs(User::factory()->role($role)->create());
            $this->get('/admin/deliveries')->assertOk();
        }
    }
}
