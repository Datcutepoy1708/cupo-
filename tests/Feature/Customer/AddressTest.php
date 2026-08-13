<?php

namespace Tests\Feature\Customer;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_address_and_first_address_is_automatically_default(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('addresses.store'), [
            'recipient_name' => 'Nguyễn Văn A',
            'recipient_phone' => '0987654321',
            'province' => 'TP. Hồ Chí Minh',
            'district' => 'Quận 1',
            'ward' => 'Phường Bến Nghé',
            'address_detail' => '123 Lê Lợi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'user_id' => $customer->id,
            'recipient_name' => 'Nguyễn Văn A',
            'address_detail' => '123 Lê Lợi',
            'is_default' => 1,
        ]);
    }

    public function test_customer_can_set_address_as_default(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $addr1 = Address::create([
            'user_id' => $customer->id,
            'recipient_name' => 'Nguyễn Văn A',
            'recipient_phone' => '0987654321',
            'province' => 'HCM',
            'district' => 'Q1',
            'ward' => 'Ward 1',
            'address_detail' => 'Detail 1',
            'is_default' => true,
        ]);

        $addr2 = Address::create([
            'user_id' => $customer->id,
            'recipient_name' => 'Nguyễn Văn A',
            'recipient_phone' => '0987654321',
            'province' => 'HCM',
            'district' => 'Q2',
            'ward' => 'Ward 2',
            'address_detail' => 'Detail 2',
            'is_default' => false,
        ]);

        $response = $this->actingAs($customer)->patch(route('addresses.set-default', $addr2));

        $response->assertRedirect();
        $this->assertFalse((bool) $addr1->fresh()->is_default);
        $this->assertTrue((bool) $addr2->fresh()->is_default);
    }

    public function test_customer_cannot_delete_or_modify_another_customer_address(): void
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);

        $address = Address::create([
            'user_id' => $customer1->id,
            'recipient_name' => 'Nguyễn Văn A',
            'recipient_phone' => '0987654321',
            'province' => 'HCM',
            'district' => 'Q1',
            'ward' => 'Ward 1',
            'address_detail' => 'Detail 1',
            'is_default' => true,
        ]);

        $this->actingAs($customer2)
            ->delete(route('addresses.destroy', $address))
            ->assertStatus(403);

        $this->actingAs($customer2)
            ->patch(route('addresses.set-default', $address))
            ->assertStatus(403);
    }
}
