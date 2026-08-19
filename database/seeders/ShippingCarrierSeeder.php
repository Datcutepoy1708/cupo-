<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\ShippingCarrier;
use Illuminate\Database\Seeder;

class ShippingCarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carriers = [
            [
                'name' => 'SPX Express',
                'code' => 'spx',
                'logo' => 'images/carriers/spx.png',
                'base_fee' => 25000.00,
                'estimated_days' => '1 - 3 ngày',
                'tracking_url_template' => 'https://spx.vn/track?bill={tracking_number}',
                'hotline' => '19001221',
                'description' => 'Dịch vụ vận chuyển tiêu chuẩn nhanh chóng, mạng lưới phủ sóng toàn quốc.',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Giao Hàng Nhanh (GHN)',
                'code' => 'ghn',
                'logo' => 'images/carriers/ghn.png',
                'base_fee' => 28000.00,
                'estimated_days' => '1 - 2 ngày',
                'tracking_url_template' => 'https://ghn.vn/blogs/trang-thai-don-hang?code={tracking_number}',
                'hotline' => '1900636677',
                'description' => 'Đối tác giao nhận chuyên nghiệp với tốc độ giao hàng vượt trội.',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Giao Hàng Tiết Kiệm (GHTK)',
                'code' => 'ghtk',
                'logo' => 'images/carriers/ghtk.png',
                'base_fee' => 22000.00,
                'estimated_days' => '2 - 4 ngày',
                'tracking_url_template' => 'https://ghtk.vn/tra-cuu-don-hang?code={tracking_number}',
                'hotline' => '19006092',
                'description' => 'Giải pháp tiết kiệm chi phí tối đa cho các đơn hàng liên tỉnh.',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Viettel Post',
                'code' => 'viettelpost',
                'logo' => 'images/carriers/viettelpost.png',
                'base_fee' => 26000.00,
                'estimated_days' => '2 - 3 ngày',
                'tracking_url_template' => 'https://viettelpost.com.vn/tra-cuu-hanh-trinh-don/?bill={tracking_number}',
                'hotline' => '19008095',
                'description' => 'Mạng lưới bưu cục rộng khắp 63 tỉnh thành, phủ sóng cả vùng sâu vùng xa.',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Hỏa Tốc Cupo Instant (2H)',
                'code' => 'instant',
                'logo' => 'images/carriers/instant.png',
                'base_fee' => 45000.00,
                'estimated_days' => 'Trong 2 giờ',
                'tracking_url_template' => null,
                'hotline' => '19008888',
                'description' => 'Giao hàng siêu tốc trong vòng 2 giờ nội thành bằng đội ngũ tài xế công nghệ.',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($carriers as $carrierData) {
            ShippingCarrier::updateOrCreate(
                ['code' => $carrierData['code']],
                $carrierData
            );
        }

        // Tạo sẵn các mã Voucher Freeship mẫu toàn sàn
        $freeshipCoupons = [
            [
                'seller_id' => null, // Toàn sàn
                'code' => 'FREESHIP_EXTRA',
                'type' => 'free_shipping',
                'value' => 100.00, // 100% Freeship
                'min_order_amount' => 200000.00,
                'max_discount' => 30000.00,
                'usage_limit' => 500,
                'used_count' => 12,
                'starts_at' => now()->subDays(5),
                'expires_at' => now()->addMonths(3),
                'status' => true,
            ],
            [
                'seller_id' => null,
                'code' => 'FREESHIP_MAX',
                'type' => 'free_shipping',
                'value' => 100.00,
                'min_order_amount' => 500000.00,
                'max_discount' => 50000.00,
                'usage_limit' => 200,
                'used_count' => 5,
                'starts_at' => now()->subDays(2),
                'expires_at' => now()->addMonths(2),
                'status' => true,
            ],
        ];

        foreach ($freeshipCoupons as $c) {
            Coupon::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
