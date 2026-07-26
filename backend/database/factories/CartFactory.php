<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $isUserCart = fake()->boolean(70);

        return [
            'user_id' => $isUserCart ? User::factory() : null,
            'session_id' => $isUserCart ? null : fake()->uuid(),
        ];
    }
}
