<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\FlashSaleRegistration;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerFlashSaleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedSeller(string $profileStatus = 'approved'): User
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop '.$seller->name,
            'slug' => 'shop-'.$seller->id,
            'address' => 'Ha Noi',
            'national_id' => '012345678901',
            'status' => $profileStatus,
        ]);

        return $seller;
    }

    private function createOpenFlashSale(): FlashSale
    {
        return FlashSale::create([
            'name' => 'Flash Sale Test',
            'registration_deadline' => now()->addHour(),
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(4),
            'status' => true,
        ]);
    }

    private function createApprovedProduct(User $seller, array $overrides = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'test-cat'], ['name' => 'Test Category']);

        return Product::create(array_merge([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'San pham test',
            'slug' => 'san-pham-test-'.$seller->id.'-'.uniqid(),
            'sku' => 'SKU-TEST-'.strtoupper(uniqid()),
            'description' => 'Mo ta test',
            'thumbnail' => 'placeholder.jpg',
            'price' => 100000,
            'stock' => 50,
            'status' => 'approved',
        ], $overrides));
    }

    // ----------- Seller view open flash sales -----------

    public function test_seller_can_view_open_flash_sales(): void
    {
        $seller = $this->createApprovedSeller();
        $this->createOpenFlashSale();

        $response = $this->actingAs($seller)->getJson(route('seller.flash-sale-registrations.index'));

        $response->assertStatus(200);
    }

    // ----------- Submit registration -----------

    public function test_seller_can_submit_registration_for_own_product(): void
    {
        $seller = $this->createApprovedSeller();
        $sale = $this->createOpenFlashSale();
        $product = $this->createApprovedProduct($seller, ['price' => 100000]);

        $response = $this->actingAs($seller)->postJson(
            route('seller.flash-sale-registrations.store'),
            [
                'flash_sale_id' => $sale->id,
                'product_id' => $product->id,
                'proposed_price' => 80000, // 80% — OK
                'proposed_quantity' => 10,
            ]
        );

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('flash_sale_registrations', [
            'flash_sale_id' => $sale->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
    }

    public function test_seller_cannot_submit_registration_for_another_sellers_product(): void
    {
        $seller1 = $this->createApprovedSeller();
        $seller2 = $this->createApprovedSeller();
        $sale = $this->createOpenFlashSale();
        $product = $this->createApprovedProduct($seller2); // Product cua seller2

        $response = $this->actingAs($seller1)->postJson(
            route('seller.flash-sale-registrations.store'),
            [
                'flash_sale_id' => $sale->id,
                'product_id' => $product->id,
                'proposed_price' => 80000,
                'proposed_quantity' => 10,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['product_id']);
    }

    public function test_seller_cannot_submit_price_above_90_percent_of_regular_price(): void
    {
        $seller = $this->createApprovedSeller();
        $sale = $this->createOpenFlashSale();
        $product = $this->createApprovedProduct($seller, ['price' => 100000]);

        $response = $this->actingAs($seller)->postJson(
            route('seller.flash-sale-registrations.store'),
            [
                'flash_sale_id' => $sale->id,
                'product_id' => $product->id,
                'proposed_price' => 95000, // 95% — QUAT PHEP TOI DA 90%
                'proposed_quantity' => 10,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['proposed_price']);
    }

    public function test_seller_cannot_submit_after_registration_deadline(): void
    {
        $seller = $this->createApprovedSeller();
        $sale = FlashSale::create([
            'name' => 'Flash Sale Qua Han',
            'registration_deadline' => now()->subMinute(), // Da qua han
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(4),
            'status' => true,
        ]);
        $product = $this->createApprovedProduct($seller);

        $response = $this->actingAs($seller)->postJson(
            route('seller.flash-sale-registrations.store'),
            [
                'flash_sale_id' => $sale->id,
                'product_id' => $product->id,
                'proposed_price' => 80000,
                'proposed_quantity' => 10,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['flash_sale_id']);
    }

    public function test_seller_cannot_submit_duplicate_registration_same_product(): void
    {
        $seller = $this->createApprovedSeller();
        $sale = $this->createOpenFlashSale();
        $product = $this->createApprovedProduct($seller, ['price' => 100000]);

        // Lan 1: OK
        FlashSaleRegistration::create([
            'flash_sale_id' => $sale->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'proposed_price' => 80000,
            'proposed_quantity' => 10,
            'status' => 'pending',
        ]);

        // Lan 2: phai bi tu choi
        $response = $this->actingAs($seller)->postJson(
            route('seller.flash-sale-registrations.store'),
            [
                'flash_sale_id' => $sale->id,
                'product_id' => $product->id,
                'proposed_price' => 80000,
                'proposed_quantity' => 5,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['product_id']);
    }

    // ----------- Cancel registration -----------

    public function test_seller_can_cancel_pending_registration_before_deadline(): void
    {
        $seller = $this->createApprovedSeller();
        $sale = $this->createOpenFlashSale();
        $product = $this->createApprovedProduct($seller);

        $reg = FlashSaleRegistration::create([
            'flash_sale_id' => $sale->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'proposed_price' => 80000,
            'proposed_quantity' => 10,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)
            ->deleteJson(route('seller.flash-sale-registrations.destroy', $reg));

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('flash_sale_registrations', ['id' => $reg->id]);
    }

    public function test_seller_cannot_cancel_approved_registration(): void
    {
        $seller = $this->createApprovedSeller();
        $sale = $this->createOpenFlashSale();
        $product = $this->createApprovedProduct($seller);

        $reg = FlashSaleRegistration::create([
            'flash_sale_id' => $sale->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'proposed_price' => 80000,
            'proposed_quantity' => 10,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($seller)
            ->deleteJson(route('seller.flash-sale-registrations.destroy', $reg));

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseHas('flash_sale_registrations', ['id' => $reg->id]);
    }

    public function test_seller_cannot_cancel_another_sellers_registration(): void
    {
        $seller1 = $this->createApprovedSeller();
        $seller2 = $this->createApprovedSeller();
        $sale = $this->createOpenFlashSale();
        $product = $this->createApprovedProduct($seller2);

        $reg = FlashSaleRegistration::create([
            'flash_sale_id' => $sale->id,
            'seller_id' => $seller2->id,
            'product_id' => $product->id,
            'proposed_price' => 80000,
            'proposed_quantity' => 10,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller1)
            ->deleteJson(route('seller.flash-sale-registrations.destroy', $reg));

        $response->assertStatus(403);
    }
}
