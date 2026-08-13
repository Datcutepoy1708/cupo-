<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_my_vouchers_tab_in_profile(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $coupon = Coupon::create([
            'seller_id' => null,
            'code' => 'PLATFORM10',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 100000,
            'usage_limit' => 50,
            'used_count' => 0,
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200)
            ->assertSee('Kho Voucher Của Tôi')
            ->assertSee('Voucher Đang Có')
            ->assertSee('Nhận Thêm Voucher')
            ->assertSee('Giảm 10%');
    }

    public function test_authenticated_user_can_save_discoverable_voucher(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $coupon = Coupon::create([
            'seller_id' => null,
            'code' => 'SAVE50K',
            'type' => 'fixed_amount',
            'value' => 50000,
            'min_order_amount' => 300000,
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => true,
        ]);

        $response = $this->actingAs($user)->postJson("/customer/vouchers/{$coupon->id}/save");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'coupon_id' => $coupon->id,
                'owned_count' => 1,
            ]);

        $this->assertDatabaseHas('customer_coupons', [
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'status' => 'saved',
        ]);
    }

    public function test_user_cannot_save_same_voucher_twice(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $coupon = Coupon::create([
            'seller_id' => null,
            'code' => 'ONCEONLY',
            'type' => 'percentage',
            'value' => 15,
            'min_order_amount' => 100000,
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => true,
        ]);

        // First save
        $this->actingAs($user)->postJson("/customer/vouchers/{$coupon->id}/save")->assertStatus(200);

        // Second save attempt
        $secondResponse = $this->actingAs($user)->postJson("/customer/vouchers/{$coupon->id}/save");
        $secondResponse->assertStatus(409)
            ->assertJson([
                'success' => false,
                'already_saved' => true,
            ]);
    }

    public function test_user_cannot_save_expired_or_disabled_voucher(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $expiredCoupon = Coupon::create([
            'seller_id' => null,
            'code' => 'EXPIRED99',
            'type' => 'fixed_amount',
            'value' => 20000,
            'min_order_amount' => 100000,
            'usage_limit' => 50,
            'used_count' => 0,
            'expires_at' => Carbon::now()->subDays(5),
            'status' => true,
        ]);

        $response = $this->actingAs($user)->postJson("/customer/vouchers/{$expiredCoupon->id}/save");
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_unauthenticated_user_cannot_save_voucher(): void
    {
        $coupon = Coupon::create([
            'seller_id' => null,
            'code' => 'GUESTSAVE',
            'type' => 'fixed_amount',
            'value' => 30000,
            'min_order_amount' => 150000,
            'usage_limit' => 50,
            'used_count' => 0,
            'status' => true,
        ]);

        $response = $this->postJson("/customer/vouchers/{$coupon->id}/save");
        $response->assertStatus(401);
    }

    public function test_shop_page_displays_shop_vouchers_and_claim_status(): void
    {
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $sellerUser = User::factory()->create(['role' => 'seller']);
        $shop = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Fashion Boutique Official',
            'slug' => 'fashion-boutique',
            'business_type' => 'personal',
            'address' => '789 Ba Trieu, Ha Noi',
            'status' => 'approved',
        ]);

        Product::create([
            'seller_id' => $sellerUser->id,
            'category_id' => $category->id,
            'sku' => 'FASHION-001',
            'thumbnail' => 'products/shirt.jpg',
            'description' => 'Cotton Shirt',
            'name' => 'Korean Oversize Shirt',
            'slug' => 'korean-oversize-shirt',
            'price' => 180000,
            'status' => 'approved',
        ]);

        $shopCoupon = Coupon::create([
            'seller_id' => $sellerUser->id,
            'code' => 'SHOPFASHION20',
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount' => 200000,
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => true,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        // Check unauthenticated view of shop page
        $publicResponse = $this->get("/shops/{$shop->id}");
        $publicResponse->assertStatus(200)
            ->assertSee('MÃ GIẢM GIÁ CỦA SHOP')
            ->assertSee('Giảm 20%');

        // Customer saves coupon
        $customer->savedCoupons()->attach($shopCoupon->id, ['status' => 'saved']);

        // Check authenticated view shows "Đã Lưu"
        $authResponse = $this->actingAs($customer)->get("/shops/{$shop->id}");
        $authResponse->assertStatus(200)
            ->assertSee('Đã Lưu');
    }
}
