<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\SellerSupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $moderator;

    protected User $accountant;

    protected User $seller;

    protected SellerProfile $sellerProfile;

    protected SellerSupportTicket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->accountant = User::factory()->create(['role' => 'accountant', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        $this->sellerProfile = SellerProfile::create([
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

    /* ---- 10. Moderator KHÔNG được xem hoặc xử lý ticket account_blocked -> 403 ---- */
    public function test_moderator_cannot_view_or_respond_to_account_blocked_tickets(): void
    {
        // Moderator vào show() -> 403
        $this->actingAs($this->moderator)
            ->get(route('admin.support-tickets.show', $this->ticket))
            ->assertStatus(403);

        // Moderator gọi in-review -> 403
        $this->actingAs($this->moderator)
            ->patchJson(route('admin.support-tickets.in-review', $this->ticket))
            ->assertStatus(403);

        // Moderator gọi respond -> 403
        $this->actingAs($this->moderator)
            ->patchJson(route('admin.support-tickets.respond', $this->ticket), [
                'admin_response' => 'Thử trả lời',
                'action_status' => 'resolved',
            ])
            ->assertStatus(403);
    }

    /* ---- 11. Accountant xem và xử lý được ticket commission_fee & withdrawal_issue ---- */
    public function test_accountant_can_view_and_respond_to_commission_and_withdrawal_tickets(): void
    {
        $feeTicket = SellerSupportTicket::create([
            'seller_id' => $this->seller->id,
            'category' => 'commission_fee',
            'subject' => 'Hỏi về phí sàn tháng này',
            'message' => 'Shop muốn kiểm tra lại biểu phí.',
            'status' => 'open',
        ]);

        $this->actingAs($this->accountant)
            ->get(route('admin.support-tickets.show', $feeTicket))
            ->assertStatus(200);

        $this->actingAs($this->accountant)
            ->patchJson(route('admin.support-tickets.respond', $feeTicket), [
                'admin_response' => 'Kế toán đã kiểm tra và đối soát biểu phí chuẩn 5%.',
                'action_status' => 'resolved',
            ])
            ->assertStatus(200);
    }

    /* ---- 12. Admin giải quyết ticket account_blocked + unlock_seller = true (CHỈ đổi seller_profiles.status) ---- */
    public function test_admin_can_resolve_ticket_and_unlock_seller_profile_only(): void
    {
        // Khóa shop
        $this->sellerProfile->update(['status' => 'blocked']);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.support-tickets.respond', $this->ticket), [
                'admin_response' => 'Admin chấp thuận kháng nghị và mở lại gian hàng.',
                'action_status' => 'resolved',
                'unlock_seller' => true,
            ]);

        $response->assertStatus(200);

        // seller_profiles.status chuyển thành approved
        $this->sellerProfile->refresh();
        $this->assertEquals('approved', $this->sellerProfile->status);

        // users.status KHÔNG BỊ ĐỤNG CHẠM
        $this->seller->refresh();
        $this->assertEquals('active', $this->seller->status);
    }

    /* ---- 13. Admin/Moderator giải quyết ticket product_rejected + approve_product = true ---- */
    public function test_staff_can_resolve_ticket_and_approve_product(): void
    {
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Áo thun phong cách mới',
            'slug' => 'ao-thun-phong-cach-moi',
            'sku' => 'AOTHUN-001',
            'price' => 150000.00,
            'stock' => 50,
            'thumbnail' => 'products/sample.jpg',
            'description' => 'Mô tả sản phẩm',
            'status' => 'rejected',
        ]);

        $productTicket = SellerSupportTicket::create([
            'seller_id' => $this->seller->id,
            'product_id' => $product->id,
            'category' => 'product_rejected',
            'subject' => 'Kháng nghị duyệt lại sản phẩm Áo thun',
            'message' => 'Shop đã sửa lại ảnh sản phẩm theo đúng quy định.',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->moderator)
            ->patchJson(route('admin.support-tickets.respond', $productTicket), [
                'admin_response' => 'Sản phẩm đã hợp lệ, chấp thuận duyệt lại.',
                'action_status' => 'resolved',
                'approve_product' => true,
            ]);

        $response->assertStatus(200);

        // Product chuyển sang approved
        $product->refresh();
        $this->assertEquals('approved', $product->status);
    }

    /* ---- 14. Non-admin không được truy cập ---- */
    public function test_non_admin_cannot_access_support_tickets(): void
    {
        $this->actingAs($this->seller)
            ->get(route('admin.support-tickets.index'))
            ->assertStatus(403);
    }
}
