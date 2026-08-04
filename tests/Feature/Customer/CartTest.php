<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedProduct(User $seller, string $sku = 'SKU-01', int $stock = 10): Product
    {
        $category = Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang']);

        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm '.$sku,
            'slug' => 'san-pham-'.strtolower($sku),
            'sku' => $sku,
            'price' => 100000,
            'stock' => $stock,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả sản phẩm',
            'status' => 'approved', // Đã duyệt
        ]);
    }

    public function test_customer_can_add_approved_product_to_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createApprovedProduct($seller, 'SKU-ADD-01', 50);

        $response = $this->actingAs($customer)->postJson('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Thêm sản phẩm vào giỏ hàng thành công!');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_cannot_add_unapproved_product_to_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang']);

        $pendingProduct = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm chờ duyệt',
            'slug' => 'san-pham-cho-duyet',
            'sku' => 'SKU-PENDING',
            'price' => 100000,
            'stock' => 10,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả',
            'status' => 'pending', // Chưa duyệt
        ]);

        $response = $this->actingAs($customer)->postJson('/cart/add', [
            'product_id' => $pendingProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Sản phẩm này hiện chưa được công khai bán.');
    }

    public function test_customer_cannot_add_quantity_exceeding_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createApprovedProduct($seller, 'SKU-STOCK-01', 3); // Tồn kho 3

        $response = $this->actingAs($customer)->postJson('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 5, // Mua 5 > 3
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Số lượng tồn kho không đủ (Còn lại: 3).');
    }

    public function test_customer_can_view_cart_grouped_by_seller(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $seller1 = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller1->id, 'shop_name' => 'Shop 1', 'slug' => 'shop-1', 'address' => 'HN', 'national_id' => '012345678901', 'status' => 'approved']);
        $product1 = $this->createApprovedProduct($seller1, 'SKU-S1', 10);

        $seller2 = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller2->id, 'shop_name' => 'Shop 2', 'slug' => 'shop-2', 'address' => 'HCM', 'national_id' => '012345678902', 'status' => 'approved']);
        $product2 = $this->createApprovedProduct($seller2, 'SKU-S2', 10);

        // Thêm 2 sản phẩm của 2 shop khác nhau vào giỏ
        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product1->id, 'quantity' => 1]);
        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product2->id, 'quantity' => 2]);

        $response = $this->actingAs($customer)->getJson('/cart');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data.shops'); // Tự động gom nhóm thành 2 Shop độc lập
    }
}
