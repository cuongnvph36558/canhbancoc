<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Arr;
use RuntimeException;

class VNPayPaymentGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.vnpay.tmn_code'))
            && filled(config('services.vnpay.hash_secret'))
            && filled(config('services.vnpay.payment_url'));
    }

    public function createPaymentUrl(Order $order, string $ipAddress): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('VNPay chưa được cấu hình. Vui lòng thiết lập VNPAY_TMN_CODE và VNPAY_HASH_SECRET.');
        }

        $parameters = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => config('services.vnpay.tmn_code'),
            'vnp_Amount' => (string) ((int) round((float) $order->total * 100)),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $order->order_number,
            'vnp_OrderInfo' => 'Thanh toan don hang '.$order->order_number,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => route('payments.vnpay.return'),
            'vnp_IpAddr' => $ipAddress,
            'vnp_CreateDate' => now()->timezone('Asia/Ho_Chi_Minh')->format('YmdHis'),
            'vnp_ExpireDate' => $order->expires_at->timezone('Asia/Ho_Chi_Minh')->format('YmdHis'),
        ];

        $query = $this->buildQuery($parameters);
        $signature = hash_hmac('sha512', $query, (string) config('services.vnpay.hash_secret'));

        return config('services.vnpay.payment_url').'?'.$query.'&vnp_SecureHash='.$signature;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function hasValidSignature(array $parameters): bool
    {
        $providedSignature = Arr::get($parameters, 'vnp_SecureHash');
        $merchantCode = Arr::get($parameters, 'vnp_TmnCode');

        if (! $this->isConfigured()
            || ! is_string($providedSignature)
            || ! is_string($merchantCode)
            || ! hash_equals((string) config('services.vnpay.tmn_code'), $merchantCode)) {
            return false;
        }

        unset($parameters['vnp_SecureHash'], $parameters['vnp_SecureHashType']);
        $signedParameters = [];
        foreach ($parameters as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'vnp_') && is_scalar($value)) {
                $signedParameters[$key] = $value;
            }
        }
        $query = $this->buildQuery($signedParameters);
        $expectedSignature = hash_hmac('sha512', $query, (string) config('services.vnpay.hash_secret'));

        return hash_equals($expectedSignature, $providedSignature);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function buildQuery(array $parameters): string
    {
        ksort($parameters, SORT_STRING);

        return http_build_query($parameters, '', '&', PHP_QUERY_RFC1738);
    }
}
