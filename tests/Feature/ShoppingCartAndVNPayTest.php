<?php

namespace Tests\Feature;

use App\Events\NewOrderCreated;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ShoppingCartAndVNPayTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_build_cart_but_must_sign_in_to_checkout(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock' => 5]);

        $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', [$product->id => 2]);

        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));
    }

    public function test_cart_rejects_inactive_products_and_quantities_over_stock(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock' => 2]);
        $inactiveProduct = Product::factory()->create(['is_active' => false, 'stock' => 10]);

        $this->from(route('products.show', $product->slug))
            ->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 3])
            ->assertRedirect(route('products.show', $product->slug))
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing('cart');

        $this->from(route('products.show', $product->slug))
            ->post(route('cart.store'), ['product_id' => $inactiveProduct->id, 'quantity' => 1])
            ->assertSessionHasErrors('product_id')
            ->assertSessionMissing('cart');
    }

    public function test_cart_rejects_more_than_ninety_nine_of_one_product(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock' => 150]);

        $this->from(route('products.show', $product->slug))
            ->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 100])
            ->assertRedirect(route('products.show', $product->slug))
            ->assertSessionHasErrors([
                'quantity' => 'Mỗi sản phẩm chỉ có thể đặt tối đa 99 chiếc trong một đơn hàng.',
            ])
            ->assertSessionMissing('cart');
    }

    public function test_cart_quantity_can_be_updated_and_product_removed(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock' => 8]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->put(route('cart.update', $product->id), ['quantity' => 4])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', [$product->id => 4]);

        $this->delete(route('cart.destroy', $product->id))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', []);
    }

    public function test_managed_product_page_and_cart_show_real_product_purchase_actions(): void
    {
        $this->withoutVite();
        $product = Product::factory()->create([
            'slug' => 'ly-gom-mua-he',
            'price' => 200000,
            'discount_percentage' => 10,
            'is_active' => true,
            'stock' => 6,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('id="product-cart-submit"', false)
            ->assertSee('value="'.$product->id.'"', false)
            ->assertSee('Thêm vào giỏ hàng');

        $this->withSession(['cart' => [$product->id => 2]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('180.000đ')
            ->assertSee('360.000đ')
            ->assertSee('Miễn phí');
    }

    public function test_checkout_uses_discounted_database_price_reserves_stock_and_creates_signed_vnpay_payment(): void
    {
        $this->configureVNPay();
        $user = User::factory()->create([
            'phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
        ]);
        $product = Product::factory()->create([
            'price' => 250000,
            'discount_percentage' => 20,
            'is_active' => true,
            'stock' => 4,
        ]);
        Queue::fake();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('value="0901234567"', false)
            ->assertSee('data-selected-province="Thành phố Hà Nội"', false)
            ->assertSee('data-selected-ward="Phường Ba Đình"', false)
            ->assertSee('12 Phố Huế');

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Nguyễn An',
                'customer_email' => 'an@example.com',
                'customer_phone' => '0901234567',
                'shipping_province' => 'Thành phố Hà Nội',
                'shipping_ward' => 'Phường Ba Đình',
                'shipping_address' => '12 Phố Huế',
                'subtotal' => 1,
                'shipping_fee' => 0,
                'total' => 1,
            ]);

        $response->assertRedirect();
        $this->assertStringStartsWith(
            'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?',
            $response->headers->get('Location'),
        );

        $order = Order::query()->with('items')->firstOrFail();
        Queue::assertPushed(
            BroadcastEvent::class,
            fn (BroadcastEvent $job): bool => $job->event instanceof NewOrderCreated
                && $job->event->orderType === 'regular'
                && $job->event->orderNumber === $order->order_number
                && $job->event->customerName === $order->customer_name
                && $job->event->totalAmount === 230000
                && $job->event->url === route('admin.orders.show', $order),
        );
        $this->assertModelExists($order);
        $this->assertSame(200000.0, (float) $order->subtotal);
        $this->assertSame(30000.0, (float) $order->shipping_fee);
        $this->assertSame(230000.0, (float) $order->total);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertSame('Thành phố Hà Nội', $order->shipping_province);
        $this->assertSame('Phường Ba Đình', $order->shipping_ward);
        $this->assertSame(200000.0, (float) $order->items->first()->unit_price);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
        ]);
        $this->assertFalse(session()->has('cart'));

        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $paymentParameters);
        $signature = $paymentParameters['vnp_SecureHash'];
        unset($paymentParameters['vnp_SecureHash']);
        ksort($paymentParameters);
        $this->assertSame(
            hash_hmac('sha512', http_build_query($paymentParameters, '', '&', PHP_QUERY_RFC1738), 'test-vnpay-secret'),
            $signature,
        );
        $this->assertSame('23000000', $paymentParameters['vnp_Amount']);
    }

    public function test_checkout_applies_free_shipping_at_the_threshold(): void
    {
        $this->configureVNPay();
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 300000,
            'discount_percentage' => 0,
            'is_active' => true,
            'stock' => 2,
        ]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Nguyễn An',
                'customer_email' => 'an@example.com',
                'customer_phone' => '0901234567',
                'shipping_province' => 'Thành phố Hà Nội',
                'shipping_ward' => 'Phường Ba Đình',
                'shipping_address' => '12 Phố Huế',
            ])
            ->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(0.0, (float) $order->shipping_fee);
        $this->assertSame(300000.0, (float) $order->total);
    }

    public function test_checkout_places_cash_on_delivery_orders_without_vnpay_configuration(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100000,
            'discount_percentage' => 0,
            'is_active' => true,
            'stock' => 3,
        ]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('name="payment_method"', false)
            ->assertSee('value="cod"', false);

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '0901234567',
                'shipping_province' => 'Thành phố Hà Nội',
                'shipping_ward' => 'Phường Ba Đình',
                'shipping_address' => '12 Phố Huế',
                'payment_method' => 'cod',
            ]);

        $order = Order::query()->with('items')->firstOrFail();
        $response->assertRedirect(route('orders.show', $order->order_number));
        $this->artisan('orders:expire-pending')->assertSuccessful();

        $order->refresh();
        $this->assertSame(Order::PAYMENT_METHOD_COD, $order->payment_method);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertNull($order->expires_at);
        $this->assertTrue($order->inventory_reserved);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertFalse(session()->has('cart'));

        $this->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Thanh toán khi nhận hàng')
            ->assertSee('sẽ thanh toán khi nhận hàng');
    }

    public function test_checkout_creates_bank_qr_order_with_a_snapshot_of_payment_details(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100000,
            'is_active' => true,
            'stock' => 3,
        ]);
        PaymentSetting::query()->create([
            'bank_code' => 'VCB',
            'bank_name' => 'Vietcombank',
            'account_number' => '0123456789',
            'account_name' => 'NGUYEN VAN A',
            'is_enabled' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('value="bank_qr"', false);

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '0901234567',
                'shipping_province' => 'Thành phố Hà Nội',
                'shipping_ward' => 'Phường Ba Đình',
                'shipping_address' => '12 Phố Huế',
                'payment_method' => Order::PAYMENT_METHOD_BANK_QR,
            ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('orders.show', $order->order_number));
        $this->assertSame(Order::PAYMENT_METHOD_BANK_QR, $order->payment_method);
        $this->assertSame('VCB', $order->payment_bank_code);
        $this->assertSame('Vietcombank', $order->payment_bank_name);
        $this->assertSame('0123456789', $order->payment_account_number);
        $this->assertSame('NGUYEN VAN A', $order->payment_account_name);
        $this->assertNull($order->payment_qr_image_path);
        $this->assertSame(130000.0, (float) $order->total);
        $this->assertNull($order->expires_at);
        $this->assertSame(2, $product->fresh()->stock);

        $this->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Chuyển khoản ngân hàng qua QR')
            ->assertSee('0123456789')
            ->assertSee('130.000đ')
            ->assertSee($order->order_number)
            ->assertSee('https://img.vietqr.io/image/VCB-0123456789-compact2.png?amount=130000&amp;addInfo='.$order->order_number.'&amp;accountName=NGUYEN%20VAN%20A', false);

        $order->update(['order_status' => Order::STATUS_CANCELLED]);

        $this->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Đơn hàng đã')
            ->assertSee('được hủy.')
            ->assertDontSee('img.vietqr.io', false);
    }

    public function test_checkout_rejects_bank_qr_payment_when_not_configured(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => true, 'stock' => 3]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '0901234567',
                'shipping_province' => 'Thành phố Hà Nội',
                'shipping_ward' => 'Phường Ba Đình',
                'shipping_address' => '12 Phố Huế',
                'payment_method' => Order::PAYMENT_METHOD_BANK_QR,
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_checkout_rejects_unknown_payment_methods_without_reserving_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => true, 'stock' => 3]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '0901234567',
                'shipping_province' => 'Thành phố Hà Nội',
                'shipping_ward' => 'Phường Ba Đình',
                'shipping_address' => '12 Phố Huế',
                'payment_method' => 'transfer',
            ])
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_checkout_without_vnpay_credentials_does_not_create_an_order_or_reserve_stock(): void
    {
        config([
            'services.vnpay.tmn_code' => null,
            'services.vnpay.hash_secret' => null,
        ]);
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => true, 'stock' => 3]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'customer_name' => 'Nguyễn An',
                'customer_email' => 'an@example.com',
                'customer_phone' => '0901234567',
                'shipping_province' => 'Thành phố Hà Nội',
                'shipping_ward' => 'Phường Ba Đình',
                'shipping_address' => '12 Phố Huế',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_vnpay_ipn_marks_a_verified_order_paid_and_duplicate_notifications_are_idempotent(): void
    {
        $this->configureVNPay();
        [$order, $product] = $this->createReservedOrder();
        $payload = $this->signedVNPayPayload($order, [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '12345678',
            'vnp_BankCode' => 'NCB',
        ]);

        $this->postJson(route('payments.vnpay.ipn'), $payload)
            ->assertOk()
            ->assertJsonPath('RspCode', '00');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_PROCESSING, $order->order_status);
        $this->assertFalse($order->inventory_reserved);
        $this->assertSame('12345678', $order->vnp_transaction_no);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(4, $product->fresh()->stock);

        $this->postJson(route('payments.vnpay.ipn'), $payload)
            ->assertOk()
            ->assertJsonPath('RspCode', '00');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
    }

    public function test_vnpay_ipn_rejects_invalid_signatures_and_amount_mismatches(): void
    {
        $this->configureVNPay();
        [$order, $product] = $this->createReservedOrder();
        $payload = $this->signedVNPayPayload($order, [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ]);
        $payload['vnp_Amount'] = '1';

        $this->postJson(route('payments.vnpay.ipn'), $payload)
            ->assertOk()
            ->assertJsonPath('RspCode', '97');

        $payload = $this->signedVNPayPayload($order, [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ]);
        $payload['vnp_Amount'] = '1';
        $payload = $this->signPayload($payload);

        $this->postJson(route('payments.vnpay.ipn'), $payload)
            ->assertOk()
            ->assertJsonPath('RspCode', '04');

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
        $this->assertTrue($order->fresh()->inventory_reserved);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_failed_vnpay_ipn_releases_reserved_inventory_only_once(): void
    {
        $this->configureVNPay();
        [$order, $product] = $this->createReservedOrder(quantity: 2);
        $payload = $this->signedVNPayPayload($order, [
            'vnp_ResponseCode' => '24',
            'vnp_TransactionStatus' => '02',
        ]);

        $this->postJson(route('payments.vnpay.ipn'), $payload)
            ->assertOk()
            ->assertJsonPath('RspCode', '00');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_FAILED, $order->payment_status);
        $this->assertSame(Order::STATUS_CANCELLED, $order->order_status);
        $this->assertFalse($order->inventory_reserved);
        $this->assertSame(5, $product->fresh()->stock);

        $this->postJson(route('payments.vnpay.ipn'), $payload)
            ->assertOk()
            ->assertJsonPath('RspCode', '00');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_expired_orders_release_reserved_inventory_once(): void
    {
        [$order, $product] = $this->createReservedOrder(quantity: 2, expiresAt: now()->subMinute());

        $this->artisan('orders:expire-pending')->assertSuccessful();

        $order->refresh();
        $this->assertSame(Order::PAYMENT_FAILED, $order->payment_status);
        $this->assertSame(Order::STATUS_EXPIRED, $order->order_status);
        $this->assertFalse($order->inventory_reserved);
        $this->assertSame(5, $product->fresh()->stock);

        Artisan::call('orders:expire-pending');
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_late_verified_payment_is_recorded_for_manual_review_after_inventory_release(): void
    {
        $this->configureVNPay();
        [$order, $product] = $this->createReservedOrder(quantity: 2, expiresAt: now()->subMinute());
        $this->artisan('orders:expire-pending')->assertSuccessful();
        $payload = $this->signedVNPayPayload($order, [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '98765432',
        ]);

        $this->postJson(route('payments.vnpay.ipn'), $payload)
            ->assertOk()
            ->assertJsonPath('RspCode', '00');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_NEEDS_REVIEW, $order->order_status);
        $this->assertFalse($order->inventory_reserved);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_vnpay_return_never_marks_payment_paid_without_the_ipn(): void
    {
        $this->configureVNPay();
        $user = User::factory()->create();
        [$order] = $this->createReservedOrder(user: $user);
        $payload = $this->signedVNPayPayload($order, [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ]);

        $this->actingAs($user)
            ->get(route('payments.vnpay.return', $payload))
            ->assertRedirect(route('orders.show', $order->order_number));

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    public function test_order_confirmation_is_only_visible_to_its_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        [$order] = $this->createReservedOrder(user: $owner);

        $this->actingAs($owner)
            ->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('12 Phố Huế, Phường Ba Đình, Thành phố Hà Nội');

        $this->actingAs($otherUser)
            ->get(route('orders.show', $order->order_number))
            ->assertNotFound();
    }

    /**
     * @return array{0: Order, 1: Product}
     */
    private function createReservedOrder(?User $user = null, int $quantity = 1, ?Carbon $expiresAt = null): array
    {
        $user ??= User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100000,
            'discount_percentage' => 0,
            'is_active' => true,
            'stock' => 5,
        ]);
        $product->decrement('stock', $quantity);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => strtoupper(fake()->unique()->bothify('ORDER-????????')),
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
            'subtotal' => 100000 * $quantity,
            'shipping_fee' => 0,
            'total' => 100000 * $quantity,
            'order_status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_method' => 'vnpay',
            'inventory_reserved' => true,
            'expires_at' => $expiresAt ?? now()->addMinutes(15),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => 100000,
            'quantity' => $quantity,
            'line_total' => 100000 * $quantity,
        ]);

        return [$order, $product];
    }

    /**
     * @param  array<string, string>  $additionalParameters
     * @return array<string, string>
     */
    private function signedVNPayPayload(Order $order, array $additionalParameters = []): array
    {
        return $this->signPayload(array_merge([
            'vnp_TmnCode' => 'TESTMERCHANT',
            'vnp_TxnRef' => $order->order_number,
            'vnp_Amount' => (string) ((int) round((float) $order->total * 100)),
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ], $additionalParameters));
    }

    /**
     * @param  array<string, string>  $parameters
     * @return array<string, string>
     */
    private function signPayload(array $parameters): array
    {
        unset($parameters['vnp_SecureHash']);
        ksort($parameters);
        $parameters['vnp_SecureHash'] = hash_hmac(
            'sha512',
            http_build_query($parameters, '', '&', PHP_QUERY_RFC1738),
            'test-vnpay-secret',
        );

        return $parameters;
    }

    private function configureVNPay(): void
    {
        config([
            'services.vnpay.tmn_code' => 'TESTMERCHANT',
            'services.vnpay.hash_secret' => 'test-vnpay-secret',
            'services.vnpay.payment_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
        ]);
    }
}
