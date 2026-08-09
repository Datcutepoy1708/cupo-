<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_categories_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/categories');

        $response->assertStatus(200)
            ->assertViewIs('admin.categories.index');
    }

    public function test_admin_can_fetch_categories_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $response = $this->actingAs($admin)->getJson('/admin/categories/data');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_non_admin_cannot_access_categories(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/admin/categories/data');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/categories', [
            'name' => 'Điện tử - Công nghệ',
            'status' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Tạo danh mục thành công')
            ->assertJsonPath('data.name', 'Điện tử - Công nghệ');

        $this->assertDatabaseHas('categories', [
            'name' => 'Điện tử - Công nghệ',
        ]);
    }

    public function test_admin_cannot_create_category_without_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/categories', [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Thời trang cũ', 'slug' => 'thoi-trang-cu']);

        $response = $this->actingAs($admin)->putJson("/admin/categories/{$category->id}", [
            'name' => 'Thời trang mới',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cập nhật danh mục thành công!');

        $this->assertEquals('Thời trang mới', $category->fresh()->name);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Danh mục test', 'slug' => 'danh-muc-test']);

        $response = $this->actingAs($admin)->deleteJson("/admin/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Xóa danh mục thành công');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_can_export_categories_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Category::create(['name' => 'Thời trang Export', 'slug' => 'thoi-trang-export']);

        $response = $this->actingAs($admin)->get('/admin/categories/export');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_bulk_update_category_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat1 = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1', 'status' => false]);
        $cat2 = Category::create(['name' => 'Cat 2', 'slug' => 'cat-2', 'status' => false]);

        $response = $this->actingAs($admin)->postJson('/admin/categories/bulk-status', [
            'ids' => [$cat1->id, $cat2->id],
            'status' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->assertTrue($cat1->fresh()->status);
        $this->assertTrue($cat2->fresh()->status);
    }

    public function test_admin_can_bulk_delete_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat1 = Category::create(['name' => 'Cat Del 1', 'slug' => 'cat-del-1']);
        $cat2 = Category::create(['name' => 'Cat Del 2', 'slug' => 'cat-del-2']);

        $response = $this->actingAs($admin)->postJson('/admin/categories/bulk-delete', [
            'ids' => [$cat1->id, $cat2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->assertDatabaseMissing('categories', ['id' => $cat1->id]);
        $this->assertDatabaseMissing('categories', ['id' => $cat2->id]);
    }
}
