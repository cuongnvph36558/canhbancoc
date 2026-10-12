<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_categories_index_and_see_stats(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        Category::factory()->create(['name' => 'Cốc sứ', 'slug' => 'coc-su']);
        Category::factory()->create(['name' => 'Cốc thủy tinh', 'slug' => 'coc-thuy-tinh', 'is_active' => false]);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Phân loại sản phẩm')
            ->assertSee('Cốc sứ')
            ->assertSee('Cốc thủy tinh')
            ->assertSee('Đang hiển thị')
            ->assertSee('Đã ẩn');
    }

    public function test_admin_can_create_a_category_with_auto_generated_slug(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Phụ kiện cốc',
            'description' => 'Ốp, nắp, túi đựng cốc.',
            'icon' => '✨',
            'position' => 5,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::first();

        $this->assertNotNull($category);
        $this->assertSame('Phụ kiện cốc', $category->name);
        $this->assertSame('phu-kien-coc', $category->slug);
        $this->assertSame('✨', $category->icon);
        $this->assertSame(5, $category->position);
        $this->assertTrue($category->is_active);
    }

    public function test_admin_can_update_a_category(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create([
            'name' => 'Cốc cũ',
            'slug' => 'coc-cu',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Cốc mới',
                'slug' => 'coc-moi',
                'description' => 'Đã cập nhật',
                'icon' => '♡',
                'position' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category->refresh();

        $this->assertSame('Cốc mới', $category->name);
        $this->assertSame('coc-moi', $category->slug);
        $this->assertSame('♡', $category->icon);
        $this->assertSame(2, $category->position);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.categories.toggle-status', $category))
            ->assertRedirect();

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_admin_cannot_delete_a_category_with_products(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_an_empty_category(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_non_admin_users_cannot_access_category_routes(): void
    {
        $this->withoutVite();

        $guest = User::factory()->create(['role' => 'client']);

        $this->actingAs($guest)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_admin_categories_index_filters_by_status(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => 'admin']);
        Category::factory()->create(['name' => 'Hiện', 'slug' => 'hien', 'is_active' => true]);
        Category::factory()->create(['name' => 'Ẩn', 'slug' => 'an', 'is_active' => false]);

        $this->actingAs($admin)
            ->get(route('admin.categories.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Ẩn')
            ->assertDontSee('Hiện');
    }
}
