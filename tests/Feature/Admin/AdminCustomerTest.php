<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    /* ---- 1. Danh sách (Blade) ---- */
    public function test_admin_can_view_customer_list_as_blade(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.customers.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.customers.index');
    }

    /* ---- 2. Danh sách (AJAX JSON) ---- */
    public function test_admin_can_fetch_customer_list_as_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.customers.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total_all', 'total_active', 'total_blocked', 'total_new_30d'],
            ]);
    }

    /* ---- 3. Lọc theo trạng thái ---- */
    public function test_admin_can_filter_customers_by_status(): void
    {
        User::factory()->create(['role' => 'customer', 'status' => 'blocked']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.customers.index', ['status' => 'blocked']));

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('blocked', $item['status']);
        }
    }

    /* ---- 4. Tìm kiếm theo keyword ---- */
    public function test_admin_can_search_customers_by_keyword(): void
    {
        User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'name' => 'Nguyen Van An',
            'email' => 'an@example.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.customers.index', ['search' => 'Nguyen Van An']));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
    }

    /* ---- 5. Xem hồ sơ chi tiết ---- */
    public function test_admin_can_view_customer_detail(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $this->customer))
            ->assertStatus(200)
            ->assertViewIs('admin.customers.show')
            ->assertSee($this->customer->name);
    }

    /* ---- 6. Không thể xem hồ sơ seller/admin qua route này ---- */
    public function test_admin_cannot_view_non_customer_user(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $seller))
            ->assertStatus(403);
    }

    /* ---- 7. Khóa tài khoản ---- */
    public function test_admin_can_block_a_customer(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.customers.block', $this->customer), [
                'admin_note' => 'Vi phạm điều khoản dịch vụ.',
            ])
            ->assertStatus(200);

        $this->assertStringContainsString($this->customer->name, $response->json('message'));

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'status' => 'blocked',
        ]);
    }

    /* ---- 8. Mở khóa tài khoản ---- */
    public function test_admin_can_unblock_a_customer(): void
    {
        $this->customer->update(['status' => 'blocked']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.customers.unblock', $this->customer))
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'status' => 'active',
        ]);
    }

    /* ---- 9. Không thể khóa nếu thiếu admin_note ---- */
    public function test_admin_cannot_block_without_reason(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.customers.block', $this->customer), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['admin_note']);
    }

    /* ---- 10. Export CSV ---- */
    public function test_admin_can_export_customers_as_csv(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.customers.export'))
            ->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /* ---- 11. Non-admin không thể truy cập ---- */
    public function test_non_admin_cannot_access_customers(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.customers.index'))
            ->assertStatus(403);
    }

    /* ---- 12. Meta JSON chứa đầy đủ các trường ---- */
    public function test_json_response_contains_stat_meta(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.customers.index'));

        $response->assertJsonStructure([
            'meta' => ['total_all', 'total_active', 'total_blocked', 'total_new_30d'],
        ]);

        $meta = $response->json('meta');
        $this->assertGreaterThanOrEqual(0, $meta['total_all']);
        $this->assertGreaterThanOrEqual(0, $meta['total_active']);
        $this->assertGreaterThanOrEqual(0, $meta['total_blocked']);
        $this->assertGreaterThanOrEqual(0, $meta['total_new_30d']);
    }
}
