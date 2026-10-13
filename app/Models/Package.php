<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug', 'name', 'description', 'price', 'price_unit', 'price_note',
    'features', 'is_popular', 'is_active', 'sort_order',
])]
class Package extends Model
{
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'integer',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
