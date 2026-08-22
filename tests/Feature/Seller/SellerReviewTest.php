<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $otherSeller;

    protected User $customer;

    protected Product $product;

    protected Product $otherProduct;

    protected Review $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'shop_name' => 'Shop Của Tôi',
            'slug' => 'shop-cua-toi',
            'address' => '123 Ha Noi',
            'national_id' => '123456789012',
            'status' => 'approved',
        ]);

        $this->otherSeller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        SellerProfile::create([
            'user_id' => $this->otherSeller->id,
            'shop_name' => 'Shop Đối Thủ',
            'slug' => 'shop-doi-thu',
            'address' => '456 Sai Gon',
            'national_id' => '987654321098',
            'status' => 'approved',
        ]);

        $this->customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $this->product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Áo Thun Nam Cao Cấp',
            'slug' => 'ao-thun-nam-cao-cap',
            'sku' => 'SKU-AO-THUN-01',
            'thumbnail' => 'products/ao-thun.jpg',
            'description' => 'Mô tả chi tiết áo thun nam cao cấp',
            'price' => 150000,
            'stock' => 100,
            'status' => 'approved',
        ]);

        $this->otherProduct = Product::create([
            'seller_id' => $this->otherSeller->id,
            'category_id' => $category->id,
            'name' => 'Quần Jean Nam',
            'slug' => 'quan-jean-nam',
            'sku' => 'SKU-QUAN-JEAN-02',
            'thumbnail' => 'products/quan-jean.jpg',
            'description' => 'Mô tả chi tiết quần jean nam',
            'price' => 350000,
            'stock' => 50,
            'status' => 'approved',
        ]);

        $this->review = Review::create([
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'comment' => 'Áo mặc rất đẹp và mát mẻ!',
            'status' => 'approved',
        ]);
    }

    /* ---- 1. Seller xem danh sách đánh giá của shop mình ---- */
    public function test_seller_can_view_own_products_reviews(): void
    {
        $response = $this->actingAs($this->seller)->get(route('seller.reviews.index'));
        $response->assertStatus(200)
            ->assertViewIs('seller.reviews.index')
            ->assertSee('Áo Thun Nam Cao Cấp')
            ->assertSee('Áo mặc rất đẹp và mát mẻ!');
    }

    /* ---- 2. Seller phản hồi đánh giá khách hàng ---- */
    public function test_seller_can_reply_to_review(): void
    {
        $response = $this->actingAs($this->seller)->postJson(
            route('seller.reviews.reply', $this->review),
            ['reply' => 'Shop cảm ơn bạn nhiều nhé!']
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.reply', 'Shop cảm ơn bạn nhiều nhé!');

        $this->assertDatabaseHas('review_replies', [
            'review_id' => $this->review->id,
            'seller_id' => $this->seller->id,
            'reply' => 'Shop cảm ơn bạn nhiều nhé!',
        ]);
    }

    /* ---- 3. Seller không được phản hồi đánh giá của shop khác ---- */
    public function test_seller_cannot_reply_to_other_sellers_product_review(): void
    {
        $otherReview = Review::create([
            'product_id' => $this->otherProduct->id,
            'user_id' => $this->customer->id,
            'rating' => 4,
            'comment' => 'Quần đẹp',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->seller)->postJson(
            route('seller.reviews.reply', $otherReview),
            ['reply' => 'Can thiệp trả lời bất hợp pháp']
        );

        $response->assertStatus(403);
    }

    /* ---- 4. Seller báo cáo vi phạm đánh giá lên Admin ---- */
    public function test_seller_can_report_review(): void
    {
        $badReview = Review::create([
            'product_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'rating' => 1,
            'comment' => 'Đồ lừa đảo',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->seller)->postJson(
            route('seller.reviews.report', $badReview),
            ['report_reason' => 'Đánh giá vu khống sai sự thật phá hoại shop']
        );

        $response->assertStatus(200);

        $badReview->refresh();
        $this->assertTrue($badReview->is_reported);
        $this->assertEquals('pending', $badReview->report_status);
        $this->assertEquals('Đánh giá vu khống sai sự thật phá hoại shop', $badReview->report_reason);
    }
}
