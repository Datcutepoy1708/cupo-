<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    // ----------- View -----------

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
        $response->assertViewHas('settings');
    }

    public function test_admin_can_get_settings_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->assertEquals('Cupo — Sàn Thương Mại Điện Tử', $response->json('data.site_name'));
    }

    // ----------- Update -----------

    public function test_admin_can_update_general_settings(): void
    {
        $data = [
            'site_name' => 'Cupo Mega Mall',
            'site_tagline' => 'Sàn TMĐT Số 1 Việt Nam',
            'contact_phone' => '1800 9999',
            'contact_email' => 'admin@cupo.vn',
            'maintenance_mode' => '1',
            '_tab_maintenance_mode' => '1',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'Cupo Mega Mall',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'maintenance_mode',
            'value' => '1',
        ]);

        // Kiem tra cache da duoc cap nhat
        $this->assertEquals('Cupo Mega Mall', setting('site_name'));
        $this->assertEquals('1', setting('maintenance_mode'));
    }

    public function test_admin_can_upload_site_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('custom_logo.png', 300, 100);

        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'site_name' => 'Cupo Mall',
            'site_logo' => $file,
        ]);

        $response->assertRedirect();

        $logoPath = setting('site_logo');
        $this->assertNotNull($logoPath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_admin_cannot_set_invalid_commission_rate(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'default_commission_rate' => 150, // vuot qua 100%
        ]);

        $response->assertSessionHasErrors(['default_commission_rate']);
    }

    public function test_admin_can_update_footer_settings(): void
    {
        $data = [
            'company_name' => 'Công Ty TNHH Cupo Toàn Cầu',
            'business_license' => 'MST: 0109999999 do Sở KH&ĐT cấp',
            'footer_slogan' => 'Mua sắm thả ga với Cupo',
            'working_hours' => '24/7 hàng ngày',
            'copyright_text' => 'Bản quyền © 2026 Cupo Inc.',
            'bct_registered' => '1',
            '_tab_bct_registered' => '1',
            'dmca_protected' => '1',
            '_tab_dmca_protected' => '1',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), $data);

        $response->assertRedirect();
        $this->assertEquals('Công Ty TNHH Cupo Toàn Cầu', setting('company_name'));
        $this->assertEquals('MST: 0109999999 do Sở KH&ĐT cấp', setting('business_license'));
        $this->assertEquals('24/7 hàng ngày', setting('working_hours'));
        $this->assertEquals('1', setting('bct_registered'));
    }

    // ----------- Helper & Cache -----------

    public function test_setting_helper_retrieves_cached_values(): void
    {
        $siteName = setting('site_name');
        $this->assertNotEmpty($siteName);

        // Kiem tra gia tri cache ton tai
        $this->assertTrue(Cache::has('cupo_settings'));

        // Default fallback
        $nonExistent = setting('non_existent_key', 'default_value');
        $this->assertEquals('default_value', $nonExistent);
    }

    // ----------- Mail Test -----------

    public function test_admin_can_send_test_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->postJson(route('admin.settings.test-mail'), [
            'test_email' => 'receiver@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        Mail::assertSentCount(1);
    }

    // ----------- Authorization -----------

    public function test_super_admin_can_update_settings_and_logs_audit_trail(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super-admin', 'status' => 'active']);

        $response = $this->actingAs($superAdmin)->post(route('admin.settings.update'), [
            'site_name' => 'Cupo Global Mall',
            'default_commission_rate' => 8.5,
        ]);

        $response->assertRedirect();
        $this->assertEquals('Cupo Global Mall', setting('site_name'));
        $this->assertEquals('8.5', setting('default_commission_rate'));

        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'update_settings',
            'module' => 'settings',
        ]);
    }

    public function test_auto_approve_sellers_setting_works_on_registration(): void
    {
        setting(['auto_approve_sellers' => '1']);

        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        $response = $this->actingAs($customer)->post(route('seller.register.store'), [
            'shop_name' => 'Shop Tu Dong Duyet',
            'business_type' => 'personal',
            'address' => '789 Da Nang',
            'national_id' => '012345678901',
            'phone' => '0912345678',
            'date_of_birth' => '15/08/1995',
        ]);

        $response->assertRedirect(route('seller.dashboard'));
        $this->assertDatabaseHas('seller_profiles', [
            'user_id' => $customer->id,
            'shop_name' => 'Shop Tu Dong Duyet',
            'status' => 'approved',
        ]);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $response = $this->actingAs($seller)->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }
}
