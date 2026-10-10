<?php

namespace App\Services;

use App\Models\Order;

class VietQrCodeGenerator
{
    public function imageUrl(Order $order): ?string
    {
        if (
            blank($order->payment_bank_code)
            || blank($order->payment_account_number)
            || blank($order->payment_account_name)
        ) {
            return null;
        }

        $bankCode = rawurlencode($order->payment_bank_code);
        $accountNumber = rawurlencode($order->payment_account_number);
        $imagePath = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png',
            $bankCode,
            $accountNumber,
        );
        $query = http_build_query([
            'amount' => (int) round((float) $order->total),
            'addInfo' => $order->order_number,
            'accountName' => $order->payment_account_name,
        ], '', '&', PHP_QUERY_RFC3986);

        return $imagePath.'?'.$query;
    }
}
