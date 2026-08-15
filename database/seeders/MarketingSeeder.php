<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $seller1 = User::where('email', 'seller1@gmail.com')->first();
        $seller2 = User::where('role', 'seller')->where('id', '!=', $seller1?->id)->first();

        // 1. Coupons (Da dang cac loai ma giam gia)
        $coupons = [
            [
                'seller_id' => null,
                'code' => 'CUPOWELCOME',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 150000.00,
                'max_discount' => 50000.00,
                'usage_limit' => 200,
                'used_count' => 45,
                'starts_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addDays(25),
                'status' => true,
            ],
            [
                'seller_id' => null,
                'code' => 'FREESHIP50K',
                'type' => 'fixed_amount',
                'value' => 50000.00,
                'min_order_amount' => 300000.00,
                'max_discount' => null,
                'usage_limit' => 100,
                'used_count' => 18,
                'starts_at' => Carbon::now()->subDays(2),
                'expires_at' => Carbon::now()->addDays(12),
                'status' => true,
            ],
            [
                'seller_id' => $seller1?->id,
                'code' => 'TECHNEW50',
                'type' => 'fixed_amount',
                'value' => 50000.00,
                'min_order_amount' => 500000.00,
                'max_discount' => null,
                'usage_limit' => 50,
                'used_count' => 12,
                'starts_at' => Carbon::now()->subDays(3),
                'expires_at' => Carbon::now()->addDays(15),
                'status' => true,
            ],
            [
                'seller_id' => $seller2?->id ?? $seller1?->id,
                'code' => 'FASHION20',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 250000.00,
                'max_discount' => 80000.00,
                'usage_limit' => 80,
                'used_count' => 0,
                'starts_at' => Carbon::now()->addDays(3),
                'expires_at' => Carbon::now()->addDays(20),
                'status' => true,
            ],
            [
                'seller_id' => null,
                'code' => 'SUMMEREXPIRED',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 100000.00,
                'max_discount' => 30000.00,
                'usage_limit' => 50,
                'used_count' => 50,
                'starts_at' => Carbon::now()->subDays(30),
                'expires_at' => Carbon::now()->subDays(2),
                'status' => true,
            ],
            [
                'seller_id' => null,
                'code' => 'VIPMEMBER100K',
                'type' => 'fixed_amount',
                'value' => 100000.00,
                'min_order_amount' => 1000000.00,
                'max_discount' => null,
                'usage_limit' => 30,
                'used_count' => 5,
                'starts_at' => Carbon::now()->subDays(1),
                'expires_at' => Carbon::now()->addDays(60),
                'status' => false,
            ],
        ];

        foreach ($coupons as $couponData) {
            Coupon::updateOrCreate(
                ['code' => $couponData['code']],
                $couponData
            );
        }

        // 2. Flash Sale
        $flashSale = FlashSale::firstOrCreate(
            ['name' => 'Giờ Vàng Giá Sốc 12h - 13h'],
            [
                'starts_at' => Carbon::now()->addHours(2),
                'ends_at' => Carbon::now()->addHours(3),
                'status' => true,
            ]
        );

        $iphone = Product::where('sku', 'IPHONE15PM')->first();
        if ($iphone && $flashSale) {
            FlashSaleProduct::firstOrCreate(
                [
                    'flash_sale_id' => $flashSale->id,
                    'product_id' => $iphone->id,
                ],
                [
                    'flash_sale_price' => 27000000.00,
                    'quantity_limit' => 3,
                    'quantity_sold' => 0,
                ]
            );
        }
    }
}
