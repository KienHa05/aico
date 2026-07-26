<?php

namespace Database\Seeders;

use App\Models\AttributeOption;
use Illuminate\Database\Seeder;

class AttributeOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // Color
            ['attribute_id' => 1, 'value' => 'Black'],
            ['attribute_id' => 1, 'value' => 'White'],
            ['attribute_id' => 1, 'value' => 'Blue'],
            ['attribute_id' => 1, 'value' => 'Red'],

            // Size
            ['attribute_id' => 2, 'value' => 'S'],
            ['attribute_id' => 2, 'value' => 'M'],
            ['attribute_id' => 2, 'value' => 'L'],
            ['attribute_id' => 2, 'value' => 'XL'],

            // Storage
            ['attribute_id' => 3, 'value' => '128GB'],
            ['attribute_id' => 3, 'value' => '256GB'],
            ['attribute_id' => 3, 'value' => '512GB'],

            // RAM
            ['attribute_id' => 4, 'value' => '8GB'],
            ['attribute_id' => 4, 'value' => '16GB'],
            ['attribute_id' => 4, 'value' => '32GB'],

            // CPU
            ['attribute_id' => 5, 'value' => 'Intel Core i5'],
            ['attribute_id' => 5, 'value' => 'Intel Core i7'],
            ['attribute_id' => 5, 'value' => 'Apple M3'],
        ];

        foreach ($options as $option) {
            AttributeOption::create($option);
        }
    }
}
