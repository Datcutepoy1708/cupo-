<?php

namespace Tests\Feature\Admin;

use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $admin;

    protected User $moderator;

    protected User $accountant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super-admin', 'status' => 'active']);
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->accountant = User::factory()->create(['role' => 'accountant', 'status' => 'active']);
    }

    /* ---- 1. Admin & Super Admin xem được trang index ---- */
    public function test_admin_can_view_activity_logs_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.index'));
        $response->assertStatus(200)
            ->assertViewIs('admin.activity-logs.index')
            ->assertSee('Nhật ký Hoạt động Nhân viên');
    }

    /* ---- 2. JSON API phân trang & bộ lọc nhật ký ---- */
    public function test_admin_can_get_activity_logs_json_with_filters(): void
    {
        ActivityLogService::log(
            action: 'approve_withdrawal',
            module: 'withdrawals',
            description: 'Duyệt lệnh rút tiền 500.000đ',
            user: $this->admin
        );

        ActivityLogService::log(
            action: 'block_seller',
            module: 'sellers',
            description: 'Khóa gian hàng vi phạm',
            user: $this->admin
        );

        // Lấy tất cả
        $response = $this->actingAs($this->admin)->getJson(route('admin.activity-logs.index'));
        $response->assertStatus(200)
            ->assertJsonPath('total', 2)
            ->assertJsonPath('meta.total_logs', 2);

        // Lọc theo module withdrawals
        $filterResponse = $this->actingAs($this->admin)->getJson(route('admin.activity-logs.index', ['module' => 'withdrawals']));
        $filterResponse->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.module', 'withdrawals');
    }

    /* ---- 3. Xem chi tiết bản ghi nhật ký (Detail Modal API) ---- */
    public function test_admin_can_view_activity_log_detail(): void
    {
        $log = ActivityLogService::log(
            action: 'update_settings',
            module: 'settings',
            description: 'Cập nhật tỷ lệ hoa hồng sàn lên 5%',
            properties: ['old_rate' => 3, 'new_rate' => 5],
            user: $this->superAdmin
        );

        $response = $this->actingAs($this->admin)->getJson(route('admin.activity-logs.show', $log));
        $response->assertStatus(200)
            ->assertJsonPath('data.action', 'update_settings')
            ->assertJsonPath('data.properties.new_rate', 5);
    }

    /* ---- 4. Xuất file CSV nhật ký kiểm toán ---- */
    public function test_admin_can_export_activity_logs_csv(): void
    {
        ActivityLogService::log(
            action: 'approve_seller',
            module: 'sellers',
            description: 'Duyệt gian hàng Shop XYZ',
            user: $this->admin
        );

        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.export'));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', 'cupo-audit-logs-'));
    }

    /* ---- 5. Phân quyền: Moderator & Accountant bị chặn (403) ---- */
    public function test_unauthorized_roles_cannot_access_activity_logs(): void
    {
        $this->actingAs($this->moderator)
            ->get(route('admin.activity-logs.index'))
            ->assertStatus(403);

        $this->actingAs($this->accountant)
            ->get(route('admin.activity-logs.index'))
            ->assertStatus(403);

        $this->actingAs($this->accountant)
            ->get(route('admin.activity-logs.export'))
            ->assertStatus(403);
    }

    /* ---- 6. Tự động ghi log khi duyệt rút tiền ---- */
    public function test_activity_log_created_when_approving_withdrawal(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $profile = SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop Thử Nghiệm',
            'slug' => 'shop-thu-nghiem',
            'address' => '123 Test',
            'national_id' => '123456789012',
            'balance' => 1000000,
            'status' => 'approved',
        ]);

        $withdrawal = Withdrawal::create([
            'seller_id' => $seller->id,
            'amount' => 500000,
            'bank_name' => 'Vietcombank',
            'bank_account' => '1234567890',
            'bank_owner' => 'NGUYEN VAN A',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.withdrawals.approve', $withdrawal))
            ->assertStatus(200);

        $this->assertDatabaseHas('admin_activity_logs', [
            'action' => 'approve_withdrawal',
            'module' => 'withdrawals',
            'user_id' => $this->admin->id,
        ]);
    }

    /* ---- 7. Tự động ghi log khi khóa gian hàng ---- */
    public function test_activity_log_created_when_blocking_seller(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $profile = SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop Vi Phạm',
            'slug' => 'shop-vi-pham',
            'address' => '123 Test',
            'national_id' => '123456789012',
            'status' => 'approved',
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.sellers.block', $profile), [
                'admin_note' => 'Vi phạm chính sách bán hàng cấm',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('admin_activity_logs', [
            'action' => 'block_seller',
            'module' => 'sellers',
            'user_id' => $this->admin->id,
        ]);
    }
}
