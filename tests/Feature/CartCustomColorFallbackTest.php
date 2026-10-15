<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCustomColorFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_to_cart_falls_back_to_first_active_color_when_form_color_is_empty(): void
    {
        $customer = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::query()->create([
            'name' => 'Cốc hồng đậm',
            'slug' => 'coc-hong-dam',
            'description' => 'Test product',
            'category_id' => $category->id,
            'price' => 150000,
            'discount_percentage' => 0,
            'image_url' => 'https://example.com/cup.jpg',
            'stock' => 10,
            'is_active' => true,
            'sku' => 'CUP-HONG-001',
        ]);

        ProductColor::query()->create([
            'product_id' => $product->id,
            'name' => 'Hồng đậm',
            'hex_code' => '#9A185E',
            'is_active' => true,
        ]);
        ProductColor::query()->create([
            'product_id' => $product->id,
            'name' => 'Trắng',
            'hex_code' => '#FFFFFF',
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.store'), [
                'product_id' => $product->id,
                'quantity' => 1,
                'custom_text' => null,
                'custom_color' => '',
                'custom_color_hex' => '',
                'custom_image' => null,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success');

        $cart = session('cart');
        $this->assertNotEmpty($cart);
        $cartItem = $cart[$product->id];
        $this->assertSame('Hồng đậm', $cartItem['custom_color']);
        $this->assertSame('#9A185E', $cartItem['custom_color_hex']);
        $this->assertNotSame('#FF00FF', $cartItem['custom_color_hex']);
        $this->assertNotSame('DEFAULT_SET', $cartItem['custom_color']);
    }

    public function test_add_to_cart_stores_only_valid_submitted_color_values(): void
    {
        $customer = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::query()->create([
            'name' => 'Cốc custom',
            'slug' => 'coc-custom',
            'description' => 'Test',
            'category_id' => $category->id,
            'price' => 200000,
            'discount_percentage' => 0,
            'image_url' => 'https://example.com/cup.jpg',
            'stock' => 5,
            'is_active' => true,
            'sku' => 'CUP-CUSTOM-001',
        ]);
        ProductColor::query()->create([
            'product_id' => $product->id,
            'name' => 'Xanh dương',
            'hex_code' => '#1E40AF',
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
                'custom_text' => 'Happy Birthday',
                'custom_color' => 'Xanh dương',
                'custom_color_hex' => '#1E40AF',
                'custom_image' => null,
            ])
            ->assertRedirect(route('cart.index'));

        $cartItem = session('cart')[$product->id];
        $this->assertSame('Xanh dương', $cartItem['custom_color']);
        $this->assertSame('#1E40AF', $cartItem['custom_color_hex']);
        $this->assertSame(2, $cartItem['quantity']);
        $this->assertSame('Happy Birthday', $cartItem['custom_text']);
    }
}
