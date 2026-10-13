<?php

namespace App\Services;

use App\Models\CustomCupIcon;
use App\Models\CustomCupSetting;
use App\Models\CustomCupVariant;
use Illuminate\Support\Facades\Cache;

class CustomCupConfig
{
    private const CACHE_KEY = 'custom_cup_config:v1';

    /**
     * @return array{
     *     unit_price: int,
     *     size: string,
     *     bulk_discount_minimum: int,
     *     bulk_discount_percentage: int,
     *     variants: array<string, array{name: string, handle_color: string, interior_color: string, is_active: bool, sort_order: int}>,
     *     icons: array<int, string>
     * }
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return $this->build();
        });
    }

    public function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $hasDbRows = CustomCupVariant::query()->exists() || CustomCupIcon::query()->exists();

        if (! $hasDbRows && CustomCupSetting::query()->doesntExist()) {
            return $this->fromFileConfig();
        }

        $settings = CustomCupSetting::current();
        $variants = CustomCupVariant::query()
            ->ordered(includeInactive: true)
            ->get();

        // Always use icons from config file (stored in base code, not database)
        $icons = array_values((array) config('store.custom_cup.icons', []));

        $variantMap = [];

        foreach ($variants as $variant) {
            $variantMap[$variant->slug] = [
                'name' => $variant->name,
                'handle_color' => $variant->handle_color,
                'interior_color' => $variant->interior_color,
                'is_active' => $variant->is_active,
                'sort_order' => $variant->sort_order,
            ];
        }

        if ($variantMap === []) {
            $fallbackVariants = (array) config('store.custom_cup.variants', []);
            $order = 0;

            foreach ($fallbackVariants as $slug => $details) {
                $variantMap[(string) $slug] = [
                    'name' => (string) ($details['name'] ?? $slug),
                    'handle_color' => (string) ($details['handle_color'] ?? '#000000'),
                    'interior_color' => (string) ($details['interior_color'] ?? '#000000'),
                    'is_active' => true,
                    'sort_order' => $order++,
                ];
            }
        }

        return [
            'unit_price' => $settings->unit_price,
            'size' => $settings->size,
            'bulk_discount_minimum' => $settings->bulk_discount_minimum,
            'bulk_discount_percentage' => $settings->bulk_discount_percentage,
            'variants' => $variantMap,
            'icons' => $icons,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fromFileConfig(): array
    {
        $config = (array) config('store.custom_cup');

        $variants = [];
        $order = 0;

        foreach ((array) ($config['variants'] ?? []) as $slug => $details) {
            $variants[(string) $slug] = [
                'name' => (string) ($details['name'] ?? $slug),
                'handle_color' => (string) ($details['handle_color'] ?? '#000000'),
                'interior_color' => (string) ($details['interior_color'] ?? '#000000'),
                'is_active' => true,
                'sort_order' => $order++,
            ];
        }

        return [
            'unit_price' => (int) ($config['unit_price'] ?? 89000),
            'size' => (string) ($config['size'] ?? '11oz'),
            'bulk_discount_minimum' => (int) ($config['bulk_discount_minimum'] ?? 5),
            'bulk_discount_percentage' => (int) ($config['bulk_discount_percentage'] ?? 10),
            'variants' => $variants,
            'icons' => array_values((array) ($config['icons'] ?? [])),
        ];
    }
}
