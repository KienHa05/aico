<?php

namespace Database\Seeders;

use App\Models\VariantAttributeValue;
use Illuminate\Database\Seeder;

class VariantAttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        VariantAttributeValue::create([
            'product_variant_id' => 1,
            'attribute_option_id' => 1,
        ]);

        VariantAttributeValue::create([
            'product_variant_id' => 1,
            'attribute_option_id' => 9,
        ]);

        VariantAttributeValue::create([
            'product_variant_id' => 2,
            'attribute_option_id' => 2,
        ]);

        VariantAttributeValue::create([
            'product_variant_id' => 2,
            'attribute_option_id' => 10,
        ]);
    }
}
