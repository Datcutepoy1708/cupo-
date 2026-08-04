<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedProduct(User $seller, string $sku = 'SKU-01', int $stock = 10): Product
    {
        $category = Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang']);

        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm ' . $sku,
            'slug' => 'san-pham-' . strtolower($sku),
            'sku' => $sku,
            'price' => 100000,
            'stock' => $stock,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả sản phẩm',
            'status' => 'approved',
        ]);
    }

    public function test_customer_can_checkout_and_automatically_split_orders_by_seller(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        // Tạo 2 Shop khác nhau
        $seller1 = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller1->id, 'shop_name' => 'Shop A', 'slug' => 'shop-a', 'address' => 'HN', 'national_id' => '012345678901', 'status' => 'approved']);
        $product1 = $this->createApprovedProduct($seller1, 'SKU-SHOP1', 10);

        $seller2 = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller2->id, 'shop_name' => 'Shop B', 'slug' => 'shop-b', 'address' => 'HCM', 'national_id' => '012345678902', 'status' => 'approved']);
        $product2 = $this->createApprovedProduct($seller2, 'SKU-SHOP2', 10);

        // Khách thêm sản phẩm của 2 shop vào giỏ hàng
        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product1->id, 'quantity' => 2]);
        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product2->id, 'quantity' => 1]);

        // Thực hiện Checkout
        $response = $this->actingAs($customer)->postJson('/checkout', [
            'recipient_name' => 'Nguyễn Văn A',
            'phone' => '0987654321',
            'shipping_address' => '123 Đường Cầu Giấy, Hà Nội',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Đặt hàng thành công!');

        // 1. Đơn hàng Master tạo đúng tổng tiền (2*100k + 1*100k = 300k)
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'grand_total' => 300000,
        ]);

        // 2.  TỰ ĐỘNG TÁCH THÀNH 2 ĐƠN HÀNG CON BÁN RIÊNG CHO 2 SHOP
        $this->assertDatabaseCount('seller_orders', 2);
        $this->assertDatabaseHas('seller_orders', ['seller_id' => $seller1->id, 'grand_total' => 200000]);
        $this->assertDatabaseHas('seller_orders', ['seller_id' => $seller2->id, 'grand_total' => 100000]);

        // 3. Tồn kho sản phẩm bị trừ chính xác
        $this->assertEquals(8, $product1->fresh()->stock); // 10 - 2 = 8
        $this->assertEquals(9, $product2->fresh()->stock); // 10 - 1 = 9

        // 4. Giỏ hàng được làm sạch
        $this->assertDatabaseCount('cart_items', 0);
    }
}
