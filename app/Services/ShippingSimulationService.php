<?php

namespace App\Services;

use App\Models\OrderShippingLog;
use App\Models\SellerOrder;
use App\Models\ShippingCarrier;

class ShippingSimulationService
{
    /**
     * Tự động sinh mã vận đơn chuẩn theo hãng.
     */
    public function generateTrackingNumber(?string $carrierCode = null): string
    {
        $prefix = match ($carrierCode) {
            'ghn' => 'GHNVN',
            'ghtk' => 'GHTK',
            'viettelpost' => 'VTP',
            'instant' => 'EXPRESS',
            default => 'SPXVN',
        };

        return $prefix.strtoupper(substr(uniqid(), -6)).rand(1000, 9999);
    }

    /**
     * Lấy danh sách timeline hành trình chi tiết của kiện hàng.
     * Nếu đã có log trong DB thì trả về, nếu chưa có thì tự động sinh timeline động logic theo thời gian.
     */
    public function getTimeline(SellerOrder $sellerOrder): array
    {
        $logs = $sellerOrder->shippingLogs()->with('carrier')->get();

        if ($logs->isNotEmpty()) {
            return $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'status' => $log->status,
                    'title' => $log->title,
                    'location' => $log->location,
                    'description' => $log->description,
                    'time' => $log->event_time->format('H:i - d/m/Y'),
                    'badge_class' => $log->status_badge_class,
                ];
            })->toArray();
        }

        // Tự động sinh timeline giả lập thông minh dựa theo trạng thái đơn
        return $this->generateVirtualTimeline($sellerOrder);
    }

    /**
     * Giả lập bước vận chuyển tiếp theo cho đơn hàng (Dành cho Admin / Demo test).
     */
    public function advanceNextStep(SellerOrder $sellerOrder): array
    {
        $carrier = $sellerOrder->carrier ?? ShippingCarrier::where('is_default', true)->first() ?? ShippingCarrier::first();
        if (! $sellerOrder->carrier_id && $carrier) {
            $sellerOrder->carrier_id = $carrier->id;
        }

        if (! $sellerOrder->tracking_number) {
            $sellerOrder->tracking_number = $this->generateTrackingNumber($carrier?->code);
        }

        $now = now();
        $hubLocations = [
            'Kho tổng phân loại Củ Chi SOC (TP.HCM)',
            'Kho trung chuyển Bắc Ninh Mega SOC',
            'Kho luân chuyển miền Trung Đà Nẵng',
            'Bưu cục giao hàng Ba Đình Hub (Hà Nội)',
            'Bưu cục giao nhận Quận 1 (TP.HCM)',
        ];
        $shippers = [
            'Nguyễn Văn Bình (SĐT: 0982.114.556)',
            'Trần Anh Tuấn (SĐT: 0973.882.190)',
            'Lê Hoàng Long (SĐT: 0912.445.901)',
        ];

        $nextStatus = match ($sellerOrder->status) {
            'pending' => 'confirmed',
            'confirmed' => 'shipping',
            'shipping' => 'completed',
            default => 'completed',
        };

        // Ghi log chi tiết tương ứng với bước
        $newLogs = [];

        if ($sellerOrder->status === 'pending') {
            $sellerOrder->status = 'confirmed';
            $newLogs[] = OrderShippingLog::create([
                'seller_order_id' => $sellerOrder->id,
                'carrier_id' => $carrier?->id,
                'status' => 'preparing',
                'title' => 'Người bán đang chuẩn bị hàng',
                'location' => $sellerOrder->seller->sellerProfile->address ?? 'Kho người bán',
                'description' => 'Gian hàng đã xác nhận đơn và đang tiến hành đóng gói sản phẩm.',
                'event_time' => $now,
            ]);
        } elseif ($sellerOrder->status === 'confirmed') {
            $sellerOrder->status = 'shipping';
            $newLogs[] = OrderShippingLog::create([
                'seller_order_id' => $sellerOrder->id,
                'carrier_id' => $carrier?->id,
                'status' => 'picked_up',
                'title' => 'Đơn vị vận chuyển đã lấy hàng',
                'location' => 'Bưu cục tiếp nhận '.$carrier?->name,
                'description' => 'Bưu tá đã nhận kiện hàng từ người bán và chuẩn bị nhập kho.',
                'event_time' => $now->copy()->subMinutes(15),
            ]);

            $newLogs[] = OrderShippingLog::create([
                'seller_order_id' => $sellerOrder->id,
                'carrier_id' => $carrier?->id,
                'status' => 'in_transit',
                'title' => 'Đơn hàng đang trung chuyển',
                'location' => $hubLocations[array_rand($hubLocations)],
                'description' => 'Kiện hàng đã rời trung tâm khai thác và đang trên đường chuyển đến kho giao.',
                'event_time' => $now,
            ]);
        } elseif ($sellerOrder->status === 'shipping') {
            $sellerOrder->status = 'completed';
            $newLogs[] = OrderShippingLog::create([
                'seller_order_id' => $sellerOrder->id,
                'carrier_id' => $carrier?->id,
                'status' => 'delivering',
                'title' => 'Shipper đang giao hàng',
                'location' => 'Trạm giao hàng địa phương',
                'description' => 'Bưu tá '.$shippers[array_rand($shippers)].' đang mang hàng đi giao đến địa chỉ của bạn.',
                'event_time' => $now->copy()->subMinutes(20),
            ]);

            $newLogs[] = OrderShippingLog::create([
                'seller_order_id' => $sellerOrder->id,
                'carrier_id' => $carrier?->id,
                'status' => 'delivered',
                'title' => 'Giao hàng thành công',
                'location' => $sellerOrder->order->shipping_address ?? 'Địa chỉ khách hàng',
                'description' => 'Người nhận đã nhận hàng thành công và đồng kiểm đầy đủ.',
                'event_time' => $now,
            ]);
        }

        $sellerOrder->save();

        return [
            'current_status' => $sellerOrder->status,
            'tracking_number' => $sellerOrder->tracking_number,
            'timeline' => $this->getTimeline($sellerOrder),
        ];
    }

    /**
     * Sinh danh sách mốc thời gian ảo chân thực nếu chưa có log DB.
     */
    protected function generateVirtualTimeline(SellerOrder $sellerOrder): array
    {
        $createdAt = $sellerOrder->created_at ?? now();
        $carrier = $sellerOrder->carrier?->name ?? 'SPX Express';
        $timeline = [];

        // Mốc 1: Đặt hàng thành công
        $timeline[] = [
            'status' => 'order_placed',
            'title' => 'Đơn hàng đã được đặt thành công',
            'location' => 'Hệ thống Cupo',
            'description' => 'Khách hàng đã hoàn tất bước đặt mua đơn hàng.',
            'time' => $createdAt->format('H:i - d/m/Y'),
            'badge_class' => 'bg-secondary text-white',
        ];

        if (in_array($sellerOrder->status, ['confirmed', 'shipping', 'completed'])) {
            $t2 = $createdAt->copy()->addMinutes(35);
            $timeline[] = [
                'status' => 'preparing',
                'title' => 'Người bán đang chuẩn bị hàng',
                'location' => 'Kho người bán',
                'description' => 'Người bán đã đóng gói và dán mã vận đơn '.$sellerOrder->tracking_number,
                'time' => $t2->format('H:i - d/m/Y'),
                'badge_class' => 'bg-info text-dark',
            ];
        }

        if (in_array($sellerOrder->status, ['shipping', 'completed'])) {
            $t3 = $createdAt->copy()->addHours(2);
            $timeline[] = [
                'status' => 'picked_up',
                'title' => $carrier.' đã tiếp nhận bưu kiện',
                'location' => 'Bưu cục gửi',
                'description' => 'Bưu tá đã lấy hàng từ người bán và nhập kho khai thác.',
                'time' => $t3->format('H:i - d/m/Y'),
                'badge_class' => 'bg-primary text-white',
            ];

            $t4 = $createdAt->copy()->addHours(6);
            $timeline[] = [
                'status' => 'in_transit',
                'title' => 'Đơn hàng đã đến kho phân loại Củ Chi SOC',
                'location' => 'Kho tổng phân loại Củ Chi SOC',
                'description' => 'Kiện hàng đã được phân luồng luân chuyển tới bưu cục phát.',
                'time' => $t4->format('H:i - d/m/Y'),
                'badge_class' => 'bg-warning text-dark',
            ];
        }

        if ($sellerOrder->status === 'completed') {
            $t5 = $createdAt->copy()->addHours(14);
            $timeline[] = [
                'status' => 'delivering',
                'title' => 'Shipper đang phát hàng',
                'location' => 'Kho phát khu vực',
                'description' => 'Bưu tá Nguyễn Văn Bình (0982.114.556) đang giao hàng đến bạn.',
                'time' => $t5->format('H:i - d/m/Y'),
                'badge_class' => 'bg-indigo text-white',
            ];

            $t6 = $createdAt->copy()->addHours(16);
            $timeline[] = [
                'status' => 'delivered',
                'title' => 'Giao hàng thành công',
                'location' => $sellerOrder->order->shipping_address ?? 'Địa chỉ người nhận',
                'description' => 'Kiện hàng đã được giao tận tay khách hàng thành công.',
                'time' => $t6->format('H:i - d/m/Y'),
                'badge_class' => 'bg-success text-white',
            ];
        }

        // Đảo ngược lại để mốc mới nhất nằm ở trên cùng (chuẩn Shopee)
        return array_reverse($timeline);
    }
}
