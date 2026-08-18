<?php

namespace Tests\Feature\Admin;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\SellerBalanceLog;
use App\Models\SellerOrder;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDisputeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $buyer;

    protected User $seller;

    protected SellerProfile $sellerProfile;

    protected Order $order;

    protected SellerOrder $sellerOrder;

    protected Dispute $dispute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->buyer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        $this->sellerProfile = SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Test Phuc',
            'slug' => 'shop-test-phuc',
            'address' => '123 Pham Van Dong',
            'national_id' => '012345678999',
            'balance' => 1000000,
            'status' => 'approved',
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-DISPUTE-01',
            'user_id' => $this->buyer->id,
            'total_item_amount' => 200000,
            'total_shipping_fee' => 30000,
            'total_discount' => 0,
            'grand_total' => 230000,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'shipping_name' => $this->buyer->name,
            'shipping_phone' => '0987654321',
            'shipping_address' => 'Ha Noi',
        ]);

        $this->sellerOrder = SellerOrder::create([
            'order_id' => $this->order->id,
            'seller_id' => $this->seller->id,
            'sub_total' => 200000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'grand_total' => 230000,
            'commission_amount' => 23000,
            'status' => 'confirmed',
        ]);

        $this->dispute = Dispute::create([
            'seller_order_id' => $this->sellerOrder->id,
            'buyer_id' => $this->buyer->id,
            'reason' => 'Sản phẩm giao bị vỡ bể và không dùng được.',
            'status' => 'pending',
        ]);
    }

    /* ---- 1. Danh sách (Blade) ---- */
    public function test_admin_can_view_disputes_blade(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.disputes.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.disputes.index');
    }

    /* ---- 2. Danh sách (AJAX JSON) ---- */
    public function test_admin_can_fetch_disputes_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.disputes.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total_all', 'total_pending', 'total_in_progress', 'total_refunded', 'total_rejected'],
            ]);
    }

    /* ---- 3. Lọc theo trạng thái ---- */
    public function test_admin_can_filter_by_status(): void
    {
        Dispute::create([
            'seller_order_id' => $this->sellerOrder->id,
            'buyer_id' => $this->buyer->id,
            'reason' => 'Khác lý do',
            'status' => 'refunded',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.disputes.index', ['status' => 'refunded']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('refunded', $item['status']);
        }
    }

    /* ---- 4. Tiếp nhận xử lý (pending -> in_progress) ---- */
    public function test_admin_can_process_dispute(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.process', $this->dispute));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('disputes', [
            'id' => $this->dispute->id,
            'status' => 'in_progress',
        ]);
    }

    /* ---- 5. Không thể tiếp nhận tranh chấp đã kết thúc ---- */
    public function test_admin_cannot_process_finished_dispute(): void
    {
        $this->dispute->update(['status' => 'refunded']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.process', $this->dispute))
            ->assertStatus(422);
    }

    /* ---- 6. Hoàn tiền tranh chấp ---- */
    public function test_admin_can_refund_dispute(): void
    {
        $initialBalance = $this->sellerProfile->balance;

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.refund', $this->dispute), [
                'admin_decision' => 'Chấp thuận yêu cầu hoàn tiền do lỗi nhà bán.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'refunded');

        // Kiểm tra Dispute status
        $this->assertDatabaseHas('disputes', [
            'id' => $this->dispute->id,
            'status' => 'refunded',
            'admin_decision' => 'Chấp thuận yêu cầu hoàn tiền do lỗi nhà bán.',
        ]);

        // Kiểm tra SellerOrder status
        $this->assertDatabaseHas('seller_orders', [
            'id' => $this->sellerOrder->id,
            'status' => 'cancelled',
        ]);

        // Kiểm tra Seller Balance bị trừ
        $this->sellerProfile->refresh();
        $this->assertEquals($initialBalance - $this->sellerOrder->grand_total, $this->sellerProfile->balance);
    }

    /* ---- 6b. Hoàn tiền tranh chấp khi số dư không đủ -> Số dư bị Âm (ghi nợ) ---- */
    public function test_admin_dispute_refund_allows_negative_seller_balance(): void
    {
        // Đặt số dư của seller nhỏ hơn số tiền đơn hàng cần hoàn (có 100k, hoàn 300k)
        $this->sellerProfile->update(['balance' => 100000.00]);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.refund', $this->dispute), [
                'admin_decision' => 'Hoàn tiền gấp cho khách, số dư seller bị ghi nợ.',
            ]);

        $response->assertStatus(200);

        // Số dư seller chuyển thành 100,000 - 230,000 = -130,000đ
        $this->sellerProfile->refresh();
        $this->assertEquals(-130000.00, $this->sellerProfile->balance);
    }

    /* ---- 7. Hoàn tiền thiếu lý do -> 422 ---- */
    public function test_admin_cannot_refund_without_decision(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.refund', $this->dispute), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['admin_decision']);
    }

    /* ---- 8. Không thể hoàn tiền tranh chấp đã kết thúc ---- */
    public function test_admin_cannot_refund_finished_dispute(): void
    {
        $this->dispute->update(['status' => 'rejected']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.refund', $this->dispute), [
                'admin_decision' => 'Hoàn tiền lại',
            ])
            ->assertStatus(422);
    }

    /* ---- 9. Từ chối khiếu nại ---- */
    public function test_admin_can_reject_dispute(): void
    {
        $initialBalance = $this->sellerProfile->balance;

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.reject', $this->dispute), [
                'admin_decision' => 'Khách hàng không cung cấp đủ bằng chứng chứng minh.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('disputes', [
            'id' => $this->dispute->id,
            'status' => 'rejected',
            'admin_decision' => 'Khách hàng không cung cấp đủ bằng chứng chứng minh.',
        ]);

        // Số dư Seller không đổi
        $this->sellerProfile->refresh();
        $this->assertEquals($initialBalance, $this->sellerProfile->balance);
    }

    /* ---- 10. Từ chối thiếu lý do -> 422 ---- */
    public function test_admin_cannot_reject_without_decision(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.reject', $this->dispute), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['admin_decision']);
    }

    /* ---- 11. Xem chi tiết tranh chấp ---- */
    public function test_admin_can_view_dispute_detail(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.disputes.show', $this->dispute))
            ->assertStatus(200)
            ->assertViewIs('admin.disputes.show')
            ->assertSee($this->dispute->reason);
    }

    /* ---- 12. Export CSV ---- */
    public function test_admin_can_export_disputes_csv(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.disputes.export'))
            ->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /* ---- 13. Non-admin không được truy cập ---- */
    public function test_non_admin_cannot_access_disputes(): void
    {
        $this->actingAs($this->buyer)
            ->get(route('admin.disputes.index'))
            ->assertStatus(403);
    }

    /* ---- 14. Atomic Transaction: log SellerBalanceLog được tạo khi hoàn tiền ---- */
    public function test_refund_is_atomic_transaction(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.disputes.refund', $this->dispute), [
                'admin_decision' => 'Đã xác minh hoàn tiền',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('seller_balance_logs', [
            'seller_id' => $this->seller->id,
            'type' => 'refund',
            'reference_id' => $this->dispute->id,
            'amount' => -$this->sellerOrder->grand_total,
        ]);
    }
}
