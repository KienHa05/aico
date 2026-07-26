<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeOptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attribute_id' => 1,
            'value' => fake()->word(),
            'is_active' => true,
        ];
    }
}
