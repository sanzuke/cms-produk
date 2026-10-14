<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /klien',
            'Disallow: /pesan/terima-kasih',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        // [url, lastmod|null, changefreq, priority]
        $urls = [];

        foreach ([
            ['home', 'weekly', '1.0'], ['services.index', 'weekly', '0.9'], ['projects.index', 'weekly', '0.8'],
            ['packages.index', 'monthly', '0.8'], ['order.create', 'monthly', '0.8'], ['posts.index', 'weekly', '0.7'],
            ['about', 'monthly', '0.6'], ['contact', 'monthly', '0.6'], ['faq', 'monthly', '0.5'],
            ['legal.privacy', 'yearly', '0.2'], ['legal.terms', 'yearly', '0.2'],
        ] as [$route, $freq, $priority]) {
            $urls[] = [route($route), null, $freq, $priority];
        }

        foreach (Service::published()->get(['slug', 'updated_at']) as $s) {
            $urls[] = [route('services.show', $s->slug), $s->updated_at, 'monthly', '0.8'];
        }
        foreach (Project::published()->get(['slug', 'updated_at']) as $p) {
            $urls[] = [route('projects.show', $p->slug), $p->updated_at, 'monthly', '0.6'];
        }
        foreach (Post::published()->get(['slug', 'updated_at']) as $p) {
            $urls[] = [route('posts.show', $p->slug), $p->updated_at, 'monthly', '0.6'];
        }

        return response()
            ->view('site.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
