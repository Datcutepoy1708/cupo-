<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\FlashSaleRegistration;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use App\Notifications\FlashSaleRegistrationOpenNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminFlashSaleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function createApprovedSeller(): User
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'approved',
        ]);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop '.$seller->name,
            'slug' => 'shop-'.$seller->id,
            'address' => 'Ha Noi',
            'national_id' => '012345678901',
            'status' => 'approved',
        ]);

        return $seller;
    }

    private function createFlashSaleWithRegistration(): array
    {
        $sale = FlashSale::create([
            'name' => 'Flash Sale Test',
            'registration_deadline' => now()->addHour(),
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(4),
            'status' => true,
        ]);

        $seller = $this->createApprovedSeller();
        $category = Category::firstOrCreate(['slug' => 'test-cat'], ['name' => 'Test Category']);
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'San pham test',
            'slug' => 'sp-test-'.uniqid(),
            'sku' => 'SKU-TEST-'.strtoupper(uniqid()),
            'description' => 'Mo ta',
            'thumbnail' => 'placeholder.jpg',
            'price' => 100000,
            'stock' => 50,
            'status' => 'approved',
        ]);

        $reg = FlashSaleRegistration::create([
            'flash_sale_id' => $sale->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'proposed_price' => 80000,
            'proposed_quantity' => 10,
            'status' => 'pending',
        ]);

        return [$sale, $reg, $product];
    }

    // ----------- View registrations -----------

    public function test_admin_can_view_registrations_for_a_flash_sale(): void
    {
        [$sale] = $this->createFlashSaleWithRegistration();

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.flash-sales.registrations.index', $sale));

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'registrations', 'counts']);
    }

    // ----------- Approve -----------

    public function test_admin_approve_creates_flash_sale_product(): void
    {
        [, $reg] = $this->createFlashSaleWithRegistration();

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.flash-sales.registrations.approve', $reg));

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('flash_sale_registrations', [
            'id' => $reg->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('flash_sale_products', [
            'flash_sale_id' => $reg->flash_sale_id,
            'product_id' => $reg->product_id,
            'flash_sale_price' => 80000,
        ]);
    }

    public function test_admin_approve_fails_if_product_already_in_flash_sale(): void
    {
        [$sale, $reg, $product] = $this->createFlashSaleWithRegistration();

        // Admin da them san pham thu cong truoc
        FlashSaleProduct::create([
            'flash_sale_id' => $sale->id,
            'product_id' => $product->id,
            'flash_sale_price' => 75000,
            'quantity_limit' => 20,
            'quantity_sold' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.flash-sales.registrations.approve', $reg));

        $response->assertStatus(422)->assertJson(['success' => false]);

        // Trang thai dang ky van la pending (chua bi thay doi)
        $this->assertDatabaseHas('flash_sale_registrations', [
            'id' => $reg->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_cannot_approve_non_pending_registration(): void
    {
        [, $reg] = $this->createFlashSaleWithRegistration();
        $reg->update(['status' => 'rejected']);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.flash-sales.registrations.approve', $reg));

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    // ----------- Reject -----------

    public function test_admin_reject_requires_reason(): void
    {
        [, $reg] = $this->createFlashSaleWithRegistration();

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.flash-sales.registrations.reject', $reg), [
                'rejection_reason' => '',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['rejection_reason']);
    }

    public function test_admin_can_reject_registration_with_valid_reason(): void
    {
        [, $reg] = $this->createFlashSaleWithRegistration();

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.flash-sales.registrations.reject', $reg), [
                'rejection_reason' => 'Gia de xuat khong hop ly voi chien dich.',
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('flash_sale_registrations', [
            'id' => $reg->id,
            'status' => 'rejected',
        ]);
    }

    // ----------- Notification -----------

    public function test_sellers_receive_notification_when_flash_sale_registration_opens(): void
    {
        Notification::fake();

        $approvedSeller = $this->createApprovedSeller();

        $this->actingAs($this->admin)->postJson(route('admin.flash-sales.store'), [
            'name' => 'Flash Sale Co Dang Ky',
            'registration_deadline' => now()->addHour()->format('Y-m-d H:i:s'),
            'starts_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHours(4)->format('Y-m-d H:i:s'),
            'status' => 1,
        ]);

        Notification::assertSentTo(
            $approvedSeller,
            FlashSaleRegistrationOpenNotification::class
        );
    }

    public function test_no_notification_when_flash_sale_has_no_registration_deadline(): void
    {
        Notification::fake();

        $this->createApprovedSeller();

        $this->actingAs($this->admin)->postJson(route('admin.flash-sales.store'), [
            'name' => 'Flash Sale Khong Co Dang Ky',
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHours(3)->format('Y-m-d H:i:s'),
            'status' => 1,
        ]);

        Notification::assertNothingSent();
    }
}
