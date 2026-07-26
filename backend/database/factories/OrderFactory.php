<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 1000);
        $discount = fake()->randomFloat(2, 0, 100);
        $shippingFee = fake()->randomFloat(2, 5, 30);
        $tax = fake()->randomFloat(2, 0, 50);

        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper(fake()->bothify('########')),
            'status' => fake()->randomElement([
                'pending',
                'confirmed',
                'processing',
                'shipping',
                'completed',
                'cancelled',
            ]),
            'payment_method' => fake()->randomElement([
                'cod',
                'paypal',
                'stripe',
            ]),
            'payment_status' => fake()->randomElement([
                'pending',
                'paid',
                'failed',
                'refunded',
            ]),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_fee' => $shippingFee,
            'tax' => $tax,
            'total' => $subtotal - $discount + $shippingFee + $tax,
            'shipping_full_name' => fake()->name(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_country' => fake()->country(),
            'shipping_state' => fake()->state(),
            'shipping_city' => fake()->city(),
            'shipping_district' => fake()->citySuffix(),
            'shipping_ward' => 'Ward ' . fake()->numberBetween(1, 20),
            'shipping_address_line' => fake()->streetAddress(),
            'shipping_postal_code' => fake()->postcode(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
