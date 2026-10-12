<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function store(Request $request, string $orderNumber, OrderItem $orderItem): RedirectResponse
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        abort_unless($order->order_status === Order::STATUS_DELIVERED, 403);

        $purchasedItem = $order->items()
            ->whereKey($orderItem->getKey())
            ->whereNotNull('product_id')
            ->firstOrFail();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = ProductReview::query()->firstOrCreate(
            ['order_item_id' => $purchasedItem->id],
            [
                'product_id' => $purchasedItem->product_id,
                'user_id' => $order->user_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ],
        );

        if (! $review->wasRecentlyCreated) {
            return back()->withErrors(['review' => 'Bạn đã đánh giá sản phẩm này rồi.']);
        }

        return to_route('orders.show', $order->order_number)
            ->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }
}
