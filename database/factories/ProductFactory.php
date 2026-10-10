<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word().' '.fake()->word(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(10),
            'detailed_description' => fake()->paragraph(3),
            'price' => fake()->numberBetween(50000, 500000),
            'discount_percentage' => 0,
            'image_url' => fake()->imageUrl(400, 400, 'cups'),
            'stock' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(80),
            'sku' => fake()->unique()->bothify('SKU-####'),
        ];
    }
}
