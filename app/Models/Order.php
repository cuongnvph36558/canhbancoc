<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_METHOD_VNPAY = 'vnpay';

    public const PAYMENT_METHOD_BANK_QR = 'bank_qr';

    public const PAYMENT_METHOD_COD = 'cod';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_NEEDS_REVIEW = 'needs_review';

    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_province',
        'shipping_ward',
        'shipping_address',
        'subtotal',
        'shipping_fee',
        'total',
        'order_status',
        'payment_status',
        'payment_method',
        'payment_bank_code',
        'payment_bank_name',
        'payment_account_number',
        'payment_account_name',
        'payment_qr_image_path',
        'vnp_transaction_no',
        'vnp_bank_code',
        'inventory_reserved',
        'expires_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'inventory_reserved' => 'boolean',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
