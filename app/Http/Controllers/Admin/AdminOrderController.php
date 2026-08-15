<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateSellerOrderStatusRequest;
use App\Models\Order;
use App\Models\SellerOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    /**
     * GET /admin/orders
     * Danh sach tat ca don hang.
     * - Browser -> Blade view (admin.orders.index)
     * - AJAX/JSON -> JSON paginate kem meta dem trang thai
     *
     * Query params:
     *   payment_status      = pending | paid | failed | refunded
     *   seller_order_status = pending | confirmed | shipping | completed | cancelled
     *   date_from           = Y-m-d
     *   date_to             = Y-m-d
     *   q                   = tu khoa (order_number, shipping_name, shipping_phone)
     *   page                = so trang
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('admin.orders.index');
        }

        $paymentStatus = $request->query('payment_status');
        $sellerOrderStatus = $request->query('seller_order_status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $keyword = $request->query('q');

        $orders = Order::with(['user', 'sellerOrders'])
            ->withCount('sellerOrders')
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->when($sellerOrderStatus, fn ($q) => $q->whereHas('sellerOrders', fn ($sq) => $sq->where('status', $sellerOrderStatus)))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('order_number', 'like', '%'.$keyword.'%')
                        ->orWhere('shipping_name', 'like', '%'.$keyword.'%')
                        ->orWhere('shipping_phone', 'like', '%'.$keyword.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Stat meta: dem theo payment_status
        $paymentCounts = Order::selectRaw('payment_status, count(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        // Dem seller_order theo status (cho stat cards xu ly don)
        $sellerOrderCounts = SellerOrder::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json(array_merge($orders->toArray(), [
            'meta' => [
                'total_all' => Order::count(),
                'payment_pending' => $paymentCounts->get('pending', 0),
                'payment_paid' => $paymentCounts->get('paid', 0),
                'payment_failed' => $paymentCounts->get('failed', 0),
                'payment_refunded' => $paymentCounts->get('refunded', 0),
                'seller_order_pending' => $sellerOrderCounts->get('pending', 0),
                'seller_order_shipping' => $sellerOrderCounts->get('shipping', 0),
                'seller_order_completed' => $sellerOrderCounts->get('completed', 0),
                'seller_order_cancelled' => $sellerOrderCounts->get('cancelled', 0),
            ],
        ]));
    }

    /**
     * GET /admin/orders/{order}
     * Chi tiet 1 don hang.
     */
    public function show(Request $request, Order $order): View|JsonResponse
    {
        $order->load([
            'user',
            'sellerOrders.seller.sellerProfile',
            'sellerOrders.items.product',
            'sellerOrders.items.variant',
            'paymentTransactions',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $order]);
        }

        return view('admin.orders.show', compact('order'));
    }

    /**
     * PATCH /admin/orders/seller-orders/{sellerOrder}/status
     * Admin cap nhat trang thai cua 1 seller_order.
     * Admin co the cap nhat bat ky trang thai (ke ca cancel).
     */
    public function updateSellerOrderStatus(
        AdminUpdateSellerOrderStatusRequest $request,
        SellerOrder $sellerOrder
    ): JsonResponse {
        $validated = $request->validated();

        $updateData = ['status' => $validated['status']];

        if (! empty($validated['tracking_number'])) {
            $updateData['tracking_number'] = $validated['tracking_number'];
        }

        // Luu ly do huy vao notes cua order cha (khong co cot rieng trong seller_orders)
        // TODO: khi WalletService duoc trien khai, goi WalletService::credit() khi status = completed
        // if ($validated['status'] === 'completed') {
        //     app(WalletService::class)->credit($sellerOrder);
        // }

        $sellerOrder->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái đơn hàng thành công!',
            'data' => $sellerOrder->fresh(['order', 'seller.sellerProfile']),
        ]);
    }

    /**
     * GET /admin/orders/export
     * Xuat CSV tat ca don hang theo bo loc hien tai.
     */
    public function export(Request $request): StreamedResponse
    {
        $paymentStatus = $request->query('payment_status');
        $sellerOrderStatus = $request->query('seller_order_status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $keyword = $request->query('q');

        $orders = Order::with(['user', 'sellerOrders'])
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->when($sellerOrderStatus, fn ($q) => $q->whereHas('sellerOrders', fn ($sq) => $sq->where('status', $sellerOrderStatus)))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('order_number', 'like', '%'.$keyword.'%')
                        ->orWhere('shipping_name', 'like', '%'.$keyword.'%')
                        ->orWhere('shipping_phone', 'like', '%'.$keyword.'%');
                });
            })
            ->latest()
            ->get();

        $paymentStatusLabel = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán lỗi',
            'refunded' => 'Đã hoàn tiền',
        ];

        $paymentMethodLabel = [
            'cod' => 'Tiền mặt (COD)',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
        ];

        $filename = 'cupo-orders-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($orders, $paymentStatusLabel, $paymentMethodLabel) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM (Excel mo dung tieng Viet)
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Mã đơn hàng', 'Khách hàng', 'SĐT giao hàng', 'Địa chỉ giao hàng',
                'Tiền hàng', 'Phí giao hàng', 'Giảm giá', 'Tổng thanh toán',
                'Phương thức TT', 'Trạng thái TT',
                'Số Seller', 'Ngày đặt',
            ]);

            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->order_number,
                    $o->user?->name ?? $o->shipping_name,
                    $o->shipping_phone,
                    $o->shipping_address,
                    number_format($o->total_item_amount, 0, ',', '.'),
                    number_format($o->total_shipping_fee, 0, ',', '.'),
                    number_format($o->total_discount, 0, ',', '.'),
                    number_format($o->grand_total, 0, ',', '.'),
                    $paymentMethodLabel[$o->payment_method] ?? $o->payment_method,
                    $paymentStatusLabel[$o->payment_status] ?? $o->payment_status,
                    $o->sellerOrders->count(),
                    $o->created_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
