<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerOrder;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected Order $order;

    protected SellerOrder $sellerOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $customer = User::factory()->create(['role' => 'client']);
        $this->seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Test',
            'slug' => 'shop-test',
            'address' => 'Ha Noi',
            'national_id' => '012345678901',
            'status' => 'approved',
        ]);

        // Tao don hang mau
        $this->order = Order::create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'total_item_amount' => 200000,
            'total_shipping_fee' => 30000,
            'total_discount' => 0,
            'grand_total' => 230000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_name' => 'Nguyen Van A',
            'shipping_phone' => '0901234567',
            'shipping_address' => '123 Nguyen Hue, HCM',
        ]);

        $this->sellerOrder = SellerOrder::create([
            'order_id' => $this->order->id,
            'seller_id' => $this->seller->id,
            'sub_total' => 200000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'grand_total' => 230000,
            'commission_amount' => 23000,
            'status' => 'pending',
        ]);

        $category = Category::firstOrCreate(['slug' => 'test'], ['name' => 'Test']);
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'San pham A',
            'slug' => 'san-pham-a-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'Mo ta',
            'thumbnail' => 'placeholder.jpg',
            'price' => 200000,
            'stock' => 10,
            'status' => 'approved',
        ]);

        OrderItem::create([
            'seller_order_id' => $this->sellerOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 200000,
            'quantity' => 1,
            'total' => 200000,
        ]);
    }

    // ----------- Index -----------

    public function test_admin_can_view_orders_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));

        $response->assertStatus(200)->assertViewIs('admin.orders.index');
    }

    public function test_admin_can_load_orders_list_via_ajax(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.orders.index'));

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'total', 'current_page']);
    }

    public function test_admin_can_filter_orders_by_payment_status(): void
    {
        // Tao them 1 don hang da thanh toan
        $paidOrder = Order::create([
            'order_number' => 'ORD-PAID-'.uniqid(),
            'user_id' => $this->order->user_id,
            'total_item_amount' => 100000,
            'total_shipping_fee' => 0,
            'total_discount' => 0,
            'grand_total' => 100000,
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'shipping_name' => 'Khach Hang B',
            'shipping_phone' => '0912345678',
            'shipping_address' => 'Ho Chi Minh',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.orders.index', ['payment_status' => 'paid']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('paid', $item['payment_status']);
        }
    }

    public function test_admin_can_search_orders_by_order_number(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.orders.index', ['q' => $this->order->order_number]));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals($this->order->order_number, $data[0]['order_number']);
    }

    // ----------- Show -----------

    public function test_admin_can_view_order_detail_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $this->order));

        $response->assertStatus(200)->assertViewIs('admin.orders.show');
    }

    public function test_admin_can_get_order_detail_via_ajax(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.orders.show', $this->order));

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('data.seller_orders'));
    }

    // ----------- Update Seller Order Status -----------

    public function test_admin_can_update_seller_order_status_to_confirmed(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(
                route('admin.orders.seller-orders.update-status', $this->sellerOrder),
                ['status' => 'confirmed']
            );

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('seller_orders', [
            'id' => $this->sellerOrder->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_update_to_shipping_requires_tracking_number(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(
                route('admin.orders.seller-orders.update-status', $this->sellerOrder),
                ['status' => 'shipping'] // thieu tracking_number
            );

        $response->assertStatus(422)->assertJsonValidationErrors(['tracking_number']);
    }

    public function test_admin_can_update_to_shipping_with_tracking_number(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(
                route('admin.orders.seller-orders.update-status', $this->sellerOrder),
                ['status' => 'shipping', 'tracking_number' => 'VN123456789']
            );

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('seller_orders', [
            'id' => $this->sellerOrder->id,
            'status' => 'shipping',
            'tracking_number' => 'VN123456789',
        ]);
    }

    public function test_admin_update_to_cancelled_requires_cancel_reason(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(
                route('admin.orders.seller-orders.update-status', $this->sellerOrder),
                ['status' => 'cancelled'] // thieu cancel_reason
            );

        $response->assertStatus(422)->assertJsonValidationErrors(['cancel_reason']);
    }

    public function test_admin_can_cancel_seller_order_with_reason(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(
                route('admin.orders.seller-orders.update-status', $this->sellerOrder),
                ['status' => 'cancelled', 'cancel_reason' => 'Khach hang yeu cau huy don.']
            );

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('seller_orders', [
            'id' => $this->sellerOrder->id,
            'status' => 'cancelled',
        ]);
    }

    // ----------- Authorization -----------

    public function test_non_admin_cannot_access_orders(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->get(route('admin.orders.index'));

        $response->assertStatus(403);
    }

    // ----------- Export -----------

    public function test_admin_can_export_orders_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.export'));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
