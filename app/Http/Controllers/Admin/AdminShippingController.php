<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerOrder;
use App\Models\ShippingCarrier;
use App\Services\ShippingSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminShippingController extends Controller
{
    /**
     * Danh sách đối tác vận chuyển & cài đặt cước phí sàn.
     */
    public function index(Request $request): View|JsonResponse
    {
        $carriers = ShippingCarrier::withCount('sellerOrders')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $stats = [
            'total_carriers' => $carriers->count(),
            'active_carriers' => $carriers->where('is_active', true)->count(),
            'total_shipments' => SellerOrder::count(),
            'in_transit_count' => SellerOrder::whereIn('status', ['confirmed', 'shipping'])->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $carriers,
                'stats' => $stats,
            ]);
        }

        return view('admin.shipping.index', compact('carriers', 'stats'));
    }

    /**
     * Bật / Tắt hoạt động của đối tác vận chuyển.
     */
    public function toggleActive(ShippingCarrier $carrier): JsonResponse
    {
        $carrier->update(['is_active' => ! $carrier->is_active]);

        return response()->json([
            'message' => $carrier->is_active ? 'Đã kích hoạt đối tác vận chuyển!' : 'Đã tạm ngưng đối tác vận chuyển!',
            'is_active' => $carrier->is_active,
        ]);
    }

    /**
     * Đặt làm hãng vận chuyển mặc định của sàn.
     */
    public function setDefault(ShippingCarrier $carrier): JsonResponse
    {
        // Reset tất cả các hãng khác
        ShippingCarrier::query()->update(['is_default' => false]);
        $carrier->update(['is_default' => true, 'is_active' => true]);

        return response()->json([
            'message' => 'Đã đặt "'.$carrier->name.'" làm đơn vị vận chuyển mặc định!',
            'data' => $carrier,
        ]);
    }

    /**
     * Cập nhật thông tin và cước phí của hãng vận chuyển.
     */
    public function updateCarrier(Request $request, ShippingCarrier $carrier): JsonResponse
    {
        $validated = $request->validate([
            'base_fee' => ['required', 'numeric', 'min:0'],
            'estimated_days' => ['required', 'string', 'max:50'],
            'hotline' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
            'tracking_url_template' => ['nullable', 'string', 'max:500'],
        ], [
            'base_fee.required' => 'Vui lòng nhập cước phí cơ bản.',
            'base_fee.numeric' => 'Cước phí phải là số hợp lệ.',
            'estimated_days.required' => 'Vui lòng nhập thời gian giao hàng dự kiến.',
        ]);

        $carrier->update($validated);

        return response()->json([
            'message' => 'Cập nhật thông tin hãng vận chuyển thành công!',
            'data' => $carrier,
        ]);
    }

    /**
     * Danh sách tất cả các gói hàng đang luân chuyển trên sàn.
     */
    public function orders(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            $carriers = ShippingCarrier::where('is_active', true)->get();

            return view('admin.shipping.orders', compact('carriers'));
        }

        $carrierId = $request->query('carrier_id');
        $status = $request->query('status');
        $keyword = $request->query('search');

        $orders = SellerOrder::with(['order.user', 'seller.sellerProfile', 'carrier'])
            ->when($carrierId, fn ($q) => $q->where('carrier_id', $carrierId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('tracking_number', 'like', "%{$keyword}%")
                        ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$keyword}%")
                            ->orWhere('shipping_name', 'like', "%{$keyword}%")
                            ->orWhere('shipping_phone', 'like', "%{$keyword}%"))
                        ->orWhereHas('seller.sellerProfile', fn ($sq) => $sq->where('shop_name', 'like', "%{$keyword}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = SellerOrder::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json(array_merge($orders->toArray(), [
            'meta' => [
                'total_all' => $counts->sum(),
                'total_pending' => $counts->get('pending', 0),
                'total_confirmed' => $counts->get('confirmed', 0),
                'total_shipping' => $counts->get('shipping', 0),
                'total_completed' => $counts->get('completed', 0),
                'total_cancelled' => $counts->get('cancelled', 0),
            ],
        ]));
    }

    /**
     * Lấy lộ trình chi tiết kiện hàng (Timeline).
     */
    public function tracking(SellerOrder $sellerOrder, ShippingSimulationService $simulator): JsonResponse
    {
        $sellerOrder->load(['order.user', 'seller.sellerProfile', 'carrier']);

        return response()->json([
            'order_number' => $sellerOrder->order->order_number ?? 'N/A',
            'tracking_number' => $sellerOrder->tracking_number ?? 'Chưa tạo mã',
            'carrier_name' => $sellerOrder->carrier->name ?? 'SPX Express',
            'carrier_logo' => $sellerOrder->carrier->logo ?? null,
            'status' => $sellerOrder->status,
            'recipient' => [
                'name' => $sellerOrder->order->shipping_name ?? '',
                'phone' => $sellerOrder->order->shipping_phone ?? '',
                'address' => $sellerOrder->order->shipping_address ?? '',
            ],
            'shop' => [
                'name' => $sellerOrder->seller->sellerProfile->shop_name ?? '',
                'address' => $sellerOrder->seller->sellerProfile->address ?? '',
            ],
            'timeline' => $simulator->getTimeline($sellerOrder),
        ]);
    }

    /**
     * Giả lập bước tiếp theo của hành trình vận chuyển (1-click simulate).
     */
    public function simulateNextStep(SellerOrder $sellerOrder, ShippingSimulationService $simulator): JsonResponse
    {
        $result = $simulator->advanceNextStep($sellerOrder);

        return response()->json([
            'message' => 'Đã cập nhật bước vận chuyển tiếp theo thành công!',
            'data' => $result,
        ]);
    }
}
