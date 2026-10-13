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
        $setting = PaymentSetting::query()->first();

        return view('admin.payment-settings.edit', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:120'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $data = [
            'bank_code' => $validated['bank_code'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'is_enabled' => (bool) ($validated['is_enabled'] ?? false),
        ];

        PaymentSetting::query()->updateOrCreate(
            ['id' => 1],
            $data,
        );

        return back()->with('success', 'Thông tin tài khoản ngân hàng đã được lưu.');
    }
}
