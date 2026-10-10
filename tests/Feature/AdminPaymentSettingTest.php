<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_manage_qr_payment_settings(): void
    {
        $this->get(route('admin.payment-settings.edit'))
            ->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.payment-settings.edit'))
            ->assertForbidden();

        $this->assertDatabaseCount('payment_settings', 0);
    }

    public function test_admin_can_save_bank_details_for_dynamic_qr_payments(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.payment-settings.edit'))
            ->assertOk()
            ->assertSee('Thanh toán QR ngân hàng');

        $this->put(route('admin.payment-settings.update'), [
            'bank_code' => 'VCB',
            'bank_name' => 'Vietcombank',
            'account_number' => '0123456789',
            'account_name' => 'NGUYEN VAN A',
            'is_enabled' => '1',
        ])
            ->assertRedirect(route('admin.payment-settings.edit'))
            ->assertSessionHas('success');

        $paymentSetting = PaymentSetting::query()->findOrFail(1);
        $this->assertSame('VCB', $paymentSetting->bank_code);
        $this->assertSame('Vietcombank', $paymentSetting->bank_name);
        $this->assertSame('0123456789', $paymentSetting->account_number);
        $this->assertSame('NGUYEN VAN A', $paymentSetting->account_name);
        $this->assertTrue($paymentSetting->isReadyForCheckout());
    }

    public function test_admin_must_enter_bank_details_before_enabling_qr_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.payment-settings.edit'))
            ->put(route('admin.payment-settings.update'), [
                'bank_name' => 'Vietcombank',
                'is_enabled' => '1',
            ])
            ->assertRedirect(route('admin.payment-settings.edit'))
            ->assertSessionHasErrors(['bank_code', 'account_number', 'account_name']);

        $this->assertDatabaseCount('payment_settings', 0);
    }
}
