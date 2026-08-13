<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBannerTest extends TestCase
{
    use RefreshDatabase;

    private function createBanner(array $attributes = []): Banner
    {
        return Banner::create(array_merge([
            'title' => 'Banner khuyến mãi test',
            'image_path' => 'https://via.placeholder.com/1200x400',
            'link_url' => 'https://cupo.vn/khuyen-mai',
            'position' => 'homepage_hero',
            'sort_order' => 1,
            'is_active' => true,
        ], $attributes));
    }

    public function test_admin_can_view_banners_blade_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/banners');

        $response->assertStatus(200)
            ->assertViewIs('admin.banners.index');
    }

    public function test_admin_can_fetch_banners_json_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createBanner();

        $response = $this->actingAs($admin)->getJson('/admin/banners');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_non_admin_cannot_access_admin_banners_list(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/admin/banners');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_banner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/banners', [
            'title' => 'Banner Hero Mới',
            'image_path' => 'https://via.placeholder.com/1200x400',
            'link_url' => 'https://cupo.vn/hero',
            'position' => 'homepage_hero',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Tạo banner mới thành công!')
            ->assertJsonPath('data.title', 'Banner Hero Mới');

        $this->assertDatabaseHas('banners', [
            'title' => 'Banner Hero Mới',
        ]);
    }

    public function test_admin_cannot_create_banner_without_title_or_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/banners', [
            'title' => '',
            'image_path' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'image_path']);
    }

    public function test_admin_can_update_banner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $banner = $this->createBanner(['title' => 'Banner Cũ']);

        $response = $this->actingAs($admin)->putJson("/admin/banners/{$banner->id}", [
            'title' => 'Banner Đã Cập Nhật',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cập nhật banner thành công!');

        $this->assertEquals('Banner Đã Cập Nhật', $banner->fresh()->title);
    }

    public function test_admin_can_delete_banner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $banner = $this->createBanner();

        $response = $this->actingAs($admin)->deleteJson("/admin/banners/{$banner->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Đã xóa banner thành công!');

        $this->assertDatabaseMissing('banners', [
            'id' => $banner->id,
        ]);
    }

    public function test_admin_can_bulk_update_banner_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $b1 = $this->createBanner(['is_active' => false]);
        $b2 = $this->createBanner(['is_active' => false]);

        $response = $this->actingAs($admin)->postJson('/admin/banners/bulk-status', [
            'ids' => [$b1->id, $b2->id],
            'is_active' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->assertTrue($b1->fresh()->is_active);
        $this->assertTrue($b2->fresh()->is_active);
    }

    public function test_admin_can_bulk_delete_banners(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $b1 = $this->createBanner();
        $b2 = $this->createBanner();

        $response = $this->actingAs($admin)->postJson('/admin/banners/bulk-delete', [
            'ids' => [$b1->id, $b2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->assertDatabaseMissing('banners', ['id' => $b1->id]);
        $this->assertDatabaseMissing('banners', ['id' => $b2->id]);
    }
}
