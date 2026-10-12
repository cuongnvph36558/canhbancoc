<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomCupVariant extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'handle_color',
        'interior_color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopeOrdered(Builder $query, bool $includeInactive = false): Builder
    {
        if (! $includeInactive) {
            $query->where('is_active', true);
        }

        return $query->orderBy('sort_order')->orderBy('id');
    }
}
