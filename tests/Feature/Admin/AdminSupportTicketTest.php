<?php

namespace Tests\Feature\Admin;

use App\Models\SellerProfile;
use App\Models\SellerSupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected SellerSupportTicket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Thoi Trang ABC',
            'slug' => 'shop-thoi-trang-abc',
            'address' => '123 Nguyen Hue, TP HCM',
            'national_id' => '123456789012',
            'status' => 'approved',
        ]);

        $this->ticket = SellerSupportTicket::create([
            'seller_id' => $this->seller->id,
            'category' => 'account_blocked',
            'subject' => 'Kháng nghị tài khoản bị tạm khóa',
            'message' => 'Kính nhờ Admin xem xét mở lại shop cho chúng tôi.',
            'status' => 'open',
        ]);
    }

    /* ---- 1. Danh sách (Blade) ---- */
    public function test_admin_can_view_support_tickets_blade(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.support-tickets.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.support-tickets.index');
    }

    /* ---- 2. Danh sách (AJAX JSON) ---- */
    public function test_admin_can_fetch_support_tickets_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.support-tickets.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total_all', 'total_open', 'total_in_review', 'total_resolved', 'total_closed'],
            ]);
    }

    /* ---- 3. Lọc theo trạng thái ---- */
    public function test_admin_can_filter_by_status(): void
    {
        SellerSupportTicket::create([
            'seller_id' => $this->seller->id,
            'category' => 'withdrawal_issue',
            'subject' => 'Sự cố rút tiền',
            'message' => 'Tiền chưa về tài khoản.',
            'status' => 'resolved',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.support-tickets.index', ['status' => 'resolved']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('resolved', $item['status']);
        }
    }

    /* ---- 4. Lọc theo danh mục ---- */
    public function test_admin_can_filter_by_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.support-tickets.index', ['category' => 'account_blocked']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('account_blocked', $item['category']);
        }
    }

    /* ---- 5. Tìm kiếm theo từ khóa ---- */
    public function test_admin_can_search_tickets(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.support-tickets.index', ['search' => 'Kháng nghị']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    /* ---- 6. Xem chi tiết ticket ---- */
    public function test_admin_can_view_ticket_detail(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.support-tickets.show', $this->ticket))
            ->assertStatus(200)
            ->assertViewIs('admin.support-tickets.show')
            ->assertSee($this->ticket->subject);
    }

    /* ---- 7. Tiếp nhận xử lý (open -> in_review) ---- */
    public function test_admin_can_mark_ticket_in_review(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.support-tickets.in-review', $this->ticket));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_review');

        $this->assertDatabaseHas('seller_support_tickets', [
            'id' => $this->ticket->id,
            'status' => 'in_review',
        ]);
    }

    /* ---- 8. Gửi phản hồi và giải quyết ticket ---- */
    public function test_admin_can_respond_and_resolve_ticket(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.support-tickets.respond', $this->ticket), [
                'admin_response' => 'Admin đã gỡ cảnh báo và kích hoạt lại shop cho bạn.',
                'action_status' => 'resolved',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'resolved');

        $this->assertDatabaseHas('seller_support_tickets', [
            'id' => $this->ticket->id,
            'status' => 'resolved',
            'admin_response' => 'Admin đã gỡ cảnh báo và kích hoạt lại shop cho bạn.',
            'resolved_by' => $this->admin->id,
        ]);
    }

    /* ---- 9. Phản hồi thiếu nội dung -> 422 ---- */
    public function test_admin_cannot_respond_without_message(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.support-tickets.respond', $this->ticket), [
                'admin_response' => '',
                'action_status' => 'resolved',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['admin_response']);
    }

    /* ---- 10. Non-admin không được truy cập ---- */
    public function test_non_admin_cannot_access_support_tickets(): void
    {
        $this->actingAs($this->seller)
            ->get(route('admin.support-tickets.index'))
            ->assertStatus(403);
    }
}
