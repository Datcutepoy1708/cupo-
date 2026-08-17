<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerOrder;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Thêm khách hàng mẫu
        $customers = [
            [
                'name' => 'Nguyễn Văn Hùng',
                'email' => 'hung.nv@gmail.com',
                'phone' => '0912334455',
                'date_of_birth' => '1995-04-12',
                'status' => 'active',
                'address' => 'Số 45 Lê Duẩn, P. Bến Nghé, Quận 1, TP. Hồ Chí Minh',
            ],
            [
                'name' => 'Trần Thị Mai',
                'email' => 'mai.tran@gmail.com',
                'phone' => '0988776655',
                'date_of_birth' => '1998-09-20',
                'status' => 'active',
                'address' => 'Số 102 Hoàng Hoa Thám, P. Liễu Giai, Ba Đình, Hà Nội',
            ],
            [
                'name' => 'Lê Hoàng Nam',
                'email' => 'nam.le@gmail.com',
                'phone' => '0933445566',
                'date_of_birth' => '1992-11-05',
                'status' => 'active',
                'address' => 'Số 88 Nguyễn Văn Linh, P. Nam Dương, Hải Châu, Đà Nẵng',
            ],
            [
                'name' => 'Phạm Quỳnh Nga',
                'email' => 'nga.pq@gmail.com',
                'phone' => '0977112233',
                'date_of_birth' => '2000-02-14',
                'status' => 'blocked',
                'address' => 'Tòa S2.05 Vinhomes Ocean Park, Gia Lâm, Hà Nội',
            ],
        ];

        $createdCustomers = [];
        foreach ($customers as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'password' => Hash::make('password'),
                    'phone' => $c['phone'],
                    'date_of_birth' => $c['date_of_birth'],
                    'role' => 'customer',
                    'status' => $c['status'],
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole('customer')) {
                $user->assignRole('customer');
            }

            Address::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'recipient_name' => $c['name'],
                    'recipient_phone' => $c['phone'],
                    'address_detail' => $c['address'],
                    'ward' => 'Phường 1',
                    'district' => 'Quận Trung Tâm',
                    'province' => 'Việt Nam',
                    'is_default' => true,
                ]
            );

            $createdCustomers[] = $user;
        }

        // 2. Lấy danh sách Seller và Product có sẵn
        $sellers = User::where('role', 'seller')->with('sellerProfile')->get();
        if ($sellers->isEmpty()) {
            $this->command->info('Không tìm thấy seller, vui lòng chạy UserSeeder trước.');

            return;
        }

        $products = Product::where('status', 'approved')->get();

        // 3. Tạo các đơn hàng mẫu
        $paymentMethods = ['cod', 'vnpay', 'momo'];
        $paymentStatuses = ['paid', 'paid', 'pending', 'paid'];
        $orderStatuses = ['completed', 'shipping', 'confirmed', 'pending'];

        $createdSellerOrders = [];

        foreach ($createdCustomers as $index => $customer) {
            $seller = $sellers[$index % $sellers->count()];
            $sellerProduct = $products->where('seller_id', $seller->id)->first() ?? $products->first();

            $price = $sellerProduct ? $sellerProduct->price : 250000;
            $qty = rand(1, 3);
            $itemTotal = $price * $qty;
            $shippingFee = 30000;
            $grandTotal = $itemTotal + $shippingFee;
            $commission = round($grandTotal * 0.05);

            $payMethod = $paymentMethods[$index % count($paymentMethods)];
            $payStatus = $paymentStatuses[$index % count($paymentStatuses)];
            $orderStatus = $orderStatuses[$index % count($orderStatuses)];

            $order = Order::create([
                'order_number' => 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(5)),
                'user_id' => $customer->id,
                'total_item_amount' => $itemTotal,
                'total_shipping_fee' => $shippingFee,
                'total_discount' => 0,
                'grand_total' => $grandTotal,
                'payment_method' => $payMethod,
                'payment_status' => $payStatus,
                'shipping_name' => $customer->name,
                'shipping_phone' => $customer->phone ?? '0901234567',
                'shipping_address' => 'Địa chỉ nhận hàng '.$customer->name,
                'notes' => 'Giao hàng giờ hành chính',
                'created_at' => now()->subDays(rand(1, 15))->subHours(rand(1, 23)),
            ]);

            $sellerOrder = SellerOrder::create([
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'sub_total' => $itemTotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0,
                'grand_total' => $grandTotal,
                'commission_amount' => $commission,
                'status' => $orderStatus,
                'tracking_number' => 'VNPOST'.rand(100000, 999999),
                'created_at' => $order->created_at,
            ]);

            if ($sellerProduct) {
                OrderItem::create([
                    'seller_order_id' => $sellerOrder->id,
                    'product_id' => $sellerProduct->id,
                    'product_name' => $sellerProduct->name,
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $itemTotal,
                ]);
            }

            $createdSellerOrders[] = $sellerOrder;
        }

        // 4. Tạo các Tranh chấp / Khiếu nại mẫu (Disputes)
        if (count($createdSellerOrders) >= 4) {
            // Dispute 1: Pending (Chờ xử lý)
            Dispute::firstOrCreate(
                ['seller_order_id' => $createdSellerOrders[0]->id],
                [
                    'buyer_id' => $createdCustomers[0]->id,
                    'reason' => 'Sản phẩm nhận được bị nứt vỡ mặt lưng kính, đóng gói sơ sài không có chống sốc.',
                    'evidence_images' => null,
                    'status' => 'pending',
                    'created_at' => now()->subDays(2),
                ]
            );

            // Dispute 2: In Progress (Đang xử lý)
            Dispute::firstOrCreate(
                ['seller_order_id' => $createdSellerOrders[1]->id],
                [
                    'buyer_id' => $createdCustomers[1]->id,
                    'reason' => 'Giao sai màu sắc và không đúng với phiên bản mô tả trên gian hàng.',
                    'evidence_images' => null,
                    'status' => 'in_progress',
                    'created_at' => now()->subDays(4),
                ]
            );

            // Dispute 3: Refunded (Đã hoàn tiền)
            Dispute::firstOrCreate(
                ['seller_order_id' => $createdSellerOrders[2]->id],
                [
                    'buyer_id' => $createdCustomers[2]->id,
                    'reason' => 'Hàng lỗi nguồn không khởi động được, đã liên hệ shop nhưng không phản hồi.',
                    'evidence_images' => null,
                    'status' => 'refunded',
                    'admin_decision' => 'Đã xác minh lỗi sản phẩm qua video gửi từ người mua. Chấp thuận hoàn tiền 100%.',
                    'created_at' => now()->subDays(6),
                ]
            );

            // Dispute 4: Rejected (Đã từ chối)
            Dispute::firstOrCreate(
                ['seller_order_id' => $createdSellerOrders[3]->id],
                [
                    'buyer_id' => $createdCustomers[3]->id,
                    'reason' => 'Không ưng ý sau 10 ngày sử dụng muốn trả hàng.',
                    'evidence_images' => null,
                    'status' => 'rejected',
                    'admin_decision' => 'Từ chối khiếu nại do quá hạn 7 ngày đổi trả theo chính sách sàn và sản phẩm không có lỗi từ NSX.',
                    'created_at' => now()->subDays(8),
                ]
            );
        }

        // 5. Tạo Yêu cầu rút tiền mẫu (Withdrawals)
        if ($sellers->isNotEmpty()) {
            $seller1 = $sellers[0];
            $seller2 = $sellers->count() > 1 ? $sellers[1] : $seller1;

            Withdrawal::firstOrCreate(
                [
                    'seller_id' => $seller1->id,
                    'amount' => 500000.00,
                    'bank_account' => '1012345678',
                ],
                [
                    'bank_name' => 'Vietcombank',
                    'bank_owner' => $seller1->name,
                    'status' => 'pending',
                    'created_at' => now()->subHours(5),
                ]
            );

            Withdrawal::firstOrCreate(
                [
                    'seller_id' => $seller2->id,
                    'amount' => 1200000.00,
                    'bank_account' => '1902345678',
                ],
                [
                    'bank_name' => 'Techcombank',
                    'bank_owner' => $seller2->name,
                    'status' => 'approved',
                    'admin_note' => 'Đã chuyển khoản thành công qua Internet Banking.',
                    'created_at' => now()->subDays(3),
                ]
            );

            Withdrawal::firstOrCreate(
                [
                    'seller_id' => $seller1->id,
                    'amount' => 2000000.00,
                    'bank_account' => '9999999999',
                ],
                [
                    'bank_name' => 'MB Bank',
                    'bank_owner' => $seller1->name,
                    'status' => 'rejected',
                    'admin_note' => 'Số tài khoản ngân hàng không trùng khớp với tên chủ gian hàng KYC.',
                    'created_at' => now()->subDays(5),
                ]
            );
        }

        $this->command->info('Đã tạo thành công dữ liệu mẫu cho Khách hàng, Đơn hàng, Tranh chấp khiếu nại và Rút tiền!');
    }
}
