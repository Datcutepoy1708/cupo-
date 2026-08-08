<?php

namespace Tests\Feature\Seller;

use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerOrderTest extends TestCase
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
            'status' => 'approved',
        ]);

        return $seller;
    }

    public function test_seller_can_view_own_orders_list(): void
    {
        $seller = $this->createApprovedSeller();
        $customer = User::factory()->create(['role' => 'customer']);

        $masterOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-123',
            'shipping_name' => 'Khách A',
            'shipping_phone' => '0987654321',
            'shipping_address' => 'HN',
            'payment_method' => 'cod',
            'total_item_amount' => 100000,
            'grand_total' => 100000,
        ]);

        SellerOrder::create([
            'order_id' => $masterOrder->id,
            'seller_id' => $seller->id,
            'sub_total' => 100000,
            'grand_total' => 100000,
            'commission_amount' => 0,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)->getJson('/seller/orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_seller_can_update_order_status_to_shipping_with_tracking_number(): void
    {
        $seller = $this->createApprovedSeller();
        $customer = User::factory()->create(['role' => 'customer']);

        $masterOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-456',
            'shipping_name' => 'Khách B',
            'shipping_phone' => '0987654321',
            'shipping_address' => 'HN',
            'payment_method' => 'cod',
            'total_item_amount' => 200000,
            'grand_total' => 200000,
        ]);

        $sellerOrder = SellerOrder::create([
            'order_id' => $masterOrder->id,
            'seller_id' => $seller->id,
            'sub_total' => 200000,
            'grand_total' => 200000,
            'commission_amount' => 0,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)->patchJson("/seller/orders/{$sellerOrder->id}/status", [
            'status' => 'shipping',
            'tracking_number' => 'VN-EXPRESS-999',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cập nhật trạng thái đơn hàng thành công!');

        $this->assertEquals('shipping', $sellerOrder->fresh()->status);
        $this->assertEquals('VN-EXPRESS-999', $sellerOrder->fresh()->tracking_number);
    }

    public function test_seller_cannot_update_other_sellers_order(): void
    {
        $seller1 = $this->createApprovedSeller();
        $seller2 = $this->createApprovedSeller();
        $customer = User::factory()->create(['role' => 'customer']);

        $masterOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-789',
            'shipping_name' => 'Khách C',
            'shipping_phone' => '0987654321',
            'shipping_address' => 'HN',
            'payment_method' => 'cod',
            'total_item_amount' => 100000,
            'grand_total' => 100000,
        ]);

        $seller1Order = SellerOrder::create([
            'order_id' => $masterOrder->id,
            'seller_id' => $seller1->id,
            'sub_total' => 100000,
            'grand_total' => 100000,
            'commission_amount' => 0,
            'status' => 'pending',
        ]);

        // Seller 2 cố tình sửa đơn của Seller 1
        $response = $this->actingAs($seller2)->patchJson("/seller/orders/{$seller1Order->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Bạn không có quyền cập nhật đơn hàng này.');
    }
}
