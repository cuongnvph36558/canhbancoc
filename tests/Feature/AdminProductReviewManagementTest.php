<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_access_review_management(): void
    {
        $this->get(route('admin.product-reviews.index'))
            ->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.product-reviews.index'))
            ->assertForbidden();
    }

    public function test_admin_can_search_and_filter_reviews(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createReview('Bình gốm', 'Khách tìm kiếm', 2, '<script>đẹp</script>');
        $this->createReview('Cốc hoa', 'Khách khác', 5, 'Đánh giá riêng.');

        $this->actingAs($admin)
            ->get(route('admin.product-reviews.index', [
                'search' => 'Bình gốm',
                'rating' => 2,
            ]))
            ->assertOk()
            ->assertSee('Bình gốm')
            ->assertSee('Khách tìm kiếm')
            ->assertSee('&lt;script&gt;đẹp&lt;/script&gt;', false)
            ->assertDontSee('<script>đẹp</script>', false)
            ->assertDontSee('Cốc hoa')
            ->assertDontSee('Đánh giá riêng.');
    }

    public function test_admin_can_delete_an_inappropriate_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = $this->createReview('Cốc gốm', 'Khách hàng', 1, 'Nội dung cần xóa.');

        $this->actingAs($admin)
            ->delete(route('admin.product-reviews.destroy', $review))
            ->assertRedirect()
            ->assertSessionHas('success', 'Đánh giá đã được xóa khỏi hệ thống.');

        $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
    }

    public function test_client_cannot_delete_a_review(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $review = $this->createReview('Cốc gốm', 'Khách hàng', 1, 'Nội dung cần giữ.');

        $this->actingAs($client)
            ->delete(route('admin.product-reviews.destroy', $review))
            ->assertForbidden();

        $this->assertDatabaseHas('product_reviews', ['id' => $review->id]);
    }

    public function test_review_rating_filter_rejects_invalid_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.product-reviews.index', ['rating' => 6]))
            ->assertSessionHasErrors('rating');
    }

    private function createReview(string $productName, string $customerName, int $rating, string $comment): ProductReview
    {
        $customer = User::factory()->create(['name' => $customerName]);
        $product = Product::factory()->create(['name' => $productName]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'REVIEW-'.strtoupper(fake()->unique()->bothify('??????')),
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total' => 130000,
            'order_status' => Order::STATUS_DELIVERED,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'inventory_reserved' => false,
            'expires_at' => null,
        ]);
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        return ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_item_id' => $orderItem->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }
}
