<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('detail-intro', false)
            ->assertSee('Thông tin sản phẩm');
        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertSee('class="cup-storefront', false)
            ->assertSee('Một chút yêu thương');
        $this->get('/lien-he')
            ->assertOk()
            ->assertSee('class="cup-storefront', false)
            ->assertSee('Gửi lời nhắn cho chúng mình');
    }

    public function test_unknown_product_returns_not_found(): void
    {
        $this->get('/san-pham/khong-ton-tai')->assertNotFound();
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
