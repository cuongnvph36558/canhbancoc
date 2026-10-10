<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'bank_code',
        'bank_name',
        'account_number',
        'account_name',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function isReadyForCheckout(): bool
    {
        return $this->is_enabled
            && filled($this->bank_code)
            && filled($this->bank_name)
            && filled($this->account_number)
            && filled($this->account_name);
    }
}
