<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomCupOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'    => ['required', 'string', 'max:120'],
            'customer_phone'   => ['required', 'string', 'min:8', 'max:30'],
            'customer_email'   => ['nullable', 'email', 'max:255'],
            'shipping_province' => ['required', 'string', 'max:120'],
            'shipping_ward'    => ['required', 'string', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'cup_variant'      => ['nullable', 'string'],
            'quantity'         => ['nullable', 'integer', 'min:1', 'max:99'],
            'design'          => ['nullable', 'array'],
            'design_preview'  => ['nullable', 'string'],
        ];
    }
}
