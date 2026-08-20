<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with('seller')->take(5)->get();
        $buyer = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);

        if ($products->isEmpty()) {
            return;
        }

        $sampleData = [
            [
                'rating' => 5,
                'comment' => 'Sản phẩm dùng rất êm, đóng gói cẩn thận, shipper giao hàng nhanh chóng!',
                'status' => 'approved',
                'is_reported' => false,
                'report_status' => 'none',
                'reply' => 'Shop Cupo chân thành cảm ơn bạn đã tin tưởng ủng hộ shop nhé!',
            ],
            [
                'rating' => 5,
                'comment' => 'Chất lượng tuyệt vời ngoài mong đợi, đúng như mô tả.',
                'status' => 'approved',
                'is_reported' => false,
                'report_status' => 'none',
                'reply' => null,
            ],
            [
                'rating' => 4,
                'comment' => 'Hàng đẹp nhưng hộp hơi móp nhẹ do vận chuyển, sản phẩm bên trong vẫn nguyên vẹn.',
                'status' => 'approved',
                'is_reported' => false,
                'report_status' => 'none',
                'reply' => 'Shop xin lỗi vì sự bất tiện này, shop sẽ nhắc nhở bưu tá cẩn thận hơn ạ!',
            ],
            [
                'rating' => 1,
                'comment' => 'Shop lừa đảo, hàng dởm đừng ai mua, đồ ăn cắp!',
                'status' => 'approved',
                'is_reported' => true,
                'report_reason' => 'Đánh giá xúc phạm thô tục và vu khống ác ý phá hoại shop',
                'report_status' => 'pending',
                'reply' => null,
            ],
            [
                'rating' => 1,
                'comment' => 'Hàng giả, liên hệ Zalo 0999999999 để mua hàng thật giá rẻ link lừa đảo',
                'status' => 'hidden',
                'is_reported' => true,
                'report_reason' => 'Đánh giá spam link quảng cáo lừa đảo',
                'report_status' => 'resolved',
                'admin_note' => 'Chấp thuận khiếu nại của Shop — Đã ẩn đánh giá spam vi phạm.',
                'reply' => null,
            ],
        ];

        foreach ($products as $index => $product) {
            $data = $sampleData[$index % count($sampleData)];
            $replyText = $data['reply'];
            unset($data['reply']);

            $review = Review::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'user_id' => $buyer->id,
                ],
                array_merge($data, [
                    'order_id' => Order::first()?->id,
                ])
            );

            if ($replyText && $product->seller_id) {
                ReviewReply::updateOrCreate(
                    ['review_id' => $review->id],
                    [
                        'seller_id' => $product->seller_id,
                        'reply' => $replyText,
                    ]
                );
            }
        }
    }
}
