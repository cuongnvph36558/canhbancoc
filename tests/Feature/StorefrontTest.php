<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_pages_are_available(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertOk()
            ->assertSee('Một chiếc cốc')
            ->assertSee('đậm chất')
            ->assertDontSee('cup-cloud-transition', false)
            ->assertSee('Cốc Mơ')
            ->assertSee('Cốc in hình thú cưng');
        $this->get('/san-pham')
            ->assertOk()
            ->assertSee('class="cup-storefront', false)
            ->assertSee('Cốc in hình thú cưng');
        $this->get('/san-pham/coc-su-in-hinh-thu-cung')
            ->assertOk()
            ->assertSee('class="product-page', false)
            ->assertSee('data-product-main-image', false)
            ->assertSee('photo-1514228742587-6b1558fcca3d', false)
            ->assertSee('Về sản phẩm')
            ->assertSee('Đặt hàng trong 3 bước');
        $this->get(route('products.customize'))
            ->assertOk()
            ->assertSee('Thiết kế cốc của bạn')
            ->assertSee('data-cup-variant-option', false)
            ->assertSee('data-add-icon', false)
            ->assertSee('data-cup-quantity', false)
            ->assertSee('customer_name', false);
        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertSee('class="cup-storefront', false)
            ->assertSee('Một chút yêu thương');
        $this->get('/lien-he')
            ->assertOk()
            ->assertSee('class="cup-storefront', false)
            ->assertSee('Gửi lời nhắn cho chúng mình');
    }

    public function test_storefront_header_shows_only_primary_links_and_floating_cart_and_chat_for_guests(): void
    {
        $this->withoutVite();

        $this->get(route('products.customize'))
            ->assertOk()
            ->assertSee('Trang chủ')
            ->assertSee('Sản phẩm')
            ->assertSee('Tự custom')
            ->assertSee('Giới thiệu')
            ->assertSee('Liên hệ')
            ->assertDontSee('Theo dõi đơn hàng')
            ->assertDontSee('Giỏ hàng (')
            ->assertSee('aria-label="Mở giỏ hàng"', false)
            ->assertSee('aria-label="Tin nhắn"', false)
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_authenticated_storefront_header_shows_order_tracking_with_username_and_logout(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['name' => 'Nguyễn An']);

        $this->actingAs($user)
            ->get(route('products.customize'))
            ->assertOk()
            ->assertSee('Xin chào, Nguyễn An')
            ->assertSee('Theo dõi đơn hàng')
            ->assertSee('href="'.route('customer.orders.index').'"', false)
            ->assertSee('Đăng xuất')
            ->assertDontSee('Quản trị')
            ->assertSee('href="'.route('chat.index').'"', false)
            ->assertSee('aria-label="Mở giỏ hàng"', false);
    }

    public function test_customizer_choices_prefill_the_contact_form(): void
    {
        $this->withoutVite();

        $this->get(route('contact', [
            'subject' => 'Đặt cốc tự custom',
            'message' => 'Màu: Hồng phấn; biểu tượng: Trái tim; tên: An Nhiên.',
        ]))
            ->assertOk()
            ->assertSee('value="Đặt cốc tự custom"', false)
            ->assertSee('Màu: Hồng phấn; biểu tượng: Trái tim; tên: An Nhiên.');
    }

    public function test_unknown_product_returns_not_found(): void
    {
        $this->get('/san-pham/khong-ton-tai')->assertNotFound();
    }

    public function test_active_managed_products_and_their_images_appear_on_the_storefront(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        Storage::disk('public')->put('products/front.jpg', 'front-image');
        Storage::disk('public')->put('products/side.jpg', 'side-image');
        $product = Product::factory()->create([
            'name' => 'Bình gốm thủ công',
            'slug' => 'binh-gom-thu-cong',
            'image_url' => null,
            'is_active' => true,
            'stock' => 8,
        ]);
        $primaryImage = $product->images()->create([
            'image_url' => 'products/front.jpg',
            'position' => 0,
            'is_primary' => true,
        ]);
        $secondaryImage = $product->images()->create([
            'image_url' => 'products/side.jpg',
            'position' => 1,
            'is_primary' => false,
        ]);
        Product::factory()->create([
            'name' => 'Sản phẩm đã ẩn',
            'slug' => 'san-pham-da-an',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Bình gốm thủ công')
            ->assertSee($primaryImage->url(), false)
            ->assertDontSee('Sản phẩm đã ẩn');

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Bình gốm thủ công')
            ->assertSee($primaryImage->url(), false)
            ->assertDontSee('Sản phẩm đã ẩn');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('data-product-gallery', false)
            ->assertSee('data-product-thumbnail', false)
            ->assertSee('data-product-main-image', false)
            ->assertSee($primaryImage->url(), false)
            ->assertSee($secondaryImage->url(), false)
            ->assertSee('Sẵn sàng giao hàng — còn 8 sản phẩm')
            ->assertSee('Gọi 090 123 4567')
            ->assertSee('Đặt hàng trong 3 bước')
            ->assertSee('subject=', false);
    }

    public function test_product_detail_shows_unavailable_status_when_stock_is_empty(): void
    {
        $this->withoutVite();
        $product = Product::factory()->create([
            'slug' => 'ly-gom-tam-het',
            'is_active' => true,
            'stock' => 0,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Tạm hết hàng — liên hệ để được tư vấn')
            ->assertSee('Đặt hàng / Nhờ tư vấn');
    }

    public function test_contact_form_stores_a_valid_inquiry(): void
    {
        $response = $this->post('/lien-he', [
            'name' => 'Nguyễn An',
            'email' => 'an@example.com',
            'phone' => '0901234567',
            'subject' => 'Hỏi về sản phẩm',
            'message' => 'Mình muốn tìm hiểu thêm về chiếc bình gốm.',
        ]);

        $response->assertRedirect('/lien-he')->assertSessionHas('success');
        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Nguyễn An',
            'email' => 'an@example.com',
            'subject' => 'Hỏi về sản phẩm',
        ]);
        $this->assertSame(1, ContactInquiry::count());
    }

    public function test_contact_form_rejects_invalid_input(): void
    {
        $this->from('/lien-he')->post('/lien-he', [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => '',
        ])->assertRedirect('/lien-he')->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertDatabaseCount('contact_inquiries', 0);
    }
}
