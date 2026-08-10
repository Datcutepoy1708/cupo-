<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Định nghĩa cây danh mục con đầy đủ cho tất cả danh mục gốc
        $subCategories = [
            // Laptop (ID 3)
            'Laptop' => [
                'Gaming Laptop',
                'Ultrabook & Văn phòng',
                'Macbook (Apple)',
                'Laptop Đồ họa & Kỹ thuật',
            ],
            // Điện tử & Công nghệ (ID 7)
            'Điện tử & Công nghệ' => [
                'Tai nghe & Loa Bluetooth',
                'Bàn phím & Chuột Gaming',
                'Phụ kiện Máy tính & Cáp sạc',
                'Thẻ nhớ & Ổ cứng di động',
                'Camera & Thiết bị ghi hình',
            ],
            // Điện thoại thông minh (ID 8)
            'Điện thoại thông minh' => [
                'iPhone (Apple)',
                'Smartphone Samsung',
                'Smartphone Xiaomi & Poco',
                'Cáp sạc & Sạc dự phòng',
                'Ốp lưng & Kính cường lực',
            ],
            // Đồng hồ (ID 10)
            'Đồng hồ' => [
                'Đồng hồ Nam cao cấp',
                'Đồng hồ Nữ thời trang',
                'Đồng hồ Thông minh (Smartwatch)',
                'Phụ kiện & Dây đồng hồ',
            ],
            // Sức Khỏe (ID 11)
            'Sức Khỏe' => [
                'Thực phẩm chức năng & Vitamin',
                'Khẩu trang & Thiết bị y tế',
                'Dụng cụ Chăm sóc cá nhân',
                'Cân sức khỏe & Đo huyết áp',
            ],
            // Voucher & Dịch vụ (ID 12)
            'Voucher & Dịch vụ' => [
                'E-Voucher Mua sắm & Ăn uống',
                'Voucher Du lịch & Khách sạn',
                'Thẻ nạp Điện thoại & Data 4G',
            ],
            // Thời trang nam (ID 13)
            'Thời trang nam' => [
                'Áo thun & Áo Polo Nam',
                'Áo sơ mi Nam',
                'Quần Jean Nam',
                'Quần Tây Nam',
                'Giày Nam & Sneaker',
            ],
            // Thời trang nữ (ID 14)
            'Thời trang nữ' => [
                'Váy & Đầm Nữ',
                'Áo thun & Áo kiểu Nữ',
                'Chân váy Nữ',
                'Túi xách & Ví Nữ',
                'Đồ lót & Đồ ngủ Nữ',
            ],
            // Nhà cửa & Đời sống (ID 15)
            'Nhà cửa & Đời sống' => [
                'Dụng cụ Bếp & Nấu nướng',
                'Chăn ga gối nệm',
                'Trang trí nhà cửa & Cây cảnh',
                'Đèn & Thiết bị chiếu sáng',
            ],
            // Nhà sách Online (ID 16)
            'Nhà sách Online' => [
                'Sách Văn học & Tiểu thuyết',
                'Sách Kỹ năng sống & Phát triển bản thân',
                'Sách Kinh tế & Khởi nghiệp',
                'Truyện tranh & Manga',
                'Văn phòng phẩm & Dụng cụ học tập',
            ],
            // Thể thao & Du lịch (ID 21)
            'Thể thao & Du lịch' => [
                'Đồ tập Gym & Yoga',
                'Dụng cụ Cầu lông & Bóng đá',
                'Giày thể thao Nam & Nữ',
                'Đồ dã ngoại & Cắm trại',
            ],
            // Thiết bị gia dụng (ID 22)
            'Thiết bị gia dụng' => [
                'Nồi chiên không dầu',
                'Máy hút bụi & Robot lau nhà',
                'Quạt & Máy làm mát',
                'Máy xay sinh tố & Ép trái cây',
            ],
            // Ô tô & Xe máy & Xe đạp (ID 23)
            'Ô tô & Xe máy & Xe đạp' => [
                'Phụ kiện & Phim cách nhiệt Ô tô',
                'Mũ bảo hiểm & Đồ phượt',
                'Phụ tùng & Dầu nhớt Xe máy',
                'Xe đạp Thể thao & Phụ kiện',
            ],
            // Đồ chơi (ID 24)
            'Đồ chơi' => [
                'Mô hình Lắp ráp & Lego',
                'Đồ chơi Trí tuệ & Boardgame',
                'Gấu bông & Búp bê',
                'Đồ chơi Vận động Ngoài trời',
            ],
        ];

        foreach ($subCategories as $parentName => $children) {
            // Tìm danh mục gốc theo tên
            $parent = Category::whereNull('parent_id')
                ->where('name', 'LIKE', '%'.$parentName.'%')
                ->first();

            if (! $parent) {
                // Nếu chưa có thì tạo mới danh mục gốc
                $parent = Category::create([
                    'name' => $parentName,
                    'slug' => Str::slug($parentName),
                    'status' => true,
                ]);
            }

            foreach ($children as $childName) {
                $slug = Str::slug($childName);

                // Tránh lặp slug
                if (Category::where('slug', $slug)->exists()) {
                    $slug .= '-'.Str::random(3);
                }

                Category::firstOrCreate(
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                    ],
                    [
                        'slug' => $slug,
                        'status' => true,
                    ]
                );
            }
        }
    }
}
