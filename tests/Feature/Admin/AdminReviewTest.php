<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $moderator;

    protected User $customer;

    protected User $seller;

    protected Product $product;

    protected Review $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Thử Nghiệm',
            'slug' => 'shop-thu-nghiem',
            'address' => '123 Ha Noi',
            'national_id' => '123456789012',
            'status' => 'approved',
        ]);

        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $this->product = Product::create([
            'seller_id'   => $this->seller->id,
            'category_id' => $category->id,
            'name'        => 'Giày Sneaker',
            'slug'        => 'giay-sneaker',
            'sku'         => 'SKU-SNEAKER-01',
            'thumbnail'   => 'products/sneaker.jpg',
            'description' => 'Mô tả chi tiết giày sneaker',
            'price'       => 500000,
            'stock'       => 20,
            'status'      => 'approved',
        ]);

        $this->review = Review::create([
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'rating' => 1,
            'comment' => 'Shop lừa đảo gửi hàng hỏng link lừa đảo',
            'status' => 'approved',
            'is_reported' => true,
            'report_reason' => 'Đánh giá spam lừa đảo',
            'report_status' => 'pending',
        ]);
    }

    /* ---- 1. Admin & Moderator xem được danh sách đánh giá toàn sàn ---- */
    public function test_admin_can_view_all_reviews_and_stats(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reviews.index'));
        $response->assertStatus(200)
            ->assertViewIs('admin.reviews.index')
            ->assertSee('Kiểm duyệt Đánh giá');

        $jsonResponse = $this->actingAs($this->moderator)->getJson(route('admin.reviews.index'));
        $jsonResponse->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('meta.pending_reports_count', 1);
    }

    /* ---- 2. Admin bật / ẩn đánh giá vi phạm ---- */
    public function test_admin_can_toggle_review_status(): void
    {
        $response = $this->actingAs($this->admin)->patchJson(route('admin.reviews.toggle', $this->review));
        $response->assertStatus(200)
            ->assertJsonPath('status', 'hidden');

        $this->review->refresh();
        $this->assertEquals('hidden', $this->review->status);
    }

    /* ---- 3. Admin chấp thuận khiếu nại báo cáo của Shop (Ẩn đánh giá) ---- */
    public function test_admin_can_approve_review_report_and_hide_review(): void
    {
        $response = $this->actingAs($this->admin)->postJson(
            route('admin.reviews.resolve-report', $this->review),
            [
                'decision' => 'approve_report',
                'admin_note' => 'Đã xác minh đánh giá spam. Chấp thuận gỡ bỏ.',
            ]
        );

        $response->assertStatus(200);

        $this->review->refresh();
        $this->assertEquals('hidden', $this->review->status);
        $this->assertEquals('resolved', $this->review->report_status);
        $this->assertEquals('Đã xác minh đánh giá spam. Chấp thuận gỡ bỏ.', $this->review->admin_note);
    }

    /* ---- 4. Admin bác bỏ khiếu nại của Shop (Giữ nguyên đánh giá) ---- */
    public function test_admin_can_dismiss_review_report_and_keep_review(): void
    {
        $response = $this->actingAs($this->admin)->postJson(
            route('admin.reviews.resolve-report', $this->review),
            [
                'decision' => 'dismiss_report',
                'admin_note' => 'Đánh giá trải nghiệm thực tế hợp lệ. Bác bỏ khiếu nại.',
            ]
        );

        $response->assertStatus(200);

        $this->review->refresh();
        $this->assertEquals('approved', $this->review->status);
        $this->assertEquals('dismissed', $this->review->report_status);
    }

    /* ---- 5. Khách hàng thông thường không được truy cập Admin Reviews ---- */
    public function test_customer_cannot_access_admin_reviews(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.reviews.index'))
            ->assertStatus(403);
    }
}
