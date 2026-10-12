<?php

namespace Tests\Feature;

use App\Models\CustomCupIcon;
use App\Models\CustomCupSetting;
use App\Models\CustomCupVariant;
use App\Models\User;
use App\Services\CustomCupConfig;
use Database\Seeders\CustomCupConfigSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomCupConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CustomCupConfigSeeder::class);
    }

    public function test_guest_is_redirected_from_custom_cup_config_page(): void
    {
        $this->get(route('admin.custom-cup-config.edit'))
            ->assertRedirect('/dang-nhap');
    }

    public function test_client_cannot_access_custom_cup_config_page(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.custom-cup-config.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_view_custom_cup_config_page_with_seeded_data(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.custom-cup-config.edit'))
            ->assertOk()
            ->assertSee('Cấu hình cốc custom')
            ->assertSee('Quai đen – lòng đen')
            ->assertSee('Phân loại mẫu cốc')
            ->assertSee('💗');
    }

    public function test_admin_can_update_pricing_and_bulk_discount_settings(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.custom-cup-config.update-settings'), [
                'unit_price' => 120000,
                'size' => '15oz',
                'bulk_discount_minimum' => 10,
                'bulk_discount_percentage' => 15,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $settings = CustomCupSetting::current();
        $this->assertSame(120000, $settings->unit_price);
        $this->assertSame('15oz', $settings->size);
        $this->assertSame(10, $settings->bulk_discount_minimum);
        $this->assertSame(15, $settings->bulk_discount_percentage);

        app(CustomCupConfig::class)->refresh();

        $this->assertSame(120000, app(CustomCupConfig::class)->all()['unit_price']);
    }

    public function test_admin_cannot_save_an_invalid_color_hex_value(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.custom-cup-config.variants.store'), [
                'name' => 'Mẫu test',
                'handle_color' => 'đỏ',
                'interior_color' => '#000000',
            ])
            ->assertSessionHasErrors('handle_color');

        $this->assertDatabaseCount('custom_cup_variants', 3);
    }

    public function test_admin_can_create_update_and_delete_a_variant(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.custom-cup-config.variants.store'), [
                'name' => 'Quai xanh – lòng xanh',
                'handle_color' => '#1d8fce',
                'interior_color' => '#0b486b',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $variant = CustomCupVariant::query()->where('slug', 'quai_xanh_long_xanh')->firstOrFail();
        $this->assertTrue($variant->is_active);
        $this->assertSame('#1d8fce', $variant->handle_color);

        $this->actingAs($admin)
            ->patch(route('admin.custom-cup-config.variants.update', $variant), [
                'name' => 'Quai xanh dương',
                'handle_color' => '#3a5ad9',
                'interior_color' => '#0b486b',
                'is_active' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $variant->refresh();
        $this->assertSame('Quai xanh dương', $variant->name);
        $this->assertFalse($variant->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.custom-cup-config.variants.destroy', $variant))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('custom_cup_variants', ['id' => $variant->id]);
    }

    public function test_admin_can_create_and_delete_icons(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.custom-cup-config.icons.store'), ['glyph' => '🦄'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $icon = CustomCupIcon::query()->where('glyph', '🦄')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.custom-cup-config.icons.destroy', $icon))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('custom_cup_icons', ['id' => $icon->id]);
    }

    public function test_admin_cannot_duplicate_an_icon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.custom-cup-config.icons.store'), ['glyph' => '💗'])
            ->assertSessionHasErrors('glyph');
    }
}
