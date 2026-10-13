<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('site.services.index', [
            'services' => Service::published()->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = Service::published()->where('slug', $slug)->firstOrFail();

        $category = match (true) {
            str_contains($slug, 'foto') => 'foto',
            str_contains($slug, 'video') => 'video',
            str_contains($slug, 'aerial'), str_contains($slug, 'monitoring') => 'aerial',
            default => null,
        };

        $related = $category
            ? Project::published()->where('category', $category)->limit(3)->get()
            : collect();

        if ($related->isEmpty()) {
            $related = Project::published()->where('featured', true)->latest()->limit(3)->get();
        }

        return view('site.services.show', compact('service', 'related'));
    }
}
