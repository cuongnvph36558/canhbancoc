<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('orders:expire-pending')]
#[Description('Release inventory reserved by expired, unpaid orders')]
class ExpirePendingOrders extends Command
{
    public function handle(): int
    {
        Order::query()
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->where('inventory_reserved', true)
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $order) {
                    DB::transaction(function () use ($order): void {
                        $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();

                        if (
                            $lockedOrder === null
                            || $lockedOrder->payment_status !== Order::PAYMENT_PENDING
                            || ! $lockedOrder->inventory_reserved
                            || $lockedOrder->expires_at->isFuture()
                        ) {
                            return;
                        }

                        foreach ($lockedOrder->items()->get() as $item) {
                            if ($item->product_id === null) {
                                continue;
                            }

                            $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                            $product?->increment('stock', $item->quantity);
                        }

                        $lockedOrder->inventory_reserved = false;
                        $lockedOrder->payment_status = Order::PAYMENT_FAILED;
                        $lockedOrder->order_status = Order::STATUS_EXPIRED;
                        $lockedOrder->save();
                    });
                }
            });

        $this->info('Expired unpaid orders processed.');

        return self::SUCCESS;
    }
}
