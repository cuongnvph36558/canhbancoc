<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_access_order_management(): void
    {
        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.orders.index'))
            ->assertForbidden();
    }

    public function test_admin_can_filter_orders_and_view_order_details(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        [$order] = $this->createOrder('COD-ORDER-001', 'cod');
        $this->createOrder('VN-ORDER-002', 'vnpay');

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['search' => 'COD-ORDER-001', 'status' => Order::STATUS_PENDING]))
            ->assertOk()
            ->assertSee('COD-ORDER-001')
            ->assertDontSee('VN-ORDER-002')
            ->assertSee('src="https://example.com/order-item-image.jpg"', false);

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->customer_name)
            ->assertSee($order->items->first()->product_name)
            ->assertSee('src="https://example.com/order-item-image.jpg"', false)
            ->assertSee('Thanh toán khi nhận hàng');
    }

    public function test_admin_sees_bank_qr_code_on_order_details(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::query()->create([
            'user_id' => $admin->id,
            'order_number' => 'QR-DETAIL-001',
            'customer_name' => 'Khách hàng A',
            'customer_email' => 'qr@example.com',
            'customer_phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total' => 130000,
            'order_status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_method' => Order::PAYMENT_METHOD_BANK_QR,
            'payment_bank_code' => 'TCB',
            'payment_bank_name' => 'Techcombank',
            'payment_account_number' => '1900000000',
            'payment_account_name' => 'COK MO',
            'inventory_reserved' => true,
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Mã QR chuyển khoản')
            ->assertSee('img.vietqr.io/image/TCB-1900000000-compact2.png', false);
    }

    public function test_admin_marks_cash_on_delivery_order_paid_when_delivered(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order] = $this->createOrder('COD-DELIVER-001', 'cod');

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order), ['order_status' => Order::STATUS_DELIVERED])
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(Order::STATUS_DELIVERED, $order->order_status);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertFalse($order->inventory_reserved);
    }

    public function test_cancelling_an_unpaid_order_releases_stock_only_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order, $product] = $this->createOrder('COD-CANCEL-001', 'cod');

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order), ['order_status' => Order::STATUS_CANCELLED])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertFalse($order->fresh()->inventory_reserved);

        $this->patch(route('admin.orders.update-status', $order), ['order_status' => Order::STATUS_PROCESSING])
            ->assertSessionHasErrors('order_status');

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->order_status);
    }

    public function test_admin_cannot_cancel_an_order_that_is_already_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order] = $this->createOrder('PAID-ORDER-001', 'vnpay', Order::PAYMENT_PAID);
        $order->update(['inventory_reserved' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order), ['order_status' => Order::STATUS_CANCELLED])
            ->assertSessionHasErrors([
                'order_status' => 'Không thể hủy đơn hàng đã thanh toán.',
            ]);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->order_status);
        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
    }

    public function test_admin_cannot_mark_unpaid_vnpay_order_as_delivered(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order] = $this->createOrder('VN-UNPAID-001', Order::PAYMENT_METHOD_VNPAY);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order), ['order_status' => Order::STATUS_DELIVERED])
            ->assertSessionHasErrors([
                'order_status' => 'Chỉ xử lý đơn thanh toán trước sau khi đã xác nhận thanh toán.',
            ]);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->order_status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    public function test_admin_cannot_process_unpaid_vnpay_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order, $product] = $this->createOrder('VN-PENDING-001', Order::PAYMENT_METHOD_VNPAY);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order), ['order_status' => Order::STATUS_PROCESSING])
            ->assertSessionHasErrors([
                'order_status' => 'Chỉ xử lý đơn thanh toán trước sau khi đã xác nhận thanh toán.',
            ]);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->order_status);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_admin_cannot_cancel_a_shipped_order_or_restore_its_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order, $product] = $this->createOrder('COD-SHIPPED-001', Order::PAYMENT_METHOD_COD);
        $order->update(['order_status' => Order::STATUS_SHIPPED]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order), ['order_status' => Order::STATUS_CANCELLED])
            ->assertSessionHasErrors([
                'order_status' => 'Không thể hủy đơn hàng đang được giao.',
            ]);

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->order_status);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertTrue($order->fresh()->inventory_reserved);
    }

    public function test_admin_can_confirm_a_pending_bank_qr_transfer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order] = $this->createOrder('QR-TRANSFER-001', Order::PAYMENT_METHOD_BANK_QR);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Xác nhận đã nhận chuyển khoản')
            ->assertSee('Chuyển khoản ngân hàng qua QR');

        $this->actingAs($admin)
            ->patch(route('admin.orders.confirm-bank-qr-payment', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(Order::STATUS_PENDING, $order->order_status);
    }

    public function test_admin_cannot_confirm_payment_for_an_order_that_is_not_pending_bank_qr(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order] = $this->createOrder('QR-TRANSFER-002', Order::PAYMENT_METHOD_VNPAY);

        $this->actingAs($admin)
            ->patch(route('admin.orders.confirm-bank-qr-payment', $order))
            ->assertSessionHasErrors([
                'payment' => 'Chỉ xác nhận được đơn chuyển khoản QR đang chờ thanh toán.',
            ]);

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
        $this->assertNull($order->fresh()->paid_at);
    }

    /**
     * @return array{0: Order, 1: Product}
     */
    private function createOrder(string $orderNumber, string $paymentMethod, string $paymentStatus = Order::PAYMENT_PENDING): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $product->decrement('stock');
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total' => 130000,
            'order_status' => Order::STATUS_PENDING,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'inventory_reserved' => $paymentStatus === Order::PAYMENT_PENDING,
            'expires_at' => $paymentMethod === Order::PAYMENT_METHOD_COD ? null : now()->addMinutes(15),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_image' => 'https://example.com/order-item-image.jpg',
            'unit_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        return [$order, $product];
    }
}
