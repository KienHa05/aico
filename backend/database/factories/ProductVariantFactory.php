<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 5000);

        $salePrice = fake()->boolean()
            ? max(1, $price - fake()->randomFloat(2, 5, 200))
            : null;

        return [
            'product_id' => 1,
            'sku' => strtoupper(fake()->bothify('SKU-####??')),
            'price' => $price,
            'sale_price' => $salePrice,
            'thumbnail' => null,
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
