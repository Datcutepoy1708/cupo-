<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_storefront_shop_detail_page(): void
    {
        $category = Category::create(['name' => 'Linh kiện điện tử', 'slug' => 'linh-kien-dien-tu']);

        $sellerUser = User::factory()->create(['role' => 'seller']);
        $shop = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Acctv.vn Tech Store',
            'slug' => 'acctv-vn',
            'business_type' => 'personal',
            'address' => '123 Nguyen Trai, Thanh Xuan, Ha Noi',
            'status' => 'approved',
        ]);

        Product::create([
            'seller_id' => $sellerUser->id,
            'category_id' => $category->id,
            'sku' => 'SKU-TEST-001',
            'thumbnail' => 'products/sample.jpg',
            'description' => 'Detailed product description text for test',
            'name' => 'STM32F401 Board Module',
            'slug' => 'stm32f401-board-module',
            'price' => 53968,
            'status' => 'approved',
        ]);

        $response = $this->get("/shops/{$shop->id}");
        $response->assertStatus(200)
            ->assertSee('Acctv.vn Tech Store')
            ->assertSee('STM32F401 Board Module');
    }

    public function test_shop_search_and_sort_filters(): void
    {
        $category = Category::create(['name' => 'Thết bị điện tử', 'slug' => 'thiet-bi-dien-tu']);

        $sellerUser = User::factory()->create(['role' => 'seller']);
        $shop = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Electronic World',
            'slug' => 'electronic-world',
            'business_type' => 'personal',
            'address' => '456 Le Duan, Da Nang',
            'status' => 'approved',
        ]);

        Product::create([
            'seller_id' => $sellerUser->id,
            'category_id' => $category->id,
            'sku' => 'SKU-TEST-002',
            'thumbnail' => 'products/sample2.jpg',
            'description' => 'Detailed product description text for test',
            'name' => 'Arduino Uno R3 Original',
            'slug' => 'arduino-uno-r3-original',
            'price' => 150000,
            'status' => 'approved',
        ]);

        Product::create([
            'seller_id' => $sellerUser->id,
            'category_id' => $category->id,
            'sku' => 'SKU-TEST-003',
            'thumbnail' => 'products/sample3.jpg',
            'description' => 'Detailed product description text for test',
            'name' => 'ESP32 Wifi Bluetooth Module',
            'slug' => 'esp32-wifi-bluetooth-module',
            'price' => 95000,
            'status' => 'approved',
        ]);

        // Test search query
        $searchResponse = $this->get("/shops/{$shop->id}?q=Arduino");
        $searchResponse->assertStatus(200)
            ->assertSee('Arduino Uno R3 Original')
            ->assertDontSee('ESP32 Wifi Bluetooth Module');

        // Test sort price asc
        $sortResponse = $this->get("/shops/{$shop->id}?sort=price_asc");
        $sortResponse->assertStatus(200);
    }
}
