<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomCupOrder extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_province',
        'shipping_ward',
        'shipping_address',
        'cup_variant',
        'cup_size',
        'quantity',
        'unit_price',
        'discount_percentage',
        'total_amount',
        'design',
        'artwork_paths',
        'preview_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'design' => 'array',
            'artwork_paths' => 'array',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'discount_percentage' => 'integer',
            'total_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
