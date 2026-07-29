<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $dienthoai_maytinh = Category::create([
            'name' => 'Điện thoại & Máy tính',
            'slug' => 'dien-thoai-may-tinh',
            'status' => true,
        ]);

        Category::create([
            'parent_id' => $dienthoai_maytinh->id,
            'name' => 'Điện thoại',
            'slug' => 'dien-thoai',
            'status' => true,
        ]);

        Category::create([
            'parent_id' => $dienthoai_maytinh->id,
            'name' => 'Laptop',
            'slug' => 'laptop',
            'status' => true,
        ]);

        $thoitrang = Category::create([
            'name' => 'Thời trang',
            'slug' => 'thoi-trang',
            'status' => true,
        ]);

        Category::create([
            'parent_id' => $thoitrang->id,
            'name' => 'Áo thun',
            'slug' => 'ao-thun',
            'status' => true,
        ]);

        Category::create([
            'parent_id' => $thoitrang->id,
            'name' => 'Quần Jean',
            'slug' => 'quan-jean',
            'status' => true,
        ]);
    }
}
