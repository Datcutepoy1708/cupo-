<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_follow_and_unfollow_a_shop(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop Test',
            'slug' => 'shop-test',
            'address' => 'Hà Nội',
            'national_id' => '012345678901',
            'status' => 'approved',
        ]);

        // Follow shop
        $response = $this->actingAs($customer)
            ->postJson(route('shops.follow.toggle', $seller->id));

        $response->assertOk()
            ->assertJson([
                'is_followed' => true,
                'followers_count' => 1,
            ]);

        $this->assertDatabaseHas('shop_follows', [
            'user_id' => $customer->id,
            'seller_profile_id' => $seller->id,
        ]);

        // Unfollow shop
        $response2 = $this->actingAs($customer)
            ->postJson(route('shops.follow.toggle', $seller->id));

        $response2->assertOk()
            ->assertJson([
                'is_followed' => false,
                'followers_count' => 0,
            ]);

        $this->assertDatabaseMissing('shop_follows', [
            'user_id' => $customer->id,
            'seller_profile_id' => $seller->id,
        ]);
    }

    public function test_seller_cannot_follow_own_shop(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop Own',
            'slug' => 'shop-own',
            'address' => 'Hà Nội',
            'national_id' => '012345678902',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($sellerUser)
            ->postJson(route('shops.follow.toggle', $seller->id));

        $response->assertStatus(422);
    }

    public function test_customer_can_get_list_of_followed_shops(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop Followed',
            'slug' => 'shop-followed',
            'address' => 'Hà Nội',
            'national_id' => '012345678903',
            'status' => 'approved',
        ]);

        $customer->followedShops()->attach($seller->id);

        $response = $this->actingAs($customer)
            ->getJson(route('customer.followed-shops.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $seller->id);
    }

    public function test_seller_profile_category_relationship(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Shop Cat',
            'slug' => 'shop-cat',
            'address' => 'Hà Nội',
            'national_id' => '012345678904',
            'status' => 'approved',
        ]);
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $seller->categories()->attach($category->id);

        $this->assertDatabaseHas('seller_categories', [
            'seller_profile_id' => $seller->id,
            'category_id' => $category->id,
        ]);

        $this->assertEquals('Thời trang', $seller->fresh()->categories->first()->name);
    }
}
