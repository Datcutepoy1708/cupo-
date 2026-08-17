<?php

namespace Database\Seeders;

use App\Models\SellerSupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::where('role', 'seller')->get();
        $admin = User::where('role', 'admin')->first();

        if ($sellers->isEmpty()) {
            return;
        }

        $seller1 = $sellers[0];
        $seller2 = $sellers->count() > 1 ? $sellers[1] : $seller1;

        // 1. Ticket Open: Kháng nghị khóa tài khoản / cảnh báo
        SellerSupportTicket::firstOrCreate(
            [
                'seller_id' => $seller1->id,
                'subject' => 'Kháng nghị cảnh báo vi phạm tỷ lệ hủy đơn hàng',
            ],
            [
                'category' => 'account_blocked',
                'message' => 'Kính gửi BQT sàn Cupo, gian hàng chúng tôi nhận được cảnh báo về tỷ lệ hủy đơn cao trong tuần qua. Lý do là do đơn vị vận chuyển khu vực gặp bão lũ không lấy được hàng, không phải lỗi từ phía shop. Kính mong BQT kiểm tra và gỡ cảnh báo giúp shop.',
                'status' => 'open',
                'created_at' => now()->subHours(6),
            ]
        );

        // 2. Ticket In Review: Sự cố rút tiền
        SellerSupportTicket::firstOrCreate(
            [
                'seller_id' => $seller2->id,
                'subject' => 'Yêu cầu kiểm tra lệnh rút tiền mã #WDR-0921 bị chậm',
            ],
            [
                'category' => 'withdrawal_issue',
                'message' => 'Shop đã thực hiện lệnh rút 1,500,000đ từ 3 ngày trước nhưng tài khoản ngân hàng Techcombank vẫn chưa nhận được tiền. Nhờ Admin kiểm tra mã giao dịch giúp shop.',
                'status' => 'in_review',
                'created_at' => now()->subDays(2),
            ]
        );

        // 3. Ticket Resolved: Thắc mắc hoa hồng & phí sàn
        SellerSupportTicket::firstOrCreate(
            [
                'seller_id' => $seller1->id,
                'subject' => 'Thắc mắc về mức phí chiết khấu hoa hồng ngành hàng công nghệ',
            ],
            [
                'category' => 'commission_fee',
                'message' => 'Shop muốn hỏi chi tiết về biểu phí hoa hồng 5% và các chính sách hỗ trợ giảm phí khi đạt doanh thu trên 50 triệu/tháng.',
                'status' => 'resolved',
                'admin_response' => 'Chào shop, chính sách sàn hiện áp dụng 5% cố định cho ngành hàng Điện tử. Nếu shop đạt doanh thu trên 50 triệu/tháng liên tiếp 3 tháng, shop sẽ được gắn huy hiệu Shop Yêu Thích và nhận voucher tài trợ từ sàn nhé!',
                'resolved_by' => $admin?->id,
                'resolved_at' => now()->subDays(1),
                'created_at' => now()->subDays(4),
            ]
        );

        // 4. Ticket Closed: Kháng nghị duyệt sản phẩm
        SellerSupportTicket::firstOrCreate(
            [
                'seller_id' => $seller2->id,
                'subject' => 'Hỏi về lý do từ chối sản phẩm "Váy lụa dạ hội"',
            ],
            [
                'category' => 'product_rejected',
                'message' => 'Sản phẩm của shop bị từ chối với lý do ảnh mờ, shop đã cập nhật lại ảnh độ phân giải cao 2000x2000px rồi ạ.',
                'status' => 'closed',
                'admin_response' => 'Admin đã kiểm tra lại và duyệt sản phẩm cho shop rồi nhé. Chúc shop buôn may bán đắt!',
                'resolved_by' => $admin?->id,
                'resolved_at' => now()->subDays(3),
                'created_at' => now()->subDays(5),
            ]
        );
    }
}
