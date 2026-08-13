<?php

namespace Tests\Feature\Admin;

use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponTest extends TestCase
{
    use RefreshDatabase;

    private function createCoupon(array $attributes = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'TESTCODE10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_amount' => 100000.00,
            'max_discount' => 50000.00,
            'usage_limit' => 100,
            'used_count' => 0,
            'starts_at' => Carbon::now()->subDay(),
            'expires_at' => Carbon::now()->addDays(10),
            'status' => true,
            'seller_id' => null,
        ], $attributes));
    }

    public function test_admin_can_view_coupons_blade_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/coupons');

        $response->assertStatus(200)
            ->assertViewIs('admin.coupons.index')
            ->assertViewHas('sellers');
    }

    public function test_admin_can_fetch_coupons_json_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createCoupon();

        $response = $this->actingAs($admin)->getJson('/admin/coupons');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'total_all',
                    'total_active',
                    'total_upcoming',
                    'total_expired',
                    'total_inactive',
                    'total_used',
                ],
            ]);
    }

    public function test_non_admin_cannot_access_coupons(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/admin/coupons');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/coupons', [
            'code' => 'NEWVOUCHER50',
            'type' => 'fixed_amount',
            'value' => 50000,
            'min_order_amount' => 200000,
            'usage_limit' => 50,
            'status' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('coupons', [
            'code' => 'NEWVOUCHER50',
            'type' => 'fixed_amount',
            'value' => 50000,
        ]);
    }

    public function test_coupon_validation_fails_for_percentage_over_100(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/coupons', [
            'code' => 'INVALIDPCT',
            'type' => 'percentage',
            'value' => 150,
            'usage_limit' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }

    public function test_admin_can_show_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coupon = $this->createCoupon();

        $response = $this->actingAs($admin)->getJson("/admin/coupons/{$coupon->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $coupon->id,
                    'code' => 'TESTCODE10',
                ],
            ]);
    }

    public function test_admin_can_update_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coupon = $this->createCoupon();

        $response = $this->actingAs($admin)->putJson("/admin/coupons/{$coupon->id}", [
            'code' => 'UPDATEDCODE',
            'type' => 'fixed_amount',
            'value' => 70000,
            'usage_limit' => 200,
            'status' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'UPDATEDCODE',
            'value' => 70000,
        ]);
    }

    public function test_admin_can_toggle_coupon_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coupon = $this->createCoupon(['status' => true]);

        $response = $this->actingAs($admin)->patchJson("/admin/coupons/{$coupon->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'status' => false]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'status' => false,
        ]);
    }

    public function test_admin_can_delete_coupon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coupon = $this->createCoupon();

        $response = $this->actingAs($admin)->deleteJson("/admin/coupons/{$coupon->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('coupons', [
            'id' => $coupon->id,
        ]);
    }

    public function test_admin_can_bulk_update_and_delete_coupons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $c1 = $this->createCoupon(['code' => 'BULK1', 'status' => true]);
        $c2 = $this->createCoupon(['code' => 'BULK2', 'status' => true]);

        // Bulk deactivate
        $responseStatus = $this->actingAs($admin)->postJson('/admin/coupons/bulk-status', [
            'ids' => [$c1->id, $c2->id],
            'status' => false,
        ]);
        $responseStatus->assertStatus(200);
        $this->assertDatabaseHas('coupons', ['id' => $c1->id, 'status' => false]);
        $this->assertDatabaseHas('coupons', ['id' => $c2->id, 'status' => false]);

        // Bulk delete
        $responseDelete = $this->actingAs($admin)->postJson('/admin/coupons/bulk-delete', [
            'ids' => [$c1->id, $c2->id],
        ]);
        $responseDelete->assertStatus(200);
        $this->assertDatabaseMissing('coupons', ['id' => $c1->id]);
        $this->assertDatabaseMissing('coupons', ['id' => $c2->id]);
    }
}
