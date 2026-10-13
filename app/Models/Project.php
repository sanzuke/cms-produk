<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug', 'title', 'category', 'commodity', 'work_area', 'year', 'client_name', 'show_client',
    'location', 'show_location', 'challenge', 'approach', 'result', 'specs', 'cover', 'gallery',
    'featured', 'status', 'sort_order',
])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'show_client' => 'boolean',
            'show_location' => 'boolean',
            'featured' => 'boolean',
            'year' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->orderBy('sort_order');
    }

    /** Nama klien hanya boleh tampil di publik bila diizinkan. */
    public function publicClientName(): ?string
    {
        return $this->show_client ? $this->client_name : null;
    }

    /** Lokasi hanya boleh tampil di publik bila diizinkan. */
    public function publicLocation(): ?string
    {
        return $this->show_location ? $this->location : null;
    }
}
