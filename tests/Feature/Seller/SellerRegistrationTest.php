<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_underage_customer_cannot_register_as_seller(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post('/seller/register', [
            'shop_name' => 'Shop Test',
            'phone' => '0987654321',
            'address' => 'Ha Noi',
            'description' => 'Mo ta shop',
            'date_of_birth' => '15/08/2010', // 16 tuổi -> Dưới 18
            'national_id' => '012345678901',
        ]);

        $response->assertSessionHasErrors(['date_of_birth']);
        $this->assertEquals('customer', $user->fresh()->role);
        $this->assertNull($user->fresh()->date_of_birth);
    }

    public function test_seller_registration_requires_valid_12_digit_national_id(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post('/seller/register', [
            'shop_name' => 'Shop Test',
            'phone' => '0987654321',
            'address' => 'Ha Noi',
            'description' => 'Mo ta shop',
            'date_of_birth' => '15/08/2000',
            'national_id' => '123456789', // Chỉ có 9 chữ số -> Không hợp lệ
        ]);

        $response->assertSessionHasErrors(['national_id']);
    }

    public function test_valid_customer_over_18_can_register_as_seller_with_single_source_of_truth_dob(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post('/seller/register', [
            'shop_name' => 'Shop Nam Nữ',
            'phone' => '0987654321',
            'address' => 'Ha Noi',
            'description' => 'Mo ta shop',
            'date_of_birth' => '15/08/2000', // 26 tuổi -> Hợp lệ
            'national_id' => '012345678901', // Đủ 12 chữ số
        ]);

        $response->assertRedirect(route('seller.pending-approval'));

        $updatedUser = $user->fresh();
        $this->assertEquals('seller', $updatedUser->role);
        // Rule 15 updated: date_of_birth là Single Source of Truth trên bảng users
        $this->assertEquals('2000-08-15', $updatedUser->date_of_birth->format('Y-m-d'));

        $profile = SellerProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('012345678901', $profile->national_id); // Dữ liệu CCCD được giải mã thông qua Eloquent Model
        // Kiểm tra chắc chắn date_of_birth được truy cập thông qua relationship $profile->user->date_of_birth
        $this->assertEquals('2000-08-15', $profile->user->date_of_birth->format('Y-m-d'));
    }

    public function test_customer_can_register_as_seller_with_categories(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'Điện tử', 'slug' => 'dien-tu']);

        $response = $this->actingAs($user)->post('/seller/register', [
            'shop_name' => 'Shop Điện Tử',
            'phone' => '0987654321',
            'address' => 'Ha Noi',
            'description' => 'Mô tả shop điện tử',
            'date_of_birth' => '15/08/2000',
            'national_id' => '012345678902',
            'category_ids' => [$category->id],
        ]);

        $response->assertRedirect(route('seller.pending-approval'));

        $profile = SellerProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Điện tử', $profile->categories->first()->name);
    }

    public function test_rejected_seller_can_reapply_successfully(): void
    {
        $user = User::factory()->create(['role' => 'seller']);

        $profile = SellerProfile::create([
            'user_id' => $user->id,
            'shop_name' => 'Shop Cũ',
            'slug' => 'shop-cu-12345',
            'address' => 'Hà Nội',
            'national_id' => '012345678901',
            'status' => 'rejected',
            'admin_note' => 'Ảnh CCCD bị mờ, vui lòng nộp lại.',
        ]);

        $response = $this->actingAs($user)->post('/seller/register', [
            'shop_name' => 'Shop Mới Đã Sửa',
            'phone' => '0987654321',
            'address' => '456 Trần Phú, Hà Nội',
            'description' => 'Mô tả mới đã chỉnh sửa',
            'date_of_birth' => '15/08/2000',
            'national_id' => '012345678901',
        ]);

        $response->assertRedirect(route('seller.pending-approval'));

        $profile->refresh();
        $this->assertEquals('Shop Mới Đã Sửa', $profile->shop_name);
        $this->assertEquals('456 Trần Phú, Hà Nội', $profile->address);
        $this->assertEquals('pending', $profile->status);
        $this->assertNull($profile->admin_note);
        $this->assertEquals(1, SellerProfile::where('user_id', $user->id)->count());
    }
}
