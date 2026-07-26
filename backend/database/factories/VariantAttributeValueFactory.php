<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VariantAttributeValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_variant_id' => 1,
            'attribute_option_id' => 1,
        ];
    }
}
