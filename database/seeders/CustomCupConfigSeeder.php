<?php

namespace Database\Seeders;

use App\Models\CustomCupIcon;
use App\Models\CustomCupSetting;
use App\Models\CustomCupVariant;
use Illuminate\Database\Seeder;

class CustomCupConfigSeeder extends Seeder
{
    public function run(): void
    {
        $config = (array) config('store.custom_cup');

        if (CustomCupSetting::query()->doesntExist()) {
            CustomCupSetting::query()->create([
                'unit_price' => (int) ($config['unit_price'] ?? 89000),
                'size' => (string) ($config['size'] ?? '11oz'),
                'bulk_discount_minimum' => (int) ($config['bulk_discount_minimum'] ?? 5),
                'bulk_discount_percentage' => (int) ($config['bulk_discount_percentage'] ?? 10),
            ]);
        }

        if (CustomCupVariant::query()->doesntExist()) {
            $variants = (array) ($config['variants'] ?? []);

            foreach ($variants as $slug => $details) {
                CustomCupVariant::query()->create([
                    'slug' => (string) $slug,
                    'name' => (string) ($details['name'] ?? $slug),
                    'handle_color' => (string) ($details['handle_color'] ?? '#000000'),
                    'interior_color' => (string) ($details['interior_color'] ?? '#000000'),
                    'is_active' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        if (CustomCupIcon::query()->doesntExist()) {
            $icons = (array) ($config['icons'] ?? []);

            foreach ($icons as $index => $glyph) {
                CustomCupIcon::query()->create([
                    'glyph' => (string) $glyph,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
