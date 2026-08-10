<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate / clear existing banners
        Banner::query()->delete();

        $banners = [
            // 1. Slide chính Trang chủ (homepage_hero)
            [
                'title' => 'Siêu Sale Mùa Hè - Giảm tới 50% Tất Cả Ngành Hàng',
                'image_path' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1400&q=80',
                'link_url' => '/promotions',
                'position' => 'homepage_hero',
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(30),
            ],
            [
                'title' => 'Công Nghệ Đột Phá - Điện Thoại & Laptop Chính Hãng Trả Góp 0%',
                'image_path' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1400&q=80',
                'link_url' => '/categories/laptop',
                'position' => 'homepage_hero',
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(60),
            ],
            [
                'title' => 'Bộ Sưu Tập Thời Trang GenZ 2026 - Mua 1 Tặng 1',
                'image_path' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1400&q=80',
                'link_url' => '/categories/thoi-trang-nam',
                'position' => 'homepage_hero',
                'sort_order' => 3,
                'is_active' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(45),
            ],

            // 2. Giữa Trang chủ (homepage_mid)
            [
                'title' => 'Tuần Lễ Gia Dụng Thông Minh - Ưu Đãi Độc Quyền Quạt & Máy Hút Bụi',
                'image_path' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=1200&q=80',
                'link_url' => '/categories/thiet-bi-gia-dung',
                'position' => 'homepage_mid',
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(20),
            ],
            [
                'title' => 'Sức Khỏe & Sắc Đẹp - Mỹ Phẩm Chính Hãng Deal Sốc Mỗi Ngày',
                'image_path' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1200&q=80',
                'link_url' => '/categories/suc-khoe',
                'position' => 'homepage_mid',
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(30),
            ],

            // 3. Đầu Trang Danh mục (category_top)
            [
                'title' => 'Khám Phá Hàng Ngàn Sản Phẩm Theo Danh Mục Yêu Thích',
                'image_path' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=1200&q=80',
                'link_url' => '/categories',
                'position' => 'category_top',
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(90),
            ],
            [
                'title' => 'Thiết Bị Điện Tử & Phụ Kiện Giá Tốt Nhất Tuần',
                'image_path' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1200&q=80',
                'link_url' => '/categories/dien-tu-cong-nghe',
                'position' => 'category_top',
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(60),
            ],

            // 4. Thanh bên (sidebar)
            [
                'title' => 'Voucher 100K Cho Đơn Hàng Đầu Tiên',
                'image_path' => 'https://images.unsplash.com/photo-1607083206869-4c7672e72a8a?auto=format&fit=crop&w=600&q=80',
                'link_url' => '/promotions',
                'position' => 'sidebar',
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(60),
            ],
            [
                'title' => 'Freeship Extra Cho Gian Hàng Chính Hãng',
                'image_path' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=600&q=80',
                'link_url' => '/help',
                'position' => 'sidebar',
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(60),
            ],
        ];

        foreach ($banners as $bannerData) {
            Banner::create($bannerData);
        }

        $this->command->info("Đã seed thành công " . count($banners) . " Banner cho cả 4 vị trí!");
    }
}
