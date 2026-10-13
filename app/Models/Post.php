<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type', 'slug', 'title', 'excerpt', 'body', 'cover', 'category',
    'seo_title', 'seo_description', 'status', 'publish_at',
])]
class Post extends Model
{
    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
        ];
    }

    /** Terbit bila status published dan jadwal publish (jika ada) sudah lewat. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->orderByDesc('publish_at')
            ->orderByDesc('id');
    }
}
