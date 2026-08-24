<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerUpdateOrderStatusRequest;
use App\Models\SellerOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    /**
     * GET /seller/orders
     * Lấy danh sách đơn hàng thuộc sở hữu của Shop đang đăng nhập
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $orders = SellerOrder::where('seller_id', $request->user()->id)
            ->with(['order.user', 'items.product'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    /**
     * GET /seller/orders/{sellerOrder}
     * Xem chi tiết 1 đơn hàng của Shop mình
     */
    public function show(Request $request, SellerOrder $sellerOrder): JsonResponse
    {
        // Chặn nếu Seller cố tình xem đơn hàng của Shop khác
        if ($sellerOrder->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền truy cập đơn hàng này.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $sellerOrder->load(['order', 'items.product', 'items.variant']),
        ]);
    }

    /**
     * PATCH /seller/orders/{sellerOrder}/status
     * Seller cập nhật trạng thái đơn hàng (Confirmed -> Shipping -> Completed / Cancelled)
     */
    public function updateStatus(SellerUpdateOrderStatusRequest $request, SellerOrder $sellerOrder): JsonResponse
    {
        // Chặn nếu Seller cố tình sửa đơn hàng của Shop khác
        if ($sellerOrder->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền cập nhật đơn hàng này.'], 403);
        }

        $validated = $request->validated();
        $updateData = ['status' => $validated['status']];

        // Nếu chuyển sang trạng thái đang giao hàng -> Lưu mã vận đơn
        if (! empty($validated['tracking_number'])) {
            $updateData['tracking_number'] = $validated['tracking_number'];
        }

        $sellerOrder->update($updateData);

        return response()->json([
            'message' => 'Cập nhật trạng thái đơn hàng thành công!',
            'data' => $sellerOrder->fresh(['order', 'items']),
        ]);
    }
}
