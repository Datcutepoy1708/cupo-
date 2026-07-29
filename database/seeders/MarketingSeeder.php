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

        // 1. Coupons
        Coupon::create([
            'seller_id' => null,
            'code' => 'ADMIN10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_amount' => 200000.00,
            'max_discount' => 100000.00,
            'usage_limit' => 100,
            'used_count' => 0,
            'starts_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(30),
            'status' => true,
        ]);

        Coupon::create([
            'seller_id' => $seller1->id,
            'code' => 'TECHNEW50',
            'type' => 'fixed_amount',
            'value' => 50000.00,
            'min_order_amount' => 500000.00,
            'usage_limit' => 50,
            'used_count' => 0,
            'starts_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(15),
            'status' => true,
        ]);

        // 2. Flash Sale
        $flashSale = FlashSale::create([
            'name' => 'Giờ Vàng Giá Sốc 12h - 13h',
            'starts_at' => Carbon::now()->addHours(2),
            'ends_at' => Carbon::now()->addHours(3),
            'status' => true,
        ]);

        $iphone = Product::where('sku', 'IPHONE15PM')->first();
        if ($iphone) {
            FlashSaleProduct::create([
                'flash_sale_id' => $flashSale->id,
                'product_id' => $iphone->id,
                'flash_sale_price' => 27000000.00,
                'quantity_limit' => 3,
                'quantity_sold' => 0,
            ]);
        }
    }
}
