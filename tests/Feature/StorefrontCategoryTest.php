<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_shows_categories_chips(): void
    {
        $this->withoutVite();

        $ceramic = Category::factory()->create(['name' => 'Cốc sứ', 'slug' => 'coc-su', 'is_active' => true]);
        Product::factory()->create([
            'name' => 'Cốc sứ hoa mộc',
            'slug' => 'coc-su-hoa-moc',
            'is_active' => true,
            'category_id' => $ceramic->id,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('coc-su', false)
            ->assertSee('Cốc sứ')
            ->assertSee('Cốc sứ hoa mộc');
    }

    public function test_product_category_page_only_lists_active_products_in_that_category(): void
    {
        $this->withoutVite();

        $category = Category::factory()->create(['name' => 'Cốc thủy tinh', 'slug' => 'coc-thuy-tinh', 'is_active' => true]);
        $other = Category::factory()->create(['name' => 'Bình trà', 'slug' => 'binh-tra', 'is_active' => true]);

        Product::factory()->create([
            'name' => 'Cốc thủy tinh xanh',
            'slug' => 'coc-thuy-tinh-xanh',
            'is_active' => true,
            'category_id' => $category->id,
        ]);
        Product::factory()->create([
            'name' => 'Bình trà Bát Tràng',
            'slug' => 'binh-tra-bat-trang',
            'is_active' => true,
            'category_id' => $other->id,
        ]);
        Product::factory()->create([
            'name' => 'Cốc ẩn',
            'slug' => 'coc-an',
            'is_active' => false,
            'category_id' => $category->id,
        ]);

        $this->get(route('products.category', $category->slug))
            ->assertOk()
            ->assertSee('Cốc thủy tinh xanh')
            ->assertSee('Cốc thủy tinh')
            ->assertDontSee('Bình trà Bát Tràng')
            ->assertDontSee('Cốc ẩn');
    }

    public function test_inactive_category_route_returns_404(): void
    {
        $this->withoutVite();

        $category = Category::factory()->create(['slug' => 'inactive-cat', 'is_active' => false]);

        $this->get(route('products.category', $category->slug))->assertNotFound();
    }

    public function test_product_card_displays_category_name(): void
    {
        $this->withoutVite();

        $category = Category::factory()->create(['name' => 'Phụ kiện', 'slug' => 'phu-kien', 'is_active' => true]);
        $product = Product::factory()->create([
            'name' => 'Ốp cốc silicon',
            'slug' => 'op-coc-silicon',
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Phụ kiện');
    }

    public function test_admin_can_assign_category_when_creating_product(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['slug' => 'coc-giu-nhiet', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Cốc giữ nhiệt 500ml',
            'sku' => 'KEEP-001',
            'price' => 250000,
            'stock' => 20,
            'category_id' => $category->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Cốc giữ nhiệt 500ml',
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_can_reassign_category_when_editing_product(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        $oldCategory = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $oldCategory->id,
            'name' => 'Cốc cần đổi loại',
            'slug' => 'coc-can-doi-loai',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => 150000,
                'stock' => 5,
                'category_id' => $newCategory->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.products.show', $product));

        $this->assertSame($newCategory->id, $product->fresh()->category_id);
    }
}
