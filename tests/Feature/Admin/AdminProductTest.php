<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(User $seller, string $status = 'pending'): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'thoi-trang'],
            ['name' => 'Thời trang']
        );

        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm test '.$seller->id.' '.rand(100, 999),
            'slug' => 'san-pham-test-'.$seller->id.'-'.rand(100, 999),
            'sku' => 'SKU-TEST-'.$seller->id.'-'.rand(100, 999),
            'price' => 100000,
            'stock' => 10,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả sản phẩm test',
            'status' => $status,
        ]);
    }

    public function test_admin_can_view_products_blade_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200)
            ->assertViewIs('admin.products.index');
    }

    public function test_admin_can_fetch_products_json_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $this->createProduct($seller, 'pending');

        $response = $this->actingAs($admin)->getJson('/admin/products');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_non_admin_cannot_access_admin_products_list(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/admin/products');

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_product_without_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct($seller, 'pending');

        $response = $this->actingAs($admin)->patchJson("/admin/products/{$product->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Duyệt sản phẩm thành công! Sản phẩm đã được công khai trên sàn.');

        $this->assertEquals('approved', $product->fresh()->status);
        $this->assertNull($product->fresh()->admin_note);
    }

    public function test_admin_cannot_reject_product_without_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct($seller, 'pending');

        $response = $this->actingAs($admin)->patchJson("/admin/products/{$product->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['admin_note']);

        $this->assertEquals('pending', $product->fresh()->status);
    }

    public function test_admin_can_reject_product_with_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct($seller, 'pending');

        $response = $this->actingAs($admin)->patchJson("/admin/products/{$product->id}/reject", [
            'admin_note' => 'Sản phẩm vi phạm bản quyền thương hiệu.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Đã từ chối sản phẩm!');

        $this->assertEquals('rejected', $product->fresh()->status);
        $this->assertEquals('Sản phẩm vi phạm bản quyền thương hiệu.', $product->fresh()->admin_note);
    }

    public function test_admin_can_bulk_approve_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $p1 = $this->createProduct($seller, 'pending');
        $p2 = $this->createProduct($seller, 'rejected');

        $response = $this->actingAs($admin)->postJson('/admin/products/bulk-approve', [
            'ids' => [$p1->id, $p2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->assertEquals('approved', $p1->fresh()->status);
        $this->assertEquals('approved', $p2->fresh()->status);
    }

    public function test_admin_can_bulk_reject_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $p1 = $this->createProduct($seller, 'pending');
        $p2 = $this->createProduct($seller, 'approved');

        $response = $this->actingAs($admin)->postJson('/admin/products/bulk-reject', [
            'ids' => [$p1->id, $p2->id],
            'admin_note' => 'Vi phạm chính sách hàng hóa cấm bán trên Cupo.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->assertEquals('rejected', $p1->fresh()->status);
        $this->assertEquals('rejected', $p2->fresh()->status);
    }

    public function test_admin_can_export_products_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $this->createProduct($seller, 'pending');

        $response = $this->actingAs($admin)->get('/admin/products/export');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
