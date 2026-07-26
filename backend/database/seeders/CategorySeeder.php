<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Laptop',
            'Điện thoại',
            'Máy tính bảng',
            'Đồng hồ',
            'Tai nghe',
            'Phụ kiện',
        ];

        foreach ($categories as $index => $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
