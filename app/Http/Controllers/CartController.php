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
            $quantity = (int) $cart[$product->id];

            return [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $shoppingCart->unitPrice($product),
                'line_total' => $shoppingCart->lineTotal($product, $quantity),
                'available' => $product->stock >= $quantity,
                'image' => $product->primaryImage?->url() ?? $product->image_url,
            ];
        })->values();

        return view('store.cart', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('is_active', true)
            ->first();

        if ($product === null) {
            return back()->withErrors(['product_id' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.']);
        }

        $cart = $request->session()->get('cart', []);
        $newQuantity = (int) ($cart[$product->id] ?? 0) + (int) $validated['quantity'];

        if ($newQuantity > 99) {
            return back()->withErrors(['quantity' => 'Mỗi sản phẩm chỉ có thể đặt tối đa 99 chiếc trong một đơn hàng.']);
        }

        if ($product->stock < $newQuantity) {
            return back()->withErrors(['quantity' => 'Số lượng yêu cầu vượt quá tồn kho hiện tại.']);
        }

        $cart[$product->id] = $newQuantity;
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

        $cart[$productId] = (int) $validated['quantity'];
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
}
