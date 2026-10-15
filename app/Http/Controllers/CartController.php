<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ShoppingCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request, ShoppingCart $shoppingCart): View|RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        $products = $shoppingCart->products($cart);
        $removedProductIds = array_diff(array_map('intval', array_keys($cart)), $products->keys()->map(fn ($id): int => (int) $id)->all());

        if ($removedProductIds !== []) {
            foreach ($removedProductIds as $productId) {
                unset($cart[$productId]);
            }
            $request->session()->put('cart', $cart);
            $request->session()->flash('warning', 'Một số sản phẩm không còn kinh doanh và đã được bỏ khỏi giỏ hàng.');
        }

        if ($cart === []) {
            return view('store.cart', ['items' => [], 'subtotal' => 0]);
        }

        $items = $products->map(function (Product $product) use ($cart, $shoppingCart): array {
            $cartItem = $cart[$product->id] ?? ['quantity' => 0];
            $quantity = (int) ($cartItem['quantity'] ?? 0);

            return [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $shoppingCart->unitPrice($product),
                'line_total' => $shoppingCart->lineTotal($product, $quantity),
                'available' => $product->stock >= $quantity,
                'image' => $product->primaryImage?->url() ?? $product->image_url,
                'custom_text' => $cartItem['custom_text'] ?? null,
                'custom_color' => $cartItem['custom_color'] ?? null,
                'custom_color_hex' => $cartItem['custom_color_hex'] ?? null,
                'custom_image_path' => $cartItem['custom_image_path'] ?? null,
            ];
        })->values();

        return view('store.cart', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
        ]);
    }

    public function store(Request $request, ShoppingCart $shoppingCart): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
            'custom_text' => ['nullable', 'string', 'max:100'],
            'custom_color' => ['nullable', 'string', 'max:50'],
            'custom_color_hex' => ['nullable', 'string', 'max:20'],
            'custom_image' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        \Log::info('Cart.store received', [
            'custom_color' => $validated['custom_color'] ?? null,
            'custom_color_hex' => $validated['custom_color_hex'] ?? null,
            'raw' => $request->only(['custom_color', 'custom_color_hex']),
        ]);

        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('is_active', true)
            ->first();

        if ($product === null) {
            return back()->withErrors(['product_id' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.']);
        }

        $cart = $request->session()->get('cart', []);
        $newQuantity = (int) ($cart[$product->id]['quantity'] ?? 0) + (int) $validated['quantity'];

        if ($newQuantity > 99) {
            return back()->withErrors(['quantity' => 'Mỗi sản phẩm chỉ có thể đặt tối đa 99 chiếc trong một đơn hàng.']);
        }

        if ($product->stock < $newQuantity) {
            return back()->withErrors(['quantity' => 'Số lượng yêu cầu vượt quá tồn kho hiện tại.']);
        }

        // Store custom image if uploaded
        $customImagePath = null;
        if ($request->hasFile('custom_image')) {
            $customImagePath = $shoppingCart->storeCustomImage($request->file('custom_image'));
        }

        // Merge custom data - keep existing if not provided
        $existingCustomText = $cart[$product->id]['custom_text'] ?? null;
        $existingCustomImage = $cart[$product->id]['custom_image_path'] ?? null;
        $existingCustomColor = $cart[$product->id]['custom_color'] ?? null;
        $existingCustomColorHex = $cart[$product->id]['custom_color_hex'] ?? null;

        // Fallback to first active color of the product if customer didn't pick one
        $resolvedColor = $validated['custom_color'] ?? $existingCustomColor;
        $resolvedColorHex = $validated['custom_color_hex'] ?? $existingCustomColorHex;
        if (empty($resolvedColorHex) && empty($resolvedColor)) {
            $fallbackColor = $product->colors()->where('is_active', true)->orderBy('id')->first();
            if ($fallbackColor !== null) {
                $resolvedColor = $fallbackColor->name;
                $resolvedColorHex = $fallbackColor->hex_code;
            }
        }

        $cart[$product->id] = [
            'quantity' => $newQuantity,
            'custom_text' => $validated['custom_text'] ?? $existingCustomText,
            'custom_color' => $resolvedColor,
            'custom_color_hex' => $resolvedColorHex,
            'custom_image_path' => $customImagePath ?? $existingCustomImage,
        ];

        $request->session()->put('cart', $cart);

        return to_route('cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $cart = $request->session()->get('cart', []);
        $product = Product::query()
            ->whereKey($productId)
            ->where('is_active', true)
            ->first();

        if ($product === null || ! array_key_exists($productId, $cart)) {
            return to_route('cart.index')->withErrors(['cart' => 'Sản phẩm không còn trong giỏ hàng.']);
        }

        if ($product->stock < (int) $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Số lượng yêu cầu vượt quá tồn kho hiện tại.']);
        }

        $cart[$productId]['quantity'] = (int) $validated['quantity'];
        $request->session()->put('cart', $cart);

        return to_route('cart.index')->with('success', 'Giỏ hàng đã được cập nhật.');
    }

    public function destroy(Request $request, int $productId): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$productId]);
        $request->session()->put('cart', $cart);

        return to_route('cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'string'],
        ]);

        $productIds = json_decode($validated['product_ids'], true);

        if (! is_array($productIds) || empty($productIds)) {
            return to_route('cart.index')->withErrors(['cart' => 'Không có sản phẩm nào được chọn.']);
        }

        $cart = $request->session()->get('cart', []);
        $removedCount = 0;

        foreach ($productIds as $productId) {
            $productId = (int) $productId;
            if (array_key_exists($productId, $cart)) {
                unset($cart[$productId]);
                $removedCount++;
            }
        }

        $request->session()->put('cart', $cart);

        $message = $removedCount === 1
            ? 'Đã xóa sản phẩm khỏi giỏ hàng.'
            : "Đã xóa {$removedCount} sản phẩm khỏi giỏ hàng.";

        return to_route('cart.index')->with('success', $message);
    }
}
