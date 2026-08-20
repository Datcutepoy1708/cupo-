<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerOrder;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $accountant;

    protected User $moderator;

    protected User $seller;

    protected User $customer;

    protected SellerOrder $completedSellerOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->accountant = User::factory()->create(['role' => 'accountant', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $this->customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        $profile = SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Thử Nghiệm Analytics',
            'slug' => 'shop-thu-nghiem-analytics',
            'address' => '123 Ha Noi',
            'national_id' => '123456789012',
            'balance' => 950000,
            'status' => 'approved',
        ]);

        $category = Category::create(['name' => 'Thiết bị số', 'slug' => 'thiet-bi-so']);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Bàn Phím Cơ',
            'slug' => 'ban-phim-co',
            'sku' => 'SKU-KEYBOARD-01',
            'thumbnail' => 'products/keyboard.jpg',
            'description' => 'Bàn phím cơ cao cấp',
            'price' => 1000000,
            'stock' => 50,
            'status' => 'approved',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-ANALYTICS-001',
            'user_id' => $this->customer->id,
            'total_item_amount' => 1000000,
            'total_shipping_fee' => 30000,
            'total_discount' => 0,
            'grand_total' => 1030000,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'shipping_name' => 'Nguyen Van A',
            'shipping_phone' => '0901234567',
            'shipping_address' => '123 Pham Van Dong',
            'status' => 'completed',
        ]);

        $this->completedSellerOrder = SellerOrder::create([
            'order_id' => $order->id,
            'seller_id' => $this->seller->id,
            'sub_total' => 1000000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'grand_total' => 1030000,
            'commission_amount' => 50000, // 5%
            'status' => 'completed',
        ]);

        OrderItem::create([
            'seller_order_id' => $this->completedSellerOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 1000000,
            'quantity' => 1,
            'total' => 1000000,
        ]);

        Withdrawal::create([
            'seller_id' => $this->seller->id,
            'amount' => 500000,
            'bank_name' => 'Vietcombank',
            'bank_account' => '123456789',
            'bank_owner' => 'TEST SELLER',
            'status' => 'approved',
        ]);
    }

    /* ---- 1. Admin & Kế toán (Accountant) xem được trang báo cáo ---- */
    public function test_admin_and_accountant_can_view_analytics_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.analytics.index'));
        $response->assertStatus(200)
            ->assertViewIs('admin.analytics.index')
            ->assertSee('Báo cáo Doanh thu', false);

        $responseAcc = $this->actingAs($this->accountant)->get(route('admin.analytics.index'));
        $responseAcc->assertStatus(200);
    }

    /* ---- 2. JSON API tính toán chính xác các chỉ số KPI ---- */
    public function test_admin_can_get_analytics_json_api(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('admin.analytics.index'));

        $response->assertStatus(200)
            ->assertJsonPath('kpis.gmv', 1030000)
            ->assertJsonPath('kpis.commission_revenue', 50000)
            ->assertJsonPath('kpis.disbursed_withdrawals', 500000)
            ->assertJsonPath('kpis.completed_orders', 1);
    }

    /* ---- 3. Xuất file CSV báo cáo tài chính đối soát ---- */
    public function test_admin_can_export_financial_report_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.analytics.export'));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', 'cupo-financial-report-'));
    }

    /* ---- 4. Moderator (Kiểm duyệt viên) không có quyền truy cập báo cáo tài chính ---- */
    public function test_unauthorized_moderator_cannot_access_analytics(): void
    {
        $this->actingAs($this->moderator)
            ->get(route('admin.analytics.index'))
            ->assertStatus(403);

        $this->actingAs($this->moderator)
            ->get(route('admin.analytics.export'))
            ->assertStatus(403);
    }
}
