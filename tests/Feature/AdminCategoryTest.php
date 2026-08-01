<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_categories_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $response = $this->actingAs($admin)->getJson('/admin/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_non_admin_cannot_access_categories(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/admin/categories');

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
            ->assertJsonPath('message', 'Xóa danh muc thành công');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
