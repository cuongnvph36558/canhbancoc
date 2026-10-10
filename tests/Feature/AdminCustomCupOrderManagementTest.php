<?php

namespace Tests\Feature;

use App\Models\CustomCupOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomCupOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_custom_order_status_for_customer_tracking(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $order = $this->createCustomOrder($customer);

        $this->actingAs($admin)
            ->patch(route('admin.custom-cup-orders.update-status', $order), ['status' => 'in_production'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('in_production', $order->fresh()->status);

        $this->actingAs($customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Đang sản xuất');
    }

    public function test_customer_cannot_update_custom_order_status(): void
    {
        $customer = User::factory()->create();
        $order = $this->createCustomOrder($customer);

        $this->actingAs($customer)
            ->patch(route('admin.custom-cup-orders.update-status', $order), ['status' => 'shipped'])
            ->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }

    private function createCustomOrder(User $customer): CustomCupOrder
    {
        return CustomCupOrder::query()->create([
            'user_id' => $customer->id,
            'order_number' => 'CM-261010-TRACK01',
            'customer_name' => $customer->name,
            'customer_phone' => '0901234567',
            'customer_email' => $customer->email,
            'shipping_address' => '12 Phố Huế',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'cup_variant' => 'black',
            'cup_size' => '11oz',
            'quantity' => 1,
            'unit_price' => 89000,
            'discount_percentage' => 0,
            'total_amount' => 89000,
            'design' => ['items' => [['type' => 'text', 'content' => 'Mai', 'x' => 50, 'y' => 50, 'width' => 30]]],
            'status' => 'pending',
        ]);
    }
}
