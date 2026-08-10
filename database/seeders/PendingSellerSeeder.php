<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PendingSellerSeeder extends Seeder
{
    public function run(): void
    {
        $pendingSellers = [
            [
                'name' => 'Nguyễn Văn Tuấn',
                'email' => 'tuan.anker@gmail.com',
                'phone' => '0981112233',
                'shop_name' => 'Anker Official Store',
                'business_type' => 'company',
                'description' => 'Chuyên phụ kiện cáp sạc, tai nghe, pin dự phòng Anker chính hãng bảo hành 18 tháng.',
                'address' => 'Tầng 5, Tòa nhà HL Tower, 82 Duy Tân, Cầu Giấy, Hà Nội',
                'bank_name' => 'Vietcombank',
                'bank_account' => '0011004455667',
                'bank_owner' => 'CONG TY TNHH ANKER VIETNAM',
                'category_kw' => 'Điện thoại',
            ],
            [
                'name' => 'Trần Thị Thu Hà',
                'email' => 'ha.giadungxanh@gmail.com',
                'phone' => '0972223344',
                'shop_name' => 'Gia Dụng Xanh Việt Nam',
                'business_type' => 'company',
                'description' => 'Cung cấp thiết bị nhà bếp thông minh, nồi chiên không dầu, robot lau nhà giá tốt nhất thị trường.',
                'address' => '45/12 Võ Văn Tần, Phường 6, Quận 3, TP. Hồ Chí Minh',
                'bank_name' => 'Techcombank',
                'bank_account' => '190333444555',
                'bank_owner' => 'CONG TY TNHH GIA DUNG XANH',
                'category_kw' => 'Gia dụng',
            ],
            [
                'name' => 'Lê Hoàng Nam',
                'email' => 'nam.genzfashion@gmail.com',
                'phone' => '0963334455',
                'shop_name' => 'GenZ Fashion Studio',
                'business_type' => 'personal',
                'description' => 'Thời trang phong cách Streetwear, Áo thun Oversize, Quần Jean unisex xu hướng mới nhất.',
                'address' => '128 Điện Biên Phủ, Quận Thanh Khê, Đà Nẵng',
                'bank_name' => 'MB Bank',
                'bank_account' => '9990111222333',
                'bank_owner' => 'LE HOANG NAM',
                'category_kw' => 'Thời trang nam',
            ],
            [
                'name' => 'Phạm Minh Vũ',
                'email' => 'vu.minhtech@gmail.com',
                'phone' => '0954445566',
                'shop_name' => 'Minh Vũ Tech World',
                'business_type' => 'company',
                'description' => 'Chuyên cung cấp Laptop Gaming, PC Đồng bộ và linh kiện máy tính chính hãng Asus, Dell, MSI.',
                'address' => '244 Lê Thanh Nghị, Hai Bà Trưng, Hà Nội',
                'bank_name' => 'BIDV',
                'bank_account' => '2151000123456',
                'bank_owner' => 'CONG TY TNHH CONG NGHE MINH VU',
                'category_kw' => 'Laptop',
            ],
            [
                'name' => 'Đặng Ánh Hồng',
                'email' => 'hong.aurabeauty@gmail.com',
                'phone' => '0945556677',
                'shop_name' => 'Aura Beauty Official',
                'business_type' => 'personal',
                'description' => 'Mỹ phẩm trang điểm và chăm sóc da nhập khẩu Hàn Quốc, Nhật Bản 100% auth.',
                'address' => '88 Nguyễn Trãi, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh',
                'bank_name' => 'VPBank',
                'bank_account' => '1567890123',
                'bank_owner' => 'DANG ANH HONG',
                'category_kw' => 'Sức Khỏe',
            ],
            [
                'name' => 'Vũ Thị Ngọc Bích',
                'email' => 'bich.babyland@gmail.com',
                'phone' => '0936667788',
                'shop_name' => 'BabyLand - Đồ Chơi & Mẹ Bầu',
                'business_type' => 'personal',
                'description' => 'Thế giới đồ chơi trí tuệ, mô hình lắp ráp Lego và sản phẩm chăm sóc sức khỏe cho mẹ & bé.',
                'address' => '15 Lạch Tray, Ngô Quyền, Hải Phòng',
                'bank_name' => 'VietinBank',
                'bank_account' => '108889990001',
                'bank_owner' => 'VU THI NGOC BICH',
                'category_kw' => 'Đồ chơi',
            ],
            [
                'name' => 'Trịnh Quốc Thuận',
                'email' => 'thuan.watch@gmail.com',
                'phone' => '0927778899',
                'shop_name' => 'Thuận Phát Luxury Watch',
                'business_type' => 'company',
                'description' => 'Đại lý phân phối chính thức đồng hồ Casio, Orient, Seiko, Tissot bảo hành 5 năm.',
                'address' => '321 Đồng Khởi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                'bank_name' => 'ACB',
                'bank_account' => '88889999123',
                'bank_owner' => 'CONG TY THUAN PHAT LUXURY',
                'category_kw' => 'Đồng hồ',
            ],
            [
                'name' => 'Ngô Phương Anh',
                'email' => 'anh.trithuctx@gmail.com',
                'phone' => '0918889900',
                'shop_name' => 'Nhà Sách Tri Thức Trẻ',
                'business_type' => 'personal',
                'description' => 'Sách văn học, kỹ năng sống, sách khởi nghiệp kinh doanh và dụng cụ văn phòng phẩm.',
                'address' => '67 Đinh Tiên Hoàng, Hoàn Kiếm, Hà Nội',
                'bank_name' => 'Agribank',
                'bank_account' => '1500205123456',
                'bank_owner' => 'NGO PHUONG ANH',
                'category_kw' => 'Nhà sách',
            ],
        ];

        foreach ($pendingSellers as $item) {
            // Kiểm tra tránh lặp email
            $user = User::where('email', $item['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'phone' => $item['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'seller',
                    'status' => 'active',
                ]);

                // Nếu có dùng Spatie roles
                if (method_exists($user, 'assignRole')) {
                    try {
                        $user->assignRole('seller');
                    } catch (\Throwable $e) {
                        // ignore nếu role chưa seed
                    }
                }
            }

            // Tạo Seller Profile với status PENDING
            $slug = Str::slug($item['shop_name']);
            if (SellerProfile::where('slug', $slug)->exists()) {
                $slug .= '-' . Str::random(3);
            }

            $profile = SellerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'shop_name' => $item['shop_name'],
                    'business_type' => $item['business_type'],
                    'slug' => $slug,
                    'description' => $item['description'],
                    'address' => $item['address'],
                    'commission_rate' => 5.00,
                    'balance' => 0.00,
                    'bank_name' => $item['bank_name'],
                    'bank_account' => $item['bank_account'],
                    'bank_owner' => $item['bank_owner'],
                    'status' => 'pending', // ★ TRẠNG THÁI PENDING NHƯ YÊU CẦU ★
                ]
            );

            // Gán danh mục tương ứng nếu có
            if (!empty($item['category_kw'])) {
                $cats = Category::where('name', 'LIKE', '%' . $item['category_kw'] . '%')->pluck('id');
                if ($cats->isNotEmpty()) {
                    $profile->categories()->sync($cats);
                }
            }
        }
    }
}
