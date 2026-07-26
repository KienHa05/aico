<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductTagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => 1,
            'tag_id' => fake()->numberBetween(1, 10),
        ];
    }
}
