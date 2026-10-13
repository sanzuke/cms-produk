<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Project;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function __invoke()
    {
        $projects = Project::published()->where('featured', true)->limit(6)->get();
        if ($projects->isEmpty()) {
            $projects = Project::published()->latest('id')->limit(6)->get();
        }

        return view('site.home', [
            'projects' => $projects,
            'testimonials' => Testimonial::public()->limit(3)->get(),
            'posts' => Post::published()->limit(3)->get(),
        ]);
    }
}
