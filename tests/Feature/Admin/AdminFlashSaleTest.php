<?php

namespace Tests\Feature\Admin;

use App\Models\FlashSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFlashSaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_flash_sales_list()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.flash-sales.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.flash-sales.index');
    }

    public function test_admin_can_create_flash_sale_session()
    {
        $data = [
            'name' => 'Flash Sale Cuối Tuần',
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHours(3)->format('Y-m-d H:i:s'),
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.flash-sales.store'), $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('flash_sales', [
            'name' => 'Flash Sale Cuối Tuần',
        ]);
    }

    public function test_admin_cannot_create_flash_sale_with_end_before_start()
    {
        $data = [
            'name' => 'Flash Sale Lỗi',
            'starts_at' => now()->addHours(3)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.flash-sales.store'), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ends_at']);
    }

    public function test_admin_can_toggle_flash_sale_status()
    {
        $flashSale = FlashSale::create([
            'name' => 'Test Flash Sale',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'status' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.flash-sales.toggle', $flashSale));

        $response->assertStatus(200);
        $this->assertDatabaseHas('flash_sales', [
            'id' => $flashSale->id,
            'status' => false,
        ]);
    }

    public function test_unauthorized_user_cannot_access_admin_flash_sales()
    {
        $user = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($user)->get(route('admin.flash-sales.index'));

        $response->assertStatus(403);
    }
}
