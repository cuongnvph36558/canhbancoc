<?php

namespace App\Http\Controllers;

use App\Events\NewOrderCreated;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Services\ShoppingCart;
use App\Services\VietQrCodeGenerator;
use App\Services\VNPayPaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request, ShoppingCart $shoppingCart): View|RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        $products = $shoppingCart->products($cart);

        if ($cart === [] || $products->count() !== count($cart)) {
            return to_route('cart.index')->withErrors(['cart' => 'Giỏ hàng trống hoặc có sản phẩm không còn kinh doanh.']);
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

        $paymentSetting = PaymentSetting::query()->find(1);

        return view('store.checkout', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
            'shippingFee' => $items->sum('line_total') >= 300000 ? 0 : 30000,
            'bankQrAvailable' => $paymentSetting?->isReadyForCheckout() ?? false,
        ]);
    }

    public function store(Request $request, ShoppingCart $shoppingCart, VNPayPaymentGateway $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_province' => ['required', 'string', 'max:120'],
            'shipping_ward' => ['required', 'string', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method' => [
                'nullable',
                'string',
                Rule::in([
                    Order::PAYMENT_METHOD_VNPAY,
                    Order::PAYMENT_METHOD_BANK_QR,
                    Order::PAYMENT_METHOD_COD,
                ]),
            ],
        ]);

        $paymentMethod = $validated['payment_method'] ?? Order::PAYMENT_METHOD_VNPAY;
        $paymentSetting = $paymentMethod === Order::PAYMENT_METHOD_BANK_QR
            ? PaymentSetting::query()->find(1)
            : null;

        if ($paymentMethod === Order::PAYMENT_METHOD_VNPAY && ! $gateway->isConfigured()) {
            return back()->withErrors(['payment' => 'Thanh toán VNPay chưa được cấu hình. Vui lòng liên hệ cửa hàng hoặc bổ sung thông tin kết nối VNPay.'])->withInput();
        }

        if ($paymentMethod === Order::PAYMENT_METHOD_BANK_QR && ! ($paymentSetting?->isReadyForCheckout() ?? false)) {
            return back()->withErrors(['payment' => 'Thanh toán QR chưa được cấu hình. Vui lòng chọn phương thức khác hoặc liên hệ cửa hàng.'])->withInput();
        }

        $cart = $request->session()->get('cart', []);

        if ($cart === []) {
            return to_route('cart.index')->withErrors(['cart' => 'Giỏ hàng của bạn hiện đang trống.']);
        }

        $order = DB::transaction(function () use ($cart, $validated, $request, $shoppingCart, $paymentMethod, $paymentSetting): Order {
            $products = Product::query()
                ->whereKey(array_keys($cart))
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->with('primaryImage')
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages(['cart' => 'Một số sản phẩm trong giỏ hàng không còn kinh doanh.']);
            }

            $subtotal = 0;
            foreach ($products as $product) {
                $quantity = (int) $cart[$product->id];

                if ($quantity < 1 || $quantity > 99) {
                    throw ValidationException::withMessages(['cart' => 'Mỗi sản phẩm chỉ có thể đặt từ 1 đến 99 chiếc trong một đơn hàng.']);
                }

                if ($quantity > $product->stock) {
                    throw ValidationException::withMessages(['cart' => 'Tồn kho vừa thay đổi. Vui lòng cập nhật số lượng trong giỏ hàng.']);
                }

                $subtotal += $shoppingCart->lineTotal($product, $quantity);
            }

            $shippingFee = $subtotal >= 300000 ? 0 : 30000;
            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'order_number' => Str::upper(Str::random(24)),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_province' => $validated['shipping_province'],
                'shipping_ward' => $validated['shipping_ward'],
                'shipping_address' => $validated['shipping_address'],
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
                'order_status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'payment_method' => $paymentMethod,
                'payment_bank_code' => $paymentSetting?->bank_code,
                'payment_bank_name' => $paymentSetting?->bank_name,
                'payment_account_number' => $paymentSetting?->account_number,
                'payment_account_name' => $paymentSetting?->account_name,
                'payment_qr_image_path' => $paymentSetting?->qr_image_path,
                'inventory_reserved' => true,
                'expires_at' => $paymentMethod === Order::PAYMENT_METHOD_VNPAY
                    ? now()->addMinutes(15)
                    : null,
            ]);

            foreach ($products as $product) {
                $quantity = (int) $cart[$product->id];
                $unitPrice = $shoppingCart->unitPrice($product);
                $product->decrement('stock', $quantity);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_image' => $product->primaryImage?->url() ?? $product->image_url,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $unitPrice * $quantity,
                ]);
            }

            return $order;
        });

        NewOrderCreated::dispatch(
            'regular',
            $order->order_number,
            $order->customer_name,
            (int) $order->total,
            route('admin.orders.show', $order),
        );

        $request->session()->forget('cart');

        if ($paymentMethod !== Order::PAYMENT_METHOD_VNPAY) {
            return to_route('orders.show', $order->order_number);
        }

        return redirect()->away($gateway->createPaymentUrl($order, (string) $request->ip()));
    }

    public function show(
        Request $request,
        string $orderNumber,
        VietQrCodeGenerator $vietQrCodeGenerator,
    ): View {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->with('items.review')
            ->firstOrFail();

        return view('store.order-confirmation', [
            'order' => $order,
            'bankQrImageUrl' => $order->payment_method === Order::PAYMENT_METHOD_BANK_QR
                ? $vietQrCodeGenerator->imageUrl($order)
                : null,
        ]);
    }
}
