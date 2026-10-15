<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductColorNameOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_color_without_hex_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Cốc custom',
            'sku' => 'CUP-NOHEX-001',
            'price' => 150000,
            'stock' => 5,
            'category_id' => $category->id,
            'is_active' => 1,
            'colors' => [
                ['name' => 'Hồng pastel', 'hex_code' => null],
                ['name' => 'Xanh mint'],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('sku', 'CUP-NOHEX-001')->firstOrFail();
        $colors = $product->colors()->orderBy('id')->get();

        $this->assertCount(2, $colors);
        $this->assertSame('Hồng pastel', $colors[0]->name);
        $this->assertNull($colors[0]->hex_code);
        $this->assertSame('Xanh mint', $colors[1]->name);
        $this->assertNull($colors[1]->hex_code);
    }

    public function test_admin_can_mix_name_only_and_hex_colors_on_a_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Cốc mix',
            'sku' => 'CUP-MIX-001',
            'price' => 180000,
            'stock' => 3,
            'category_id' => $category->id,
            'is_active' => 1,
            'colors' => [
                ['name' => 'Đen', 'hex_code' => '#000000'],
                ['name' => 'Hồng đậm', 'hex_code' => '#9A185E'],
                ['name' => 'Special Edition'],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('sku', 'CUP-MIX-001')->firstOrFail();
        $colors = $product->colors()->orderBy('id')->get();

        $this->assertCount(3, $colors);
        $this->assertSame('#000000', $colors[0]->hex_code);
        $this->assertSame('#9A185E', $colors[1]->hex_code);
        $this->assertNull($colors[2]->hex_code);
    }

    public function test_name_only_color_is_kept_when_updating_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        ProductColor::query()->create([
            'product_id' => $product->id,
            'name' => 'Special Edition',
            'hex_code' => null,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'stock' => $product->stock,
            'colors' => [
                ['name' => 'Special Edition', 'hex_code' => null],
            ],
        ])->assertRedirect(route('admin.products.show', $product));

        $color = $product->colors()->firstOrFail();
        $this->assertSame('Special Edition', $color->name);
        $this->assertNull($color->hex_code);
    }

    public function test_storefront_renders_name_only_color_without_hex_in_data_attribute(): void
    {
        $product = Product::factory()->create();
        ProductColor::query()->create([
            'product_id' => $product->id,
            'name' => 'Pastel',
            'hex_code' => null,
            'is_active' => true,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Pastel', false)
            ->assertSee('data-has-hex="0"', false);
    }
}
