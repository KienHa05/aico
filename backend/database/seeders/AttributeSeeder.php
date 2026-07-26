<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            ['name' => 'Color', 'slug' => 'color'],
            ['name' => 'Size', 'slug' => 'size'],
            ['name' => 'Storage', 'slug' => 'storage'],
            ['name' => 'RAM', 'slug' => 'ram'],
            ['name' => 'CPU', 'slug' => 'cpu'],
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }
}
