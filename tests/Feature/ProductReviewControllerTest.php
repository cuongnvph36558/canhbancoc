<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_a_product_after_their_order_is_delivered(): void
    {
        $this->withoutVite();
        $customer = User::factory()->create();
        $product = Product::factory()->create(['is_active' => true]);
        [$order, $item] = $this->createOrderWithProduct($customer, $product, Order::STATUS_DELIVERED);

        $this->actingAs($customer)
            ->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Đánh giá '.$product->name)
            ->assertSee(route('customer.product-reviews.store', [$order->order_number, $item->id]), false);

        $this->post(route('customer.product-reviews.store', [$order->order_number, $item->id]), [
            'rating' => 5,
            'comment' => 'Sản phẩm rất đẹp và đóng gói cẩn thận.',
        ])
            ->assertRedirect(route('orders.show', $order->order_number))
            ->assertSessionHas('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_item_id' => $item->id,
            'rating' => 5,
            'comment' => 'Sản phẩm rất đẹp và đóng gói cẩn thận.',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('5.0/5')
            ->assertSee('Đã mua và nhận hàng')
            ->assertSee('Sản phẩm rất đẹp và đóng gói cẩn thận.');
    }

    public function test_customer_cannot_review_a_product_before_the_order_is_delivered(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        [$order, $item] = $this->createOrderWithProduct($customer, $product, Order::STATUS_SHIPPED);
        $reviewRoute = route('customer.product-reviews.store', [$order->order_number, $item->id]);

        $this->actingAs($customer)
            ->get(route('orders.show', $order->order_number))
            ->assertOk()
            ->assertDontSee($reviewRoute);

        $this->post($reviewRoute, ['rating' => 5])
            ->assertForbidden();

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_cannot_review_another_customers_order(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $product = Product::factory()->create();
        [$order, $item] = $this->createOrderWithProduct($otherCustomer, $product, Order::STATUS_DELIVERED);

        $this->actingAs($customer)
            ->post(route('customer.product-reviews.store', [$order->order_number, $item->id]), [
                'rating' => 5,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_cannot_review_an_item_from_a_different_order(): void
    {
        $customer = User::factory()->create();
        $firstProduct = Product::factory()->create();
        $secondProduct = Product::factory()->create();
        [$order] = $this->createOrderWithProduct($customer, $firstProduct, Order::STATUS_DELIVERED);
        [, $otherOrderItem] = $this->createOrderWithProduct($customer, $secondProduct, Order::STATUS_DELIVERED);

        $this->actingAs($customer)
            ->post(route('customer.product-reviews.store', [$order->order_number, $otherOrderItem->id]), [
                'rating' => 5,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_must_choose_a_rating_between_one_and_five(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        [$order, $item] = $this->createOrderWithProduct($customer, $product, Order::STATUS_DELIVERED);

        $this->actingAs($customer)
            ->from(route('orders.show', $order->order_number))
            ->post(route('customer.product-reviews.store', [$order->order_number, $item->id]), [
                'rating' => 6,
            ])
            ->assertRedirect(route('orders.show', $order->order_number))
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_cannot_submit_a_second_review_for_the_same_order_item(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        [$order, $item] = $this->createOrderWithProduct($customer, $product, Order::STATUS_DELIVERED);
        $reviewRoute = route('customer.product-reviews.store', [$order->order_number, $item->id]);

        $this->actingAs($customer)->post($reviewRoute, ['rating' => 4]);

        $this->from(route('orders.show', $order->order_number))
            ->post($reviewRoute, ['rating' => 2])
            ->assertRedirect(route('orders.show', $order->order_number))
            ->assertSessionHasErrors('review');

        $this->assertSame(1, ProductReview::query()->where('order_item_id', $item->id)->count());
    }

    /**
     * @return array{0: Order, 1: OrderItem}
     */
    private function createOrderWithProduct(User $customer, Product $product, string $status): array
    {
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'REVIEW-'.strtoupper(fake()->unique()->bothify('??????')),
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '0901234567',
            'shipping_address' => '12 Phố Huế',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total' => 130000,
            'order_status' => $status,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'inventory_reserved' => false,
            'expires_at' => null,
        ]);

        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        return [$order, $item];
    }
}
