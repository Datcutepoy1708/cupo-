<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
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

    public function test_customer_cart_page_uses_storage_thumbnail_url(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller->id, 'shop_name' => 'Shop Thumbnail', 'slug' => 'shop-thumbnail', 'address' => 'HN', 'national_id' => '012345678903', 'status' => 'approved']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang'])->id,
            'name' => 'Sản phẩm ảnh storage',
            'slug' => 'san-pham-anh-storage',
            'sku' => 'SKU-STORAGE-01',
            'price' => 150000,
            'stock' => 10,
            'thumbnail' => 'products/test-product.jpg',
            'description' => 'Mô tả sản phẩm',
            'status' => 'approved',
        ]);

        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($customer)->get('/cart');

        $response->assertStatus(200)
            ->assertSee('/storage/products/test-product.jpg');
    }

    public function test_customer_can_open_dedicated_checkout_page_for_selected_cart_items(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller->id, 'shop_name' => 'Shop Checkout', 'slug' => 'shop-checkout', 'address' => 'HN', 'national_id' => '012345678904', 'status' => 'approved']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang'])->id,
            'name' => 'Sản phẩm thanh toán',
            'slug' => 'san-pham-thanh-toan',
            'sku' => 'SKU-CHECKOUT-01',
            'price' => 250000,
            'stock' => 12,
            'thumbnail' => 'products/checkout-product.jpg',
            'description' => 'Mô tả',
            'status' => 'approved',
        ]);

        $this->actingAs($customer)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $cart = \App\Models\Cart::where('user_id', $customer->id)->first();
        $cartItemId = $cart->items()->first()->id;

        $response = $this->actingAs($customer)->get('/checkout?cart_item_ids=' . $cartItemId);

        $response->assertStatus(200)
            ->assertSee('Thông tin thanh toán')
            ->assertSee('Xác nhận đơn hàng');
    }

    public function test_customer_can_open_direct_checkout_page_without_variant(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller->id, 'shop_name' => 'Shop Direct Checkout', 'slug' => 'shop-direct-checkout', 'address' => 'HN', 'national_id' => '012345678905', 'status' => 'approved']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang'])->id,
            'name' => 'Sản phẩm mua ngay không biến thể',
            'slug' => 'san-pham-mua-ngay-khong-bien-the',
            'sku' => 'SKU-DIRECT-01',
            'price' => 180000,
            'stock' => 20,
            'thumbnail' => 'products/direct-product.jpg',
            'description' => 'Mô tả',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($customer)->get('/checkout?product_id=' . $product->id . '&qty=2');

        $response->assertStatus(200)
            ->assertSee('Thông tin thanh toán')
            ->assertSee('Sản phẩm đặt hàng')
            ->assertSee('Thanh toán khi nhận hàng');
    }

    public function test_customer_checkout_page_uses_variant_image_when_variant_is_selected(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller->id, 'shop_name' => 'Shop Variant Image', 'slug' => 'shop-variant-image', 'address' => 'HN', 'national_id' => '012345678906', 'status' => 'approved']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang'])->id,
            'name' => 'Sản phẩm có biến thể',
            'slug' => 'san-pham-co-bien-the',
            'sku' => 'SKU-VARIANT-01',
            'price' => 220000,
            'stock' => 20,
            'thumbnail' => 'products/default-product.jpg',
            'description' => 'Mô tả',
            'status' => 'approved',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ / M',
            'sku' => 'SKU-VARIANT-RED-M',
            'price' => 220000,
            'sale_price' => null,
            'stock' => 12,
            'image_path' => 'products/variant-red-m.jpg',
        ]);

        $response = $this->actingAs($customer)->get('/checkout?product_id=' . $product->id . '&variant_id=' . $variant->id . '&qty=1');

        $response->assertStatus(200)
            ->assertSee('/storage/products/variant-red-m.jpg');
    }
}
