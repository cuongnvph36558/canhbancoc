<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDiscountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_discount_percentage_and_storefront_displays_discounted_price(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Ly gốm handmade',
                'sku' => 'LY-DISCOUNT-001',
                'price' => 250000,
                'discount_percentage' => 20,
                'stock' => 12,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('sku', 'LY-DISCOUNT-001')->firstOrFail();

        $this->assertSame(20, $product->discount_percentage);
        $this->assertSame(200000.0, round($product->discountedPrice(), 2));

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('name="discount_percentage" value="20"', false);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('-20%')
            ->assertSee('200.000');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('200.000')
            ->assertSee('Giảm 20%');
    }

    public function test_admin_can_update_discount_percentage_and_storefront_refreshes_the_discount(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create([
            'price' => 250000,
            'discount_percentage' => 20,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'discount_percentage' => 30,
                'stock' => $product->stock,
            ])
            ->assertRedirect(route('admin.products.show', $product));

        $product->refresh();
        $this->assertSame(30, $product->discount_percentage);
        $this->assertSame(175000.0, round($product->discountedPrice(), 2));

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('-30%')
            ->assertSee('175.000');
    }

    public function test_admin_cannot_save_discount_percentage_above_one_hundred(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['discount_percentage' => 15]);

        $this->actingAs($admin)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'discount_percentage' => 101,
                'stock' => $product->stock,
            ])
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasErrors('discount_percentage');

        $this->assertSame(15, $product->fresh()->discount_percentage);
    }
}
