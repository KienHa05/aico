<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => 1,
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'brand' => fake()->company(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => 'active',
            'is_featured' => fake()->boolean(),
        ];
    }
}
