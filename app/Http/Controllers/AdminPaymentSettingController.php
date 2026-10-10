<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.payment-settings.edit', [
            'paymentSetting' => PaymentSetting::query()->find(1),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $paymentSetting = PaymentSetting::query()->find(1);
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:120'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $paymentSetting ??= new PaymentSetting;
        if (! $paymentSetting->exists) {
            $paymentSetting->id = 1;
        }

        $paymentSetting->fill($validated);
        $paymentSetting->is_enabled = $request->boolean('is_enabled');

        $paymentSetting->save();

        return to_route('admin.payment-settings.edit')
            ->with('success', 'Cài đặt thanh toán QR đã được cập nhật.');
    }
}
