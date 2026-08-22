<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\SellerProfile;
use App\Models\ShippingCarrier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShippingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected User $buyer;

    protected ShippingCarrier $carrier;

    protected SellerOrder $sellerOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $this->buyer = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Giao Nhanh HN',
            'slug' => 'shop-giao-nhanh-hn',
            'address' => '123 Pham Van Dong, Ha Noi',
            'national_id' => '012345678901',
            'status' => 'approved',
        ]);

        $this->carrier = ShippingCarrier::create([
            'name' => 'SPX Express',
            'code' => 'spx',
            'base_fee' => 25000.00,
            'estimated_days' => '1 - 3 ngày',
            'hotline' => '19001221',
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-SHIP-001',
            'user_id' => $this->buyer->id,
            'total_item_amount' => 300000.00,
            'total_shipping_fee' => 25000.00,
            'total_discount' => 0.00,
            'grand_total' => 325000.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_name' => 'Nguyen Van Khach',
            'shipping_phone' => '0987654321',
            'shipping_address' => '456 Tran Phu, Da Nang',
        ]);

        $this->sellerOrder = SellerOrder::create([
            'order_id' => $order->id,
            'seller_id' => $this->seller->id,
            'sub_total' => 300000.00,
            'shipping_fee' => 25000.00,
            'discount_amount' => 0.00,
            'grand_total' => 325000.00,
            'commission_amount' => 15000.00,
            'status' => 'pending',
            'tracking_number' => 'SPXVN123456',
            'carrier_id' => $this->carrier->id,
        ]);
    }

    /* ---- 1. Xem danh sách đối tác vận chuyển (Blade) ---- */
    public function test_admin_can_view_shipping_carriers_blade(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.shipping.carriers.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.shipping.index')
            ->assertSee($this->carrier->name);
    }

    /* ---- 2. Lấy dữ liệu đối tác JSON ---- */
    public function test_admin_can_fetch_shipping_carriers_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.shipping.carriers.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'stats' => ['total_carriers', 'active_carriers', 'total_shipments', 'in_transit_count'],
            ]);
    }

    /* ---- 3. Bật / Tắt hoạt động đối tác vận chuyển ---- */
    public function test_admin_can_toggle_carrier_active_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.shipping.carriers.toggle', $this->carrier));

        $response->assertStatus(200)
            ->assertJsonPath('is_active', false);

        $this->carrier->refresh();
        $this->assertFalse($this->carrier->is_active);
    }

    /* ---- 4. Đặt hãng làm mặc định sàn ---- */
    public function test_admin_can_set_default_carrier(): void
    {
        $carrier2 = ShippingCarrier::create([
            'name' => 'GHN Express',
            'code' => 'ghn',
            'base_fee' => 28000.00,
            'estimated_days' => '1 - 2 ngày',
            'is_active' => true,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.shipping.carriers.default', $carrier2));

        $response->assertStatus(200);

        $this->carrier->refresh();
        $carrier2->refresh();

        $this->assertFalse($this->carrier->is_default);
        $this->assertTrue($carrier2->is_default);
    }

    /* ---- 5. Cập nhật cước phí và thông tin hãng ---- */
    public function test_admin_can_update_carrier_fee_and_info(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.shipping.carriers.update', $this->carrier), [
                'base_fee' => 30000.00,
                'estimated_days' => '2 - 3 ngày',
                'hotline' => '19009999',
                'description' => 'Mô tả cập nhật mới',
            ]);

        $response->assertStatus(200);

        $this->carrier->refresh();
        $this->assertEquals(30000.00, $this->carrier->base_fee);
        $this->assertEquals('2 - 3 ngày', $this->carrier->estimated_days);
        $this->assertEquals('19009999', $this->carrier->hotline);
    }

    /* ---- 6. Cập nhật phí âm bị chặn 422 ---- */
    public function test_carrier_update_validation_fails_on_negative_fee(): void
    {
        $this->actingAs($this->admin)
            ->putJson(route('admin.shipping.carriers.update', $this->carrier), [
                'base_fee' => -5000,
                'estimated_days' => '1 ngày',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['base_fee']);
    }

    /* ---- 7. Xem danh sách kiện hàng (Blade) ---- */
    public function test_admin_can_view_shipments_orders_blade(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.shipping.orders'))
            ->assertStatus(200)
            ->assertViewIs('admin.shipping.orders');
    }

    /* ---- 8. Lấy danh sách kiện hàng JSON ---- */
    public function test_admin_can_fetch_shipments_orders_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.shipping.orders'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total_all', 'total_pending', 'total_confirmed', 'total_shipping', 'total_completed'],
            ]);
    }

    /* ---- 9. Xem chi tiết lộ trình bưu cục (Tracking timeline) ---- */
    public function test_admin_can_view_tracking_timeline(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.shipping.tracking', $this->sellerOrder));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'order_number',
                'tracking_number',
                'carrier_name',
                'recipient' => ['name', 'phone', 'address'],
                'timeline',
            ]);
    }

    /* ---- 10. Giả lập bước vận chuyển tiếp theo (Simulate 1-Click) ---- */
    public function test_admin_can_simulate_next_shipping_step(): void
    {
        $this->assertEquals('pending', $this->sellerOrder->status);

        // Bấm bước 1: pending -> confirmed
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.shipping.simulate', $this->sellerOrder));

        $response->assertStatus(200)
            ->assertJsonPath('data.current_status', 'confirmed');

        $this->sellerOrder->refresh();
        $this->assertEquals('confirmed', $this->sellerOrder->status);
        $this->assertDatabaseHas('order_shipping_logs', [
            'seller_order_id' => $this->sellerOrder->id,
            'status' => 'preparing',
        ]);

        // Bấm bước 2: confirmed -> shipping
        $response2 = $this->actingAs($this->admin)
            ->postJson(route('admin.shipping.simulate', $this->sellerOrder));

        $response2->assertStatus(200)
            ->assertJsonPath('data.current_status', 'shipping');

        $this->sellerOrder->refresh();
        $this->assertEquals('shipping', $this->sellerOrder->status);
    }

    /* ---- 11. Tạo mã giảm giá loại Miễn phí vận chuyển (Freeship) ---- */
    public function test_admin_can_create_free_shipping_coupon(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.coupons.store'), [
                'code' => 'FREESHIP100K',
                'type' => 'free_shipping',
                'value' => 100, // 100% Freeship
                'min_order_amount' => 100000,
                'max_discount' => 30000,
                'usage_limit' => 100,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'free_shipping');

        $this->assertDatabaseHas('coupons', [
            'code' => 'FREESHIP100K',
            'type' => 'free_shipping',
        ]);
    }

    /* ---- 12. Phân quyền: Kế toán (Accountant) chỉ có quyền xem, không được sửa cước ---- */
    public function test_accountant_cannot_modify_carrier_settings(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant', 'status' => 'active']);

        // Xem danh sách: Cho phép (200)
        $this->actingAs($accountant)
            ->get(route('admin.shipping.carriers.index'))
            ->assertStatus(200);

        // Bật/tắt hãng: Bị chặn (403)
        $this->actingAs($accountant)
            ->patchJson(route('admin.shipping.carriers.toggle', $this->carrier))
            ->assertStatus(403);

        // Cập nhật cước: Bị chặn (403)
        $this->actingAs($accountant)
            ->putJson(route('admin.shipping.carriers.update', $this->carrier), [
                'base_fee' => 35000,
                'estimated_days' => '1 ngày',
            ])
            ->assertStatus(403);
    }

    /* ---- 13. Phân quyền: Kiểm duyệt viên (Moderator) được mô phỏng nhưng không được sửa cước hãng ---- */
    public function test_moderator_can_simulate_but_cannot_update_carrier(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);

        // Sửa cước: Bị chặn (403)
        $this->actingAs($moderator)
            ->putJson(route('admin.shipping.carriers.update', $this->carrier), [
                'base_fee' => 35000,
                'estimated_days' => '1 ngày',
            ])
            ->assertStatus(403);

        // Mô phỏng giao hàng: Được phép (200)
        $this->actingAs($moderator)
            ->postJson(route('admin.shipping.simulate', $this->sellerOrder))
            ->assertStatus(200)
            ->assertJsonPath('data.current_status', 'confirmed');
    }
}
