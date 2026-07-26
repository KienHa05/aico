<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_variant_id' => 1,
            'image' => fake()->imageUrl(800, 800, 'products'),
            'sort_order' => fake()->numberBetween(1, 5),
        ];
    }
}
