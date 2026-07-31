<?php

namespace Tests\Feature;

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

    public function test_valid_customer_over_18_can_register_as_seller_with_encrypted_national_id(): void
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
        $this->assertEquals('seller', $user->fresh()->role);

        $profile = SellerProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('012345678901', $profile->national_id); // Đọc thông qua Eloquent Model tự giải mã
    }
}
