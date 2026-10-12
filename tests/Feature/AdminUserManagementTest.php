<?php

namespace Tests\Feature;

use App\Models\CustomCupOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_user_management(): void
    {
        $this->get('/admin')
            ->assertRedirect('/dang-nhap');
    }

    public function test_client_cannot_open_admin_user_management(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_sees_a_separate_user_management_page(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create([
            'name' => 'Mai Nguyen',
            'role' => 'client',
        ]);

        $this->actingAs($admin)
            ->get('/admin/nguoi-dung')
            ->assertOk()
            ->assertSee('Cốc Mơ Admin')
            ->assertSee('Khách hàng')
            ->assertSee('phân quyền')
            ->assertSee('Mai Nguyen')
            ->assertSee('Client')
            ->assertSee('option value="admin"', false)
            ->assertDontSee('cup-storefront', false);
    }

    public function test_admin_dashboard_shows_revenue_statistics(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        Order::query()->create([
            'user_id' => $admin->id,
            'order_number' => 'REV-001',
            'customer_name' => 'Khách hàng A',
            'customer_email' => 'customer@example.com',
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
            'expires_at' => now()->addDay(),
            'paid_at' => now(),
        ]);

        CustomCupOrder::query()->create([
            'user_id' => $admin->id,
            'order_number' => 'REV-CUSTOM-001',
            'customer_name' => 'Khách hàng B',
            'customer_phone' => '0907654321',
            'customer_email' => 'custom@example.com',
            'shipping_address' => '88 Trần Phú',
            'cup_variant' => 'black',
            'cup_size' => '11oz',
            'quantity' => 1,
            'unit_price' => 89000,
            'discount_percentage' => 0,
            'total_amount' => 89000,
            'design' => ['items' => [['type' => 'text', 'content' => 'Cốc', 'x' => 50, 'y' => 50, 'width' => 30]]],
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Doanh thu tổng')
            ->assertSee(number_format(219000, 0, ',', '.') . '₫', false);
    }

    public function test_admin_can_assign_admin_role_to_a_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($admin)
            ->put(route('admin.users.update-role', $client), ['role' => 'admin'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_cannot_assign_an_unsupported_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($admin)
            ->put(route('admin.users.update-role', $client), ['role' => 'super-admin'])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'role' => 'client',
        ]);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.users.update-role', $admin), ['role' => 'client'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_search_clients_by_name_or_email(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'Lan Tran',
            'email' => 'lan@example.com',
            'role' => 'client',
        ]);
        User::factory()->create([
            'name' => 'Minh Le',
            'email' => 'minh@example.com',
            'role' => 'client',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users', ['search' => 'lan@example.com']))
            ->assertOk()
            ->assertSee('Lan Tran')
            ->assertDontSee('Minh Le');
    }

    public function test_new_registration_gets_the_client_role(): void
    {
        $this->get('/dang-ky')
            ->assertOk()
            ->assertSee('name="phone"', false)
            ->assertSee('data-vietnam-address', false)
            ->assertSee('name="shipping_address"', false);

        $this->post('/dang-ky', [
            'name' => 'Thao Nguyen',
            'email' => 'thao@example.com',
            'phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'thao@example.com',
            'role' => 'client',
            'is_active' => true,
            'phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => '12 Phố Huế',
        ]);
    }

    public function test_registration_requires_phone_and_shipping_address(): void
    {
        $this->from('/dang-ky')
            ->post('/dang-ky', [
                'name' => 'Thao Nguyen',
                'email' => 'thao@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect('/dang-ky')
            ->assertSessionHasErrors([
                'phone',
                'shipping_province',
                'shipping_ward',
                'shipping_address',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_inactive_admin_cannot_access_admin_user_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertForbidden();
    }
}
