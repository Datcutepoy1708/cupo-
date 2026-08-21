<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\GeneralNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    /* ---- 1. Lấy danh sách thông báo và số lượng chưa đọc ---- */
    public function test_user_can_get_notifications_and_unread_count(): void
    {
        $this->user->notify(new GeneralNotification(
            'Đơn hàng mới',
            'Bạn có 1 đơn hàng mới cần đóng gói',
            '/seller/orders',
            'fa-solid fa-bag-shopping',
            'success'
        ));

        $response = $this->actingAs($this->user)->getJson(route('notifications.index'));
        $response->assertStatus(200)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.data.title', 'Đơn hàng mới');

        $countResponse = $this->actingAs($this->user)->getJson(route('notifications.unread-count'));
        $countResponse->assertStatus(200)
            ->assertJsonPath('unread_count', 1);
    }

    /* ---- 2. Đánh dấu 1 thông báo là đã đọc ---- */
    public function test_user_can_mark_single_notification_as_read(): void
    {
        $this->user->notify(new GeneralNotification(
            'Tin nhắn hệ thống',
            'Chào mừng bạn đến với Cupo',
            '/home'
        ));

        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user)->patchJson(route('notifications.read', $notification->id));
        $response->assertStatus(200)
            ->assertJsonPath('unread_count', 0);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /* ---- 3. Đánh dấu tất cả thông báo là đã đọc ---- */
    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->user->notify(new GeneralNotification('Tin 1', 'Nội dung 1'));
        $this->user->notify(new GeneralNotification('Tin 2', 'Nội dung 2'));

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user)->postJson(route('notifications.read-all'));
        $response->assertStatus(200)
            ->assertJsonPath('unread_count', 0);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    /* ---- 4. Admin nhận được thông báo khi có Shop mới đăng ký ---- */
    public function test_admin_receives_notification_on_seller_registration(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

        $this->actingAs($customer)->post(route('seller.register.store'), [
            'shop_name' => 'Shop Thử Nghiệm Thông Báo',
            'business_type' => 'personal',
            'address' => '123 Ha Noi',
            'national_id' => '123456789012',
            'phone' => '0901234567',
            'date_of_birth' => '01/01/1990',
            'category_ids' => [$category->id],
        ]);

        $this->assertEquals(1, $this->admin->unreadNotifications()->count());
        $this->assertEquals('Gian hàng mới đăng ký', $this->admin->notifications()->first()->data['title']);
    }

    /* ---- 5. Seller nhận được thông báo khi Admin duyệt lệnh rút tiền ---- */
    public function test_seller_receives_notification_on_withdrawal_approval(): void
    {
        SellerProfile::create([
            'user_id' => $this->user->id,
            'shop_name' => 'Shop Của Tôi',
            'slug' => 'shop-cua-toi',
            'address' => '123 Ha Noi',
            'national_id' => '123456789012',
            'balance' => 1000000,
            'status' => 'approved',
        ]);

        $withdrawal = Withdrawal::create([
            'seller_id' => $this->user->id,
            'amount' => 200000,
            'bank_name' => 'Vietcombank',
            'bank_account' => '123456789',
            'bank_owner' => 'TEST SELLER',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)->patchJson(route('admin.withdrawals.approve', $withdrawal));

        $this->assertEquals(1, $this->user->unreadNotifications()->count());
        $this->assertEquals('Lệnh rút tiền thành công', $this->user->notifications()->first()->data['title']);
    }
}
