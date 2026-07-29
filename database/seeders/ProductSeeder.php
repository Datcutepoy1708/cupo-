<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $seller1 = User::where('email', 'seller1@gmail.com')->first();
        $seller2 = User::where('email', 'seller2@gmail.com')->first();

        $catDienThoai = Category::where('slug', 'dien-thoai')->first();
        $catLaptop = Category::where('slug', 'laptop')->first();
        $catAoThun = Category::where('slug', 'ao-thun')->first();

        // 1. iPhone 15 Pro Max (With variants)
        $iphone = Product::create([
            'seller_id' => $seller1->id,
            'category_id' => $catDienThoai->id,
            'name' => 'iPhone 15 Pro Max 256GB',
            'slug' => 'iphone-15-pro-max-256gb',
            'sku' => 'IPHONE15PM',
            'price' => 30000000.00,
            'has_variants' => true,
            'stock' => 0,
            'thumbnail' => 'products/iphone15.jpg',
            'description' => '<p>Chip A17 Pro mạnh mẽ, camera zoom quang học 5x, khung titan sang trọng.</p>',
            'attributes' => [
                'brand' => 'Apple',
                'colors' => ['Black', 'White'],
            ],
            'status' => 'approved',
        ]);

        ProductImage::create([
            'product_id' => $iphone->id,
            'image_path' => 'products/iphone15_detail1.jpg',
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $iphone->id,
            'name' => 'Titanium Đen',
            'sku' => 'IP15PM-BLK',
            'price' => 30000000.00,
            'stock' => 10,
            'image_path' => 'products/iphone15_black.jpg',
        ]);

        ProductVariant::create([
            'product_id' => $iphone->id,
            'name' => 'Titanium Trắng',
            'sku' => 'IP15PM-WHT',
            'price' => 30500000.00,
            'stock' => 5,
            'image_path' => 'products/iphone15_white.jpg',
        ]);

        // 2. MacBook Air M2 (No variants)
        Product::create([
            'seller_id' => $seller1->id,
            'category_id' => $catLaptop->id,
            'name' => 'MacBook Air M2 8GB 256GB',
            'slug' => 'macbook-air-m2-8gb-256gb',
            'sku' => 'MBA-M2-8-256',
            'price' => 24500000.00,
            'has_variants' => false,
            'stock' => 15,
            'thumbnail' => 'products/macbook_air.jpg',
            'description' => '<p>Màn hình Liquid Retina 13.6 inch, Chip Apple M2 siêu êm, pin lên đến 18 giờ.</p>',
            'attributes' => [
                'brand' => 'Apple',
                'color' => 'Space Gray',
            ],
            'status' => 'approved',
        ]);

        // 3. Áo Thun Unisex (With variants)
        $aothun = Product::create([
            'seller_id' => $seller2->id,
            'category_id' => $catAoThun->id,
            'name' => 'Áo Thun Unisex Basic Cotton',
            'slug' => 'ao-thun-unisex-basic-cotton',
            'sku' => 'AOTHUNBASIC',
            'price' => 180000.00,
            'has_variants' => true,
            'stock' => 0,
            'thumbnail' => 'products/aothun.jpg',
            'description' => '<p>Chất liệu 100% cotton dày dặn, thấm hút mồ hôi tốt, phù hợp nam nữ.</p>',
            'attributes' => [
                'brand' => 'Local Brand',
                'material' => 'Cotton',
            ],
            'status' => 'approved',
        ]);

        ProductVariant::create([
            'product_id' => $aothun->id,
            'name' => 'Size M',
            'sku' => 'AT-UNX-M',
            'price' => 180000.00,
            'stock' => 100,
        ]);

        ProductVariant::create([
            'product_id' => $aothun->id,
            'name' => 'Size L',
            'sku' => 'AT-UNX-L',
            'price' => 180000.00,
            'stock' => 50,
        ]);
    }
}
