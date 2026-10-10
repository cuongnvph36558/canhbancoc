<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\VNPayPaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VNPayCallbackController extends Controller
{
    public function returned(Request $request, VNPayPaymentGateway $gateway): RedirectResponse
    {
        $parameters = $request->query();

        if (! $gateway->hasValidSignature($parameters)) {
            abort(403, 'Chữ ký thanh toán không hợp lệ.');
        }

        $order = Order::query()->where('order_number', $request->query('vnp_TxnRef'))->first();

        if ($order === null || ! $this->hasValidOrderAmount($order, $parameters)) {
            abort(404, 'Không tìm thấy đơn hàng hợp lệ.');
        }

        if (! $request->user() || $request->user()->id !== $order->user_id) {
            return redirect()->guest(route('login'));
        }

        return to_route('orders.show', $order->order_number);
    }

    public function ipn(Request $request, VNPayPaymentGateway $gateway): JsonResponse
    {
        $parameters = $request->all();

        if (! $gateway->hasValidSignature($parameters)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $order = Order::query()->where('order_number', $request->input('vnp_TxnRef'))->first();

        if ($order === null) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if (! $this->hasValidOrderAmount($order, $parameters)) {
            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        DB::transaction(function () use ($order, $parameters): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $paymentSucceeded = ($parameters['vnp_ResponseCode'] ?? null) === '00'
                && ($parameters['vnp_TransactionStatus'] ?? null) === '00';

            if ($lockedOrder->payment_status === Order::PAYMENT_PAID) {
                return;
            }

            if ($lockedOrder->payment_status !== Order::PAYMENT_PENDING) {
                if ($paymentSucceeded) {
                    $lockedOrder->payment_status = Order::PAYMENT_PAID;
                    $lockedOrder->order_status = Order::STATUS_NEEDS_REVIEW;
                    $lockedOrder->vnp_transaction_no = $parameters['vnp_TransactionNo'] ?? null;
                    $lockedOrder->vnp_bank_code = $parameters['vnp_BankCode'] ?? null;
                    $lockedOrder->paid_at = now();
                    $lockedOrder->save();
                }

                return;
            }

            $lockedOrder->vnp_transaction_no = $parameters['vnp_TransactionNo'] ?? null;
            $lockedOrder->vnp_bank_code = $parameters['vnp_BankCode'] ?? null;

            if ($paymentSucceeded) {
                $inventoryWasReserved = $lockedOrder->inventory_reserved;
                $lockedOrder->payment_status = Order::PAYMENT_PAID;
                $lockedOrder->order_status = $inventoryWasReserved
                    ? Order::STATUS_PROCESSING
                    : Order::STATUS_NEEDS_REVIEW;
                $lockedOrder->inventory_reserved = false;
                $lockedOrder->paid_at = now();
            } else {
                $this->releaseReservedInventory($lockedOrder);
                $lockedOrder->payment_status = Order::PAYMENT_FAILED;
                $lockedOrder->order_status = Order::STATUS_CANCELLED;
            }

            $lockedOrder->save();
        });

        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function hasValidOrderAmount(Order $order, array $parameters): bool
    {
        $amount = $parameters['vnp_Amount'] ?? null;

        return is_string($amount) || is_int($amount)
            ? ctype_digit((string) $amount) && (int) $amount === (int) round((float) $order->total * 100)
            : false;
    }

    private function releaseReservedInventory(Order $order): void
    {
        if (! $order->inventory_reserved) {
            return;
        }

        foreach ($order->items()->get() as $item) {
            if ($item->product_id === null) {
                continue;
            }

            $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
            $product?->increment('stock', $item->quantity);
        }

        $order->inventory_reserved = false;
    }
}
