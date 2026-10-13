<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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
            'qr_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $data = [
            'bank_code' => $validated['bank_code'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'is_enabled' => (bool) ($validated['is_enabled'] ?? false),
        ];

        // Handle QR code image upload
        if ($request->hasFile('qr_image')) {
            // Delete old image if exists
            $setting = PaymentSetting::query()->first();
            if ($setting && $setting->qr_image_path) {
                Storage::disk('public')->delete($setting->qr_image_path);
            }

            $path = $request->file('qr_image')->store('payment-qr', 'public');
            $data['qr_image_path'] = $path;
        }

        PaymentSetting::query()->updateOrCreate(
            ['id' => 1],
            $data,
        );

        return back()->with('success', 'Thông tin tài khoản ngân hàng đã được lưu.');
    }

    public function deleteQrImage(): RedirectResponse
    {
        $setting = PaymentSetting::query()->first();
        if ($setting && $setting->qr_image_path) {
            Storage::disk('public')->delete($setting->qr_image_path);
            $setting->update(['qr_image_path' => null]);
        }

        return back()->with('success', 'Hình ảnh mã QR đã được xóa.');
    }
}
