<?php

namespace App\Http\Controllers;

use App\Models\CustomCupIcon;
use App\Models\CustomCupSetting;
use App\Models\CustomCupVariant;
use App\Services\CustomCupConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminCustomCupConfigController extends Controller
{
    public function __construct(private readonly CustomCupConfig $config) {}

    public function edit(): View
    {
        return view('admin.custom-cup-config.edit', [
            'settings' => CustomCupSetting::current(),
            'variants' => CustomCupVariant::query()
                ->ordered(includeInactive: true)
                ->get(),
            'icons' => CustomCupIcon::query()->ordered()->get(),
            'previewConfig' => $this->config->all(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_price' => ['required', 'integer', 'min:0'],
            'size' => ['required', 'string', 'max:20'],
            'bulk_discount_minimum' => ['required', 'integer', 'min:1', 'max:99'],
            'bulk_discount_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (
            $validated['bulk_discount_percentage'] > 0
            && $validated['bulk_discount_minimum'] < 2
        ) {
            throw ValidationException::withMessages([
                'bulk_discount_minimum' => 'Phải mua tối thiểu 2 cốc trở lên để áp dụng giảm giá.',
            ]);
        }

        CustomCupSetting::current()->update($validated);
        $this->config->refresh();

        return back()->with('success', 'Thiết lập giá và chương trình giảm giá đã được cập nhật.');
    }

    public function storeVariant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'handle_color' => ['required', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'interior_color' => ['required', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
        ]);

        $slug = $this->uniqueVariantSlug($validated['name']);

        DB::transaction(function () use ($slug, $validated): void {
            $maxOrder = (int) CustomCupVariant::query()->max('sort_order');

            CustomCupVariant::query()->create([
                'slug' => $slug,
                'name' => $validated['name'],
                'handle_color' => $validated['handle_color'],
                'interior_color' => $validated['interior_color'],
                'is_active' => true,
                'sort_order' => $maxOrder + 1,
            ]);
        });

        $this->config->refresh();

        return back()->with('success', 'Mẫu cốc mới đã được thêm.');
    }

    public function updateVariant(Request $request, CustomCupVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'handle_color' => ['required', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'interior_color' => ['required', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $variant->update([
            'name' => $validated['name'],
            'handle_color' => $validated['handle_color'],
            'interior_color' => $validated['interior_color'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        $this->config->refresh();

        return back()->with('success', 'Mẫu cốc đã được cập nhật.');
    }

    public function destroyVariant(CustomCupVariant $variant): RedirectResponse
    {
        $variant->delete();
        $this->config->refresh();

        return back()->with('success', 'Mẫu cốc đã được xóa.');
    }

    public function storeIcon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'glyph' => ['required', 'string', 'max:16', Rule::unique('custom_cup_icons', 'glyph')],
        ]);

        DB::transaction(function () use ($validated): void {
            $maxOrder = (int) CustomCupIcon::query()->max('sort_order');

            CustomCupIcon::query()->create([
                'glyph' => $validated['glyph'],
                'sort_order' => $maxOrder + 1,
            ]);
        });

        $this->config->refresh();

        return back()->with('success', 'Biểu tượng đã được thêm.');
    }

    public function destroyIcon(CustomCupIcon $icon): RedirectResponse
    {
        $icon->delete();
        $this->config->refresh();

        return back()->with('success', 'Biểu tượng đã được xóa.');
    }

    private function uniqueVariantSlug(string $name): string
    {
        $base = Str::slug($name, '_');

        if ($base === '') {
            $base = 'variant';
        }

        $slug = $base;
        $suffix = 1;

        while (CustomCupVariant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'_'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
