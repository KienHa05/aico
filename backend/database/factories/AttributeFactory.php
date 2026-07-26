<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttributeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Color',
            'Size',
            'Storage',
            'RAM',
            'CPU',
            'Material',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
        ];
    }
}
