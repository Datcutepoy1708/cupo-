<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerProductTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedSeller(): User
    {
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop của '.$seller->name,
            'slug' => 'shop-'.$seller->id,
            'address' => 'Hà Nội',
            'national_id' => '012345678901',
            'status' => 'approved', // Đã được Admin duyệt
        ]);

        return $seller;
    }

    public function test_approved_seller_can_view_own_products_list(): void
    {
        $seller = $this->createApprovedSeller();
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm của tôi',
            'slug' => 'san-pham-cua-toi',
            'sku' => 'SKU-MY-PROD',
            'price' => 150000,
            'stock' => 20,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả sản phẩm',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)->getJson('/seller/products');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_pending_seller_cannot_access_seller_products(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop Chờ Duyệt',
            'slug' => 'shop-cho-duyet',
            'address' => 'Hà Nội',
            'national_id' => '012345678901',
            'status' => 'pending', // Vẫn đang chờ duyệt
        ]);

        $response = $this->actingAs($seller)->get('/seller/products');

        // Bị Middleware EnsureSellerApproved chặn lại chuyển hướng
        $response->assertRedirect(route('seller.pending-approval'));
    }

    public function test_seller_can_create_product(): void
    {
        $seller = $this->createApprovedSeller();
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $response = $this->actingAs($seller)->postJson('/seller/products', [
            'category_id' => $category->id,
            'name' => 'Áo Sơ Mi Nam',
            'sku' => 'SKU-SOMI-01',
            'price' => 200000,
            'stock' => 30,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả áo sơ mi',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Đăng sản phẩm mới thành công! Đang chờ Admin kiểm duyệt.')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('products', [
            'seller_id' => $seller->id,
            'sku' => 'SKU-SOMI-01',
            'status' => 'pending',
        ]);
    }

    public function test_seller_cannot_create_product_with_duplicate_sku(): void
    {
        $seller = $this->createApprovedSeller();
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm cũ',
            'slug' => 'san-pham-cu',
            'sku' => 'SKU-DUPLICATE',
            'price' => 150000,
            'stock' => 20,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả',
        ]);

        // Cố tình tạo sản phẩm trùng SKU
        $response = $this->actingAs($seller)->postJson('/seller/products', [
            'category_id' => $category->id,
            'name' => 'Sản phẩm mới',
            'sku' => 'SKU-DUPLICATE',
            'price' => 200000,
            'stock' => 10,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_seller_cannot_update_other_sellers_product(): void
    {
        $seller1 = $this->createApprovedSeller();
        $seller2 = $this->createApprovedSeller();
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $productSeller1 = Product::create([
            'seller_id' => $seller1->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm Shop 1',
            'slug' => 'san-pham-shop-1',
            'sku' => 'SKU-SHOP-1',
            'price' => 100000,
            'stock' => 10,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả',
        ]);

        // Seller 2 cố tình sửa sản phẩm của Seller 1
        $response = $this->actingAs($seller2)->putJson("/seller/products/{$productSeller1->id}", [
            'category_id' => $category->id,
            'name' => 'Hacker sửa tên',
            'sku' => 'SKU-SHOP-1',
            'price' => 50000,
            'stock' => 10,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Bạn không có quyền chỉnh sửa sản phẩm này.');
    }

    public function test_seller_can_delete_own_product(): void
    {
        $seller = $this->createApprovedSeller();
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm xóa',
            'slug' => 'san-pham-xoa',
            'sku' => 'SKU-DELETE',
            'price' => 100000,
            'stock' => 10,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả',
        ]);

        $response = $this->actingAs($seller)->deleteJson("/seller/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Xóa sản phẩm thành công!');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
