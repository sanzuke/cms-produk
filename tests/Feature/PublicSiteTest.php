<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_render_with_empty_database(): void
    {
        foreach (['/', '/layanan', '/portofolio', '/paket', '/pesan', '/artikel', '/tentang', '/kontak', '/faq', '/legal/privasi', '/legal/syarat'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_draft_content_is_not_public(): void
    {
        Service::create(['slug' => 'draf', 'title' => 'Draf', 'status' => 'draft']);
        Project::create(['slug' => 'draf', 'title' => 'Draf', 'category' => 'foto', 'status' => 'draft']);
        Post::create(['slug' => 'terjadwal', 'title' => 'Terjadwal', 'status' => 'published', 'publish_at' => now()->addDay()]);

        $this->get('/layanan/draf')->assertNotFound();
        $this->get('/portofolio/draf')->assertNotFound();
        $this->get('/artikel/terjadwal')->assertNotFound();
    }

    public function test_client_name_and_location_respect_privacy_flags(): void
    {
        Project::create([
            'slug' => 'rahasia', 'title' => 'Rahasia', 'category' => 'aerial', 'status' => 'published',
            'client_name' => 'PT Rahasia Besar', 'show_client' => false,
            'location' => 'Blok Rahasia', 'show_location' => false,
        ]);
        Project::create([
            'slug' => 'terbuka', 'title' => 'Terbuka', 'category' => 'aerial', 'status' => 'published',
            'client_name' => 'PT Terbuka Jaya', 'show_client' => true,
        ]);

        $this->get('/portofolio/rahasia')->assertOk()->assertDontSee('PT Rahasia Besar')->assertDontSee('Blok Rahasia');
        $this->get('/portofolio/terbuka')->assertOk()->assertSee('PT Terbuka Jaya');
        $this->get('/portofolio')->assertDontSee('PT Rahasia Besar');
    }

    public function test_testimonials_without_permission_are_hidden(): void
    {
        Testimonial::create(['author' => 'Tanpa Izin', 'quote' => 'q1', 'allow_public' => false]);
        Testimonial::create(['author' => 'Berizin', 'quote' => 'q2', 'allow_public' => true]);

        $this->get('/')->assertSee('Berizin')->assertDontSee('Tanpa Izin');
    }

    public function test_invalid_filters_do_not_break_listings(): void
    {
        $this->get('/portofolio?jenis=ngawur&tahun[]=1')->assertOk();
        $this->get('/artikel?kategori=ngawur&tipe=x&q=%25')->assertOk();
    }

    public function test_quote_request_is_stored_and_validated(): void
    {
        $service = Service::create(['slug' => 's', 'title' => 'S', 'status' => 'published']);

        $this->post('/pesan', ['name' => '', 'email' => 'bukan-email'])->assertSessionHasErrors(['name', 'email']);
        $this->assertDatabaseCount('inquiries', 0);

        $valid = [
            'name' => 'Budi', 'company' => 'PT Contoh', 'email' => 'budi@contoh.test', 'phone' => '081234567890',
            'site' => 'Site A', 'needed_date' => now()->addWeek()->toDateString(),
            'service_id' => $service->id, 'consent' => '1',
        ];
        $this->post('/pesan', $valid)->assertRedirect(route('order.thanks'));
        $this->assertDatabaseHas('inquiries', ['email' => 'budi@contoh.test', 'status' => 'new', 'service_id' => $service->id]);

        $this->post('/pesan', ['website' => 'spam'] + array_merge($valid, ['email' => 'bot@contoh.test']));
        $this->assertDatabaseMissing('inquiries', ['email' => 'bot@contoh.test']);
        $this->assertSame(1, Inquiry::count());
    }
}
