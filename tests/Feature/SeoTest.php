<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_only_published_content(): void
    {
        Service::create(['slug' => 'terbit', 'title' => 'T', 'status' => 'published']);
        Service::create(['slug' => 'draf', 'title' => 'D', 'status' => 'draft']);
        Project::create(['slug' => 'proyek-draf', 'title' => 'P', 'category' => 'foto', 'status' => 'draft']);
        Post::create(['slug' => 'terjadwal', 'title' => 'J', 'status' => 'published', 'publish_at' => now()->addDay()]);
        Post::create(['slug' => 'tayang', 'title' => 'Y', 'status' => 'published', 'publish_at' => now()->subDay()]);

        $res = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($res->getContent());

        $locs = [];
        foreach ($xml->url as $u) {
            $locs[] = (string) $u->loc;
        }
        $this->assertContains(route('services.show', 'terbit'), $locs);
        $this->assertContains(route('posts.show', 'tayang'), $locs);
        $this->assertContains(route('home'), $locs);
        $this->assertNotContains(route('services.show', 'draf'), $locs);
        $this->assertNotContains(route('projects.show', 'proyek-draf'), $locs);
        $this->assertNotContains(route('posts.show', 'terjadwal'), $locs);
        $this->assertNotContains(route('order.thanks'), $locs);
    }

    public function test_robots_blocks_admin_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /klien', false)
            ->assertSee('Disallow: /pesan/terima-kasih', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_home_has_business_json_ld_without_placeholder_phone(): void
    {
        config(['site.phone_display' => '+62 8XX-XXXX-XXXX']);
        $html = $this->get('/')->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $ld = json_decode($m[1], true);

        $this->assertSame('ProfessionalService', $ld['@type']);
        $this->assertArrayNotHasKey('telephone', $ld);

        config(['site.phone_display' => '+62 812-3456-7890']);
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $this->get('/')->getContent(), $m);
        $this->assertSame('+62 812-3456-7890', json_decode($m[1], true)['telephone']);
    }
}
