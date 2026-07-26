<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(0, 200);
        $reservedQuantity = fake()->numberBetween(0, min($quantity, 20));

        return [
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => $quantity,
            'reserved_quantity' => $reservedQuantity,
        ];
    }
}
