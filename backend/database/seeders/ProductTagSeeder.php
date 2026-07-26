<?php

namespace Database\Seeders;

use App\Models\ProductTag;
use Illuminate\Database\Seeder;

class ProductTagSeeder extends Seeder
{
    public function run(): void
    {
        ProductTag::create([
            'product_id' => 1,
            'tag_id' => 1,
        ]);

        ProductTag::create([
            'product_id' => 1,
            'tag_id' => 2,
        ]);

        ProductTag::create([
            'product_id' => 2,
            'tag_id' => 3,
        ]);

        ProductTag::create([
            'product_id' => 2,
            'tag_id' => 4,
        ]);
    }
}
