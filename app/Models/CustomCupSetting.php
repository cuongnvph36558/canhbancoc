<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomCupSetting extends Model
{
    protected $fillable = [
        'unit_price',
        'size',
        'print_area_width_mm',
        'print_area_height_mm',
        'bulk_discount_minimum',
        'bulk_discount_percentage',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'print_area_width_mm' => 'integer',
            'print_area_height_mm' => 'integer',
            'bulk_discount_minimum' => 'integer',
            'bulk_discount_percentage' => 'integer',
        ];
    }

    /**
     * Return the singleton settings row, creating defaults on first access.
     */
    public static function current(): self
    {
        $settings = static::query()->first();

        if ($settings !== null) {
            return $settings;
        }

        $config = (array) config('store.custom_cup');

        return static::query()->create([
            'unit_price' => (int) ($config['unit_price'] ?? 89000),
            'size' => (string) ($config['size'] ?? '11oz'),
            'print_area_width_mm' => (int) ($config['print_area_width_mm'] ?? 220),
            'print_area_height_mm' => (int) ($config['print_area_height_mm'] ?? 90),
            'bulk_discount_minimum' => (int) ($config['bulk_discount_minimum'] ?? 5),
            'bulk_discount_percentage' => (int) ($config['bulk_discount_percentage'] ?? 10),
        ]);
    }
}
