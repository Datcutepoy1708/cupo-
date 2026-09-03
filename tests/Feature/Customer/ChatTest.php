<?php

namespace Tests\Feature\Customer;

use App\Events\ChatMessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_chat_rooms(): void
    {
        $response = $this->getJson(route('chat.rooms.index'));
        $response->assertStatus(401);
    }

    public function test_customer_can_open_chat_room_with_seller(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop Độc Đáo',
            'slug' => 'shop-doc-dao',
            'address' => 'Hà Nội',
            'national_id' => '012345678901',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($customer)
            ->postJson(route('chat.rooms.store'), [
                'seller_id' => $seller->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Shop Độc Đáo');

        $this->assertDatabaseHas('chat_rooms', [
            'buyer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);
    }

    public function test_user_cannot_chat_with_themselves(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Shop Test',
            'slug' => 'shop-test',
            'address' => 'Hà Nội',
            'national_id' => '012345678901',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($seller)
            ->postJson(route('chat.rooms.store'), [
                'seller_id' => $seller->id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Bạn không thể chat với chính mình.']);
    }

    public function test_blocked_user_cannot_open_room(): void
    {
        $blockedCustomer = User::factory()->create([
            'role' => 'customer',
            'status' => 'blocked',
        ]);
        $seller = User::factory()->create(['role' => 'seller']);

        $response = $this->actingAs($blockedCustomer)
            ->postJson(route('chat.rooms.store'), [
                'seller_id' => $seller->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_participant_can_send_message_and_event_is_dispatched(): void
    {
        Event::fake([ChatMessageSent::class]);

        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $room = ChatRoom::create([
            'buyer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        $response = $this->actingAs($customer)
            ->postJson(route('chat.rooms.messages.store', $room->id), [
                'message' => 'Xin chào shop, sản phẩm này còn hàng không?',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.message', 'Xin chào shop, sản phẩm này còn hàng không?')
            ->assertJsonPath('data.is_mine', true);

        $this->assertDatabaseHas('chat_messages', [
            'chat_room_id' => $room->id,
            'sender_id' => $customer->id,
            'message' => 'Xin chào shop, sản phẩm này còn hàng không?',
        ]);

        Event::assertDispatched(ChatMessageSent::class, function ($event) use ($room, $customer) {
            return $event->message->chat_room_id === $room->id
                && $event->message->sender_id === $customer->id;
        });
    }

    public function test_non_participant_cannot_access_or_send_messages(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $intruder = User::factory()->create(['role' => 'customer']);

        $room = ChatRoom::create([
            'buyer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        // Attempt view
        $viewResponse = $this->actingAs($intruder)
            ->getJson(route('chat.rooms.messages', $room->id));
        $viewResponse->assertStatus(403);

        // Attempt send
        $sendResponse = $this->actingAs($intruder)
            ->postJson(route('chat.rooms.messages.store', $room->id), [
                'message' => 'Tin nhắn spam',
            ]);
        $sendResponse->assertStatus(403);
    }

    public function test_messages_are_marked_as_read_when_other_party_views_them(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $room = ChatRoom::create([
            'buyer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'sender_id' => $customer->id,
            'message' => 'Sản phẩm có sẵn không shop?',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->assertFalse($message->fresh()->is_read);

        // Seller views messages
        $response = $this->actingAs($seller)
            ->getJson(route('chat.rooms.messages', $room->id));

        $response->assertOk();
        $this->assertTrue($message->fresh()->is_read);
    }

    public function test_user_can_list_rooms_with_unread_counts(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Gian Hàng A',
            'slug' => 'gian-hang-a',
            'address' => 'Đà Nẵng',
            'national_id' => '987654321098',
            'status' => 'approved',
        ]);

        $room = ChatRoom::create([
            'buyer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        // Seller sends a message to customer
        ChatMessage::create([
            'chat_room_id' => $room->id,
            'sender_id' => $seller->id,
            'message' => 'Chào bạn, shop còn hàng nhé!',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($customer)
            ->getJson(route('chat.rooms.index'));

        $response->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.id', $room->id)
            ->assertJsonPath('data.0.name', 'Gian Hàng A')
            ->assertJsonPath('data.0.other_role', 'seller')
            ->assertJsonPath('data.0.other_role_label', 'Gian hàng')
            ->assertJsonPath('data.0.unread_count', 1);
    }

    public function test_room_distinguishes_between_buyer_and_seller_roles(): void
    {
        // User A is customer who ALSO has a shop
        $buyer = User::factory()->create(['name' => 'Nguyễn Văn Mua', 'role' => 'seller']);
        SellerProfile::create([
            'user_id' => $buyer->id,
            'shop_name' => 'Shop Bán Phụ Kiện',
            'slug' => 'shop-ban-phu-kien',
            'address' => 'Hà Nội',
            'national_id' => '111122223333',
            'status' => 'approved',
        ]);

        // User B is seller
        $seller = User::factory()->create(['name' => 'Trần Thị Bán', 'role' => 'seller']);
        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Siêu Thị Điện Máy',
            'slug' => 'sieu-thi-dien-may',
            'address' => 'TP.HCM',
            'national_id' => '444455556666',
            'status' => 'approved',
        ]);

        // Buyer buys from Seller
        $room = ChatRoom::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        // When Buyer views: other party must be the Shop ("Siêu Thị Điện Máy") with role seller
        $buyerResp = $this->actingAs($buyer)->getJson(route('chat.rooms.index'));
        $buyerResp->assertOk()
            ->assertJsonPath('data.0.name', 'Siêu Thị Điện Máy')
            ->assertJsonPath('data.0.other_role', 'seller')
            ->assertJsonPath('data.0.other_role_label', 'Gian hàng')
            ->assertJsonPath('data.0.my_role', 'buyer');

        // When Seller views: other party must be the customer personal name ("Nguyễn Văn Mua"), NOT customer's shop!
        $sellerResp = $this->actingAs($seller)->getJson(route('chat.rooms.index'));
        $sellerResp->assertOk()
            ->assertJsonPath('data.0.name', 'Nguyễn Văn Mua')
            ->assertJsonPath('data.0.other_role', 'buyer')
            ->assertJsonPath('data.0.other_role_label', 'Khách mua')
            ->assertJsonPath('data.0.my_role', 'seller');
    }
}
