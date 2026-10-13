<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug', 'title', 'summary', 'body', 'use_cases', 'deliverables', 'requirements', 'faq',
    'cover', 'price_from', 'seo_title', 'seo_description', 'status', 'sort_order',
])]
class Service extends Model
{
    protected function casts(): array
    {
        return [
            'use_cases' => 'array',
            'deliverables' => 'array',
            'faq' => 'array',
            'price_from' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->orderBy('sort_order');
    }
}
