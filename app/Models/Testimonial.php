<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['author', 'role', 'company', 'quote', 'allow_public', 'sort_order'])]
class Testimonial extends Model
{
    protected function casts(): array
    {
        return [
            'allow_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Hanya testimoni dengan izin tampil. */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('allow_public', true)->orderBy('sort_order');
    }
}
