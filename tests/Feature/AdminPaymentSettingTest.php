<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_qr_payment_settings_page_and_navigation_are_removed(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/thanh-toan-qr')
            ->assertNotFound();

        $this->actingAs($admin)
            ->put('/admin/thanh-toan-qr', [
                'bank_code' => 'VCB',
                'bank_name' => 'Vietcombank',
                'account_number' => '0123456789',
                'account_name' => 'NGUYEN VAN A',
                'is_enabled' => '1',
            ])
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Thanh toán QR ngân hàng')
            ->assertDontSee('/admin/thanh-toan-qr', false);
    }
}
