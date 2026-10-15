<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class ShoppingCart
{
    /**
     * @param  array<int|string, array{quantity: int, custom_text?: string|null, custom_image_path?: string|null}>  $cart
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

    /**
     * Store uploaded custom image and return path.
     */
    public function storeCustomImage(UploadedFile $file): string
    {
        return $file->store('custom-images', 'public');
    }
}
