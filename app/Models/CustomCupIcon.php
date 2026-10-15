<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomCupIcon extends Model
{
    protected $fillable = [
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
