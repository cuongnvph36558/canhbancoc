<?php

namespace App\Http\Controllers;

use App\Models\CustomCupVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomCupCartController extends Controller
{
    private const SESSION_KEY = 'custom_cup_cart';

    public function index(Request $request): View
    {
        $items = $this->cartItems($request);

        return view('store.custom-cup-cart', [
            'items' => $items,
            'subtotal' => array_sum(array_column($items, 'line_total')),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->extractPayload($request);

        $validated = validator($data, [
            'cup_variant' => ['required', 'string', 'max:60'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'unit_price' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:0'],
            'design' => ['required', 'array'],
            'design.items' => ['required', 'array', 'min:1', 'max:20'],
            'design.items.*.type' => ['required', 'string', 'in:text,icon,image'],
            'design.items.*.content' => ['required', 'string', 'max:80'],
            'design.items.*.x' => ['required', 'numeric'],
            'design.items.*.y' => ['required', 'numeric'],
            'design.items.*.width' => ['required', 'numeric'],
            'preview' => ['nullable', 'string', 'max:500000'],
        ])->validate();

        $cart = $request->session()->get(self::SESSION_KEY, []);
        $id = (string) Str::uuid();
        $cart[$id] = [
            'id' => $id,
            'cup_variant' => $validated['cup_variant'],
            'quantity' => (int) $validated['quantity'],
            'unit_price' => (int) $validated['unit_price'],
            'total' => (int) $validated['total'],
            'design' => $validated['design'],
            'preview' => $validated['preview'] ?? null,
            'added_at' => now()->toIso8601String(),
        ];

        $request->session()->put(self::SESSION_KEY, $cart);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Đã thêm cốc tự thiết kế vào giỏ.',
                'cart_count' => array_sum(array_column($cart, 'quantity')),
            ]);
        }

        return to_route('custom-cup-cart.index')->with('success', 'Đã thêm cốc tự thiết kế vào giỏ.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);

        if (array_key_exists($id, $cart)) {
            unset($cart[$id]);
            $request->session()->put(self::SESSION_KEY, $cart);
        }

        return to_route('custom-cup-cart.index')->with('success', 'Đã xóa khỏi giỏ hàng.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cartItems(Request $request): array
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        $items = [];

        foreach ($cart as $entry) {
            $items[] = [
                'id' => $entry['id'],
                'cup_variant' => $entry['cup_variant'],
                'cup_variant_name' => $this->variantName($entry['cup_variant']),
                'quantity' => (int) $entry['quantity'],
                'unit_price' => (int) $entry['unit_price'],
                'line_total' => (int) $entry['total'],
                'design' => $entry['design'],
                'preview' => $entry['preview'] ?? null,
            ];
        }

        return $items;
    }

    private function variantName(string $slug): string
    {
        $config = config('store.custom_cup.variants', []);
        $name = $config[$slug]['name'] ?? null;

        if ($name !== null) {
            return (string) $name;
        }

        $dbName = CustomCupVariant::query()->where('slug', $slug)->value('name');

        return $dbName !== null ? (string) $dbName : ucfirst($slug);
    }

    private function extractPayload(Request $request): array
    {
        if ($request->isJson() || $request->wantsJson()) {
            return (array) $request->json()->all();
        }

        return $request->all();
    }
}
