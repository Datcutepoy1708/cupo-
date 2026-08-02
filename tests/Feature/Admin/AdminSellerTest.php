<?php

namespace Tests\Feature\Admin;

use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSellerTest extends TestCase
{
    use RefreshDatabase;

    private function createSellerProfile(User $user, string $status = 'pending'): SellerProfile
    {
        return SellerProfile::create([
            'user_id' => $user->id,
            'shop_name' => 'Shop Test '.$user->id,
            'slug' => 'shop-test-'.$user->id,
            'address' => 'Hà Nội',
            'description' => 'Mô tả shop',
            'national_id' => '012345678901',
            'status' => $status,
        ]);
    }

    public function test_admin_can_view_sellers_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $this->createSellerProfile($seller, 'pending');

        $response = $this->actingAs($admin)->getJson('/admin/sellers');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'total', 'current_page']);
    }

    public function test_non_admin_cannot_access_admin_sellers_list(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/admin/sellers');

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_seller_without_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $profile = $this->createSellerProfile($sellerUser, 'pending');

        $response = $this->actingAs($admin)->patchJson("/admin/sellers/{$profile->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Duyệt gian hàng thành công!')
            ->assertJsonPath('data.status', 'approved');

        $this->assertEquals('approved', $profile->fresh()->status);
        $this->assertNull($profile->fresh()->admin_note);
    }

    public function test_admin_cannot_reject_seller_without_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $profile = $this->createSellerProfile($sellerUser, 'pending');

        // Rule 16 & 17: Từ chối bắt buộc truyền admin_note
        $response = $this->actingAs($admin)->patchJson("/admin/sellers/{$profile->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['admin_note']);

        $this->assertEquals('pending', $profile->fresh()->status);
    }

    public function test_admin_can_reject_seller_with_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $profile = $this->createSellerProfile($sellerUser, 'pending');

        $response = $this->actingAs($admin)->patchJson("/admin/sellers/{$profile->id}/reject", [
            'admin_note' => 'Giấy tờ CCCD mờ không nhìn rõ thông tin.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Đã từ chối đơn đăng ký gian hàng!')
            ->assertJsonPath('data.status', 'rejected');

        $this->assertEquals('rejected', $profile->fresh()->status);
        $this->assertEquals('Giấy tờ CCCD mờ không nhìn rõ thông tin.', $profile->fresh()->admin_note);
    }

    public function test_admin_cannot_block_seller_without_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $profile = $this->createSellerProfile($sellerUser, 'approved');

        // Rule 16 & 17: Khóa gian hàng bắt buộc truyền admin_note
        $response = $this->actingAs($admin)->patchJson("/admin/sellers/{$profile->id}/block", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['admin_note']);

        $this->assertEquals('approved', $profile->fresh()->status);
    }

    public function test_admin_can_block_seller_with_admin_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $profile = $this->createSellerProfile($sellerUser, 'approved');

        $response = $this->actingAs($admin)->patchJson("/admin/sellers/{$profile->id}/block", [
            'admin_note' => 'Bán hàng giả, vi phạm chính sách của sàn.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Đã khóa gian hàng người bán!')
            ->assertJsonPath('data.status', 'blocked');

        $this->assertEquals('blocked', $profile->fresh()->status);
        $this->assertEquals('Bán hàng giả, vi phạm chính sách của sàn.', $profile->fresh()->admin_note);
    }
}
