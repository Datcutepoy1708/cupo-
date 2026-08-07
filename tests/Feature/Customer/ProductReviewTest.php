<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\SellerOrder;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithSeller(): array
    {
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop Test',
            'slug' => 'shop-test',
            'address' => 'HN',
            'national_id' => '012345678901',
            'status' => 'approved',
        ]);

        $category = Category::firstOrCreate(['slug' => 'thoi-trang'], ['name' => 'Thời trang']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Áo Phông Nam',
            'slug' => 'ao-phong-nam',
            'sku' => 'AO-01',
            'price' => 150000,
            'stock' => 50,
            'thumbnail' => 'https://via.placeholder.com/300',
            'description' => 'Mô tả',
            'status' => 'approved',
        ]);

        return [$seller, $product];
    }

    public function test_customer_cannot_review_product_if_not_purchased(): void
    {
        [$seller, $product] = $this->createProductWithSeller();
        $customer = User::factory()->create(['role' => 'customer']);

        // Khách hàng chưa mua hàng nhưng cố tình gửi Đánh giá
        $response = $this->actingAs($customer)->postJson("/products/{$product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Sản phẩm rất đẹp chất lượng tốt!',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua hàng và đơn hàng đã hoàn thành thành công.');
    }

    public function test_customer_can_review_product_after_successful_purchase(): void
    {
        [$seller, $product] = $this->createProductWithSeller();
        $customer = User::factory()->create(['role' => 'customer']);

        // 1. Giả lập Khách đã mua hàng và Đơn con của Shop đã hoàn thành (completed)
        $masterOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-REV-101',
            'shipping_name' => 'Khách Hàng A',
            'shipping_phone' => '0987654321',
            'shipping_address' => 'Hà Nội',
            'payment_method' => 'cod',
            'total_item_amount' => 150000,
            'grand_total' => 150000,
        ]);

        $sellerOrder = SellerOrder::create([
            'order_id' => $masterOrder->id,
            'seller_id' => $seller->id,
            'sub_total' => 150000,
            'grand_total' => 150000,
            'commission_amount' => 0,
            'status' => 'completed', //  Đơn đã hoàn thành!
        ]);

        OrderItem::create([
            'seller_order_id' => $sellerOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 150000,
            'quantity' => 1,
            'total' => 150000,
        ]);

        // 2. Khách thực hiện Đánh giá 5 sao
        $response = $this->actingAs($customer)->postJson("/products/{$product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Vải dày dặn, mặc vừa vặn tuyệt vời!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Đánh giá sản phẩm thành công! Cảm ơn nhận xét của bạn.');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
        ]);
    }

    private function createPurchasedReview(User $customer): array
    {
        [$seller, $product] = $this->createProductWithSeller();

        $masterOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-REV-'.rand(1000, 9999),
            'shipping_name' => 'Khách Hàng',
            'shipping_phone' => '0987654321',
            'shipping_address' => 'Hà Nội',
            'payment_method' => 'cod',
            'total_item_amount' => 150000,
            'grand_total' => 150000,
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $masterOrder->id,
            'rating' => 3,
            'comment' => 'Đánh giá ban đầu',
            'status' => 'approved',
        ]);

        return [$product, $review];
    }

    public function test_customer_can_update_their_own_review(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$product, $review] = $this->createPurchasedReview($customer);

        $response = $this->actingAs($customer)->putJson("/reviews/{$review->id}", [
            'rating' => 5,
            'comment' => 'Sản phẩm tuyệt vời sau khi dùng thử!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cập nhật đánh giá sản phẩm thành công!');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Sản phẩm tuyệt vời sau khi dùng thử!',
        ]);
    }

    public function test_customer_cannot_update_another_customer_review(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        [$product, $review] = $this->createPurchasedReview($customer1);

        $response = $this->actingAs($customer2)->putJson("/reviews/{$review->id}", [
            'rating' => 1,
            'comment' => 'Cố tình sửa bài người khác',
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_delete_their_own_review(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$product, $review] = $this->createPurchasedReview($customer);

        $response = $this->actingAs($customer)->deleteJson("/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
