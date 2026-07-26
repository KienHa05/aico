<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->country(),
            'state' => fake()->state(),
            'city' => fake()->city(),
            'district' => fake()->citySuffix(),
            'ward' => 'Ward ' . fake()->numberBetween(1, 20),
            'address_line' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'is_default' => fake()->boolean(20),
        ];
    }
}
