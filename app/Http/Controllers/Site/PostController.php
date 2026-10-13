<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private const TYPES = [
        'article' => 'Artikel',
        'news' => 'Berita',
    ];

    public function index(Request $request)
    {
        $tipe = $request->query('tipe');
        $tipe = is_string($tipe) && isset(self::TYPES[$tipe]) ? $tipe : null;

        $categories = Post::published()->reorder()
            ->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category')->all();

        $kategori = $request->query('kategori');
        $kategori = is_string($kategori) && in_array($kategori, $categories, true) ? $kategori : null;

        $q = $request->query('q');
        $q = is_string($q) ? trim(mb_substr($q, 0, 100)) : '';

        $query = Post::published();
        if ($tipe) {
            $query->where('type', $tipe);
        }
        if ($kategori) {
            $query->where('category', $kategori);
        }
        if ($q !== '') {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%';
            $query->where(fn ($w) => $w
                ->whereRaw("title like ? escape '!'", [$like])
                ->orWhereRaw("excerpt like ? escape '!'", [$like]));
        }

        $posts = $query->paginate(9)->withQueryString();

        return view('site.posts.index', [
            'posts' => $posts,
            'types' => self::TYPES,
            'categories' => $categories,
            'tipe' => $tipe,
            'kategori' => $kategori,
            'q' => $q,
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->when($post->category, fn ($q) => $q->where('category', $post->category), fn ($q) => $q->whereRaw('1 = 0'))
            ->limit(3)->get();

        return view('site.posts.show', [
            'post' => $post,
            'related' => $related,
            'typeLabel' => self::TYPES[$post->type] ?? 'Artikel',
        ]);
    }
}
