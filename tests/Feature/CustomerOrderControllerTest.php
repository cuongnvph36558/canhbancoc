<?php

namespace Tests\Feature;

use App\Models\CustomCupOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_customer_order_tracking(): void
    {
        $this->get(route('customer.orders.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_sees_only_their_regular_and_custom_orders(): void
    {
        $this->withoutVite();
        Storage::fake('local');
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        [$regularOrder] = $this->createRegularOrder($customer, 'REGULAR-OWN-001');
        $customOrder = $this->createCustomOrder($customer, 'CUSTOM-OWN-001', 'customer-orders/previews/custom.png');
        Storage::disk('local')->put($customOrder->preview_path, 'preview-image');
        $this->createRegularOrder($otherCustomer, 'REGULAR-OTHER-001');
        $this->createCustomOrder($otherCustomer, 'CUSTOM-OTHER-001');

        $this->actingAs($customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Đơn cốc thường')
            ->assertSee('Đơn cốc custom')
            ->assertSee($regularOrder->order_number)
            ->assertSee($customOrder->order_number)
            ->assertSee('src="https://example.com/customer-order-cup.jpg"', false)
            ->assertSee('src="'.route('customer.custom-cup-orders.preview', $customOrder).'"', false)
            ->assertDontSee('REGULAR-OTHER-001')
            ->assertDontSee('CUSTOM-OTHER-001');
    }

    public function test_customer_can_view_custom_order_details_and_preview(): void
    {
        $this->withoutVite();
        Storage::fake('local');
        $customer = User::factory()->create();
        $order = $this->createCustomOrder($customer, 'CUSTOM-DETAIL-001', 'customer-orders/previews/detail.png');
        Storage::disk('local')->put($order->preview_path, 'preview-image');

        $this->actingAs($customer)
            ->get(route('customer.custom-cup-orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Chờ xác nhận')
            ->assertSee($order->customer_name)
            ->assertSee('Thiết kế riêng')
            ->assertSee('src="'.route('customer.custom-cup-orders.preview', $order).'"', false);

        $this->get(route('customer.custom-cup-orders.preview', $order))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_customer_can_see_a_generated_preview_for_an_order_without_a_saved_preview_file(): void
    {
        $this->withoutVite();
        Storage::fake('local');
        $customer = User::factory()->create();
        $order = $this->createCustomOrder($customer, 'CUSTOM-FALLBACK-001');

        $this->actingAs($customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('src="'.route('customer.custom-cup-orders.preview', $order).'"', false);

        $this->actingAs($customer)
            ->get(route('customer.custom-cup-orders.show', $order))
            ->assertOk()
            ->assertSee('src="'.route('customer.custom-cup-orders.preview', $order).'"', false);

        $this->get(route('customer.custom-cup-orders.preview', $order))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('Thiết kế riêng');
    }

    public function test_customer_cannot_view_another_customers_order_or_preview(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = $this->createCustomOrder($otherCustomer, 'CUSTOM-PRIVATE-001', 'customer-orders/previews/private.png');

        $this->actingAs($customer)
            ->get(route('customer.custom-cup-orders.show', $order))
            ->assertNotFound();

        $this->get(route('customer.custom-cup-orders.preview', $order))
            ->assertNotFound();
    }

    /**
     * @return array{0: Order, 1: Product}
     */
    private function createRegularOrder(User $customer, string $orderNumber): array
    {
        $product = Product::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => $orderNumber,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '0901234567',
            'shipping_address' => '12 Phố Huế',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total' => 130000,
            'order_status' => Order::STATUS_PROCESSING,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => Order::PAYMENT_METHOD_VNPAY,
            'inventory_reserved' => false,
            'expires_at' => now()->addMinutes(15),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_image' => 'https://example.com/customer-order-cup.jpg',
            'unit_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        return [$order, $product];
    }

    private function createCustomOrder(User $customer, string $orderNumber, ?string $previewPath = null): CustomCupOrder
    {
        return CustomCupOrder::query()->create([
            'user_id' => $customer->id,
            'order_number' => $orderNumber,
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'shipping_address' => '12 Phố Huế',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'cup_variant' => 'black',
            'cup_size' => '11oz',
            'quantity' => 1,
            'unit_price' => 89000,
            'discount_percentage' => 0,
            'total_amount' => 89000,
            'design' => [
                'items' => [
                    [
                        'type' => 'text',
                        'content' => 'Thiết kế riêng',
                        'x' => 50,
                        'y' => 50,
                        'width' => 30,
                    ],
                ],
            ],
            'preview_path' => $previewPath,
            'status' => 'pending',
        ]);
    }
}
