<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ShoppingCart
{
    /**
     * @param  array<int|string, int>  $cart
     * @return Collection<int, Product>
     */
    public function products(array $cart): Collection
    {
        if ($cart === []) {
            return new Collection;
        }

        return Product::query()
            ->whereKey(array_keys($cart))
            ->where('is_active', true)
            ->with('primaryImage')
            ->get()
            ->keyBy('id');
    }

    public function unitPrice(Product $product): float
    {
        return round($product->discountedPrice());
    }

    public function lineTotal(Product $product, int $quantity): float
    {
        return $this->unitPrice($product) * $quantity;
    }
}
