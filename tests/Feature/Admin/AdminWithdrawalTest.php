<?php

namespace Tests\Feature\Admin;

use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected SellerProfile $sellerProfile;

    protected Withdrawal $withdrawal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        $this->sellerProfile = SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Cong Nghe Test',
            'slug' => 'shop-cong-nghe-test',
            'address' => '123 Pham Van Dong, Ha Noi',
            'national_id' => '012345678901',
            'balance' => 2000000.00,
            'bank_name' => 'Vietcombank',
            'bank_account' => '1012345678',
            'bank_owner' => 'NGUYEN VAN TEST',
            'status' => 'approved',
        ]);

        $this->withdrawal = Withdrawal::create([
            'seller_id' => $this->seller->id,
            'amount' => 500000.00,
            'bank_name' => 'Vietcombank',
            'bank_account' => '1012345678',
            'bank_owner' => 'NGUYEN VAN TEST',
            'status' => 'pending',
        ]);
    }

    /* ---- 1. Danh sách (Blade) ---- */
    public function test_admin_can_view_withdrawals_blade(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.withdrawals.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.withdrawals.index');
    }

    /* ---- 2. Danh sách (AJAX JSON) ---- */
    public function test_admin_can_fetch_withdrawals_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.withdrawals.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total_all', 'total_pending', 'total_approved', 'total_rejected', 'total_paid'],
            ]);
    }

    /* ---- 3. Lọc theo trạng thái ---- */
    public function test_admin_can_filter_by_status(): void
    {
        Withdrawal::create([
            'seller_id' => $this->seller->id,
            'amount' => 300000.00,
            'bank_name' => 'Techcombank',
            'bank_account' => '1901234567',
            'bank_owner' => 'NGUYEN VAN TEST',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.withdrawals.index', ['status' => 'approved']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('approved', $item['status']);
        }
    }

    /* ---- 4. Tìm kiếm theo tên ngân hàng hoặc số tài khoản ---- */
    public function test_admin_can_search_by_shop_name_or_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.withdrawals.index', ['search' => '1012345678']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    /* ---- 5. Duyệt yêu cầu rút tiền thành công (Trừ balance + Ghi log) ---- */
    public function test_admin_can_approve_withdrawal(): void
    {
        $initialBalance = $this->sellerProfile->balance;

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.withdrawals.approve', $this->withdrawal));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        // Kiểm tra database Withdrawal
        $this->assertDatabaseHas('withdrawals', [
            'id' => $this->withdrawal->id,
            'status' => 'approved',
        ]);

        // Kiểm tra Seller Profile balance đã bị trừ đúng 500,000đ
        $this->sellerProfile->refresh();
        $this->assertEquals($initialBalance - $this->withdrawal->amount, $this->sellerProfile->balance);

        // Kiểm tra SellerBalanceLog được tạo
        $this->assertDatabaseHas('seller_balance_logs', [
            'seller_id' => $this->seller->id,
            'type' => 'withdrawal',
            'reference_id' => $this->withdrawal->id,
            'amount' => -$this->withdrawal->amount,
        ]);
    }

    /* ---- 6. Không thể duyệt yêu cầu đã xử lý xong ---- */
    public function test_admin_cannot_approve_already_processed_withdrawal(): void
    {
        $this->withdrawal->update(['status' => 'approved']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.withdrawals.approve', $this->withdrawal))
            ->assertStatus(422);
    }

    /* ---- 7. Hệ thống tự động chặn khi số dư Seller không đủ (balance < amount) ---- */
    public function test_admin_cannot_approve_if_seller_balance_insufficient(): void
    {
        // Giảm số dư của seller xuống thấp hơn số tiền muốn rút
        $this->sellerProfile->update(['balance' => 200000.00]);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.withdrawals.approve', $this->withdrawal));

        $response->assertStatus(422)
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'Số dư ví của Seller không đủ'));

        // Trạng thái giữ nguyên pending và số dư không đổi
        $this->assertDatabaseHas('withdrawals', [
            'id' => $this->withdrawal->id,
            'status' => 'pending',
        ]);
        $this->sellerProfile->refresh();
        $this->assertEquals(200000.00, $this->sellerProfile->balance);
    }

    /* ---- 8. Từ chối yêu cầu rút tiền (Balance không đổi) ---- */
    public function test_admin_can_reject_withdrawal(): void
    {
        $initialBalance = $this->sellerProfile->balance;

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.withdrawals.reject', $this->withdrawal), [
                'admin_note' => 'Số tài khoản không đúng tên chủ thẻ đã đăng ký.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('withdrawals', [
            'id' => $this->withdrawal->id,
            'status' => 'rejected',
            'admin_note' => 'Số tài khoản không đúng tên chủ thẻ đã đăng ký.',
        ]);

        // Số dư Seller không đổi
        $this->sellerProfile->refresh();
        $this->assertEquals($initialBalance, $this->sellerProfile->balance);
    }

    /* ---- 9. Từ chối thiếu lý do -> 422 ---- */
    public function test_admin_cannot_reject_without_reason(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.withdrawals.reject', $this->withdrawal), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['admin_note']);
    }

    /* ---- 10. Xem chi tiết yêu cầu rút tiền ---- */
    public function test_admin_can_view_withdrawal_detail(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.withdrawals.show', $this->withdrawal))
            ->assertStatus(200)
            ->assertViewIs('admin.withdrawals.show')
            ->assertSee($this->withdrawal->bank_account);
    }

    /* ---- 11. Xuất danh sách CSV ---- */
    public function test_admin_can_export_withdrawals_csv(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.withdrawals.export'))
            ->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /* ---- 12. Non-admin không được truy cập ---- */
    public function test_non_admin_cannot_access_withdrawals(): void
    {
        $this->actingAs($this->seller)
            ->get(route('admin.withdrawals.index'))
            ->assertStatus(403);
    }
}
