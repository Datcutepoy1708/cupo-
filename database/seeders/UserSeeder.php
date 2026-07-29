<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0987654321',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        // 2. Create Sellers
        $seller1 = User::create([
            'name' => 'John Seller (Tech Store)',
            'email' => 'seller1@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0912345678',
            'role' => 'seller',
            'status' => 'active',
        ]);
        $seller1->assignRole('seller');
        SellerProfile::create([
            'user_id' => $seller1->id,
            'shop_name' => 'Tech Store',
            'slug' => 'tech-store',
            'description' => 'Chuyên cung cấp đồ công nghệ chính hãng',
            'address' => '123 Đường Cầu Giấy, Hà Nội',
            'commission_rate' => 5.00,
            'balance' => 1500000.00,
            'bank_name' => 'Vietcombank',
            'bank_account' => '1012345678',
            'bank_owner' => 'NGUYEN VAN A',
            'status' => 'approved',
        ]);

        $seller2 = User::create([
            'name' => 'Mary Seller (Fashion Hub)',
            'email' => 'seller2@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0923456789',
            'role' => 'seller',
            'status' => 'active',
        ]);
        $seller2->assignRole('seller');
        SellerProfile::create([
            'user_id' => $seller2->id,
            'shop_name' => 'Fashion Hub',
            'slug' => 'fashion-hub',
            'description' => 'Thời trang cao cấp cho giới trẻ',
            'address' => '456 Đường Nguyễn Trãi, TP.HCM',
            'commission_rate' => 8.00,
            'balance' => 0.00,
            'bank_name' => 'Techcombank',
            'bank_account' => '1902345678',
            'bank_owner' => 'TRAN THI B',
            'status' => 'approved',
        ]);

        // 3. Create Customers
        $customer1 = User::create([
            'name' => 'David Buyer',
            'email' => 'customer1@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0934567890',
            'role' => 'customer',
            'status' => 'active',
        ]);
        $customer1->assignRole('customer');
        Address::create([
            'user_id' => $customer1->id,
            'recipient_name' => 'David Buyer',
            'recipient_phone' => '0934567890',
            'address_detail' => 'Số 12 ngõ 80 Chùa Láng',
            'ward' => 'Láng Thượng',
            'district' => 'Đống Đa',
            'province' => 'Hà Nội',
            'is_default' => true,
        ]);

        $customer2 = User::create([
            'name' => 'Emma Watson',
            'email' => 'customer2@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0945678901',
            'role' => 'customer',
            'status' => 'active',
        ]);
        $customer2->assignRole('customer');
        Address::create([
            'user_id' => $customer2->id,
            'recipient_name' => 'Emma Watson',
            'recipient_phone' => '0945678901',
            'address_detail' => '789 Đường Lê Lợi',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'Hồ Chí Minh',
            'is_default' => true,
        ]);
    }
}
