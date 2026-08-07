<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    /**
     * Lấy danh sách đơn hàng của người dùng đang đăng nhập
     */
    public function index(Request $request): JsonResponse|View
    {
        $orders = Order::with(['sellerOrders.items.product', 'sellerOrders.seller.sellerProfile'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $orders,
            ]);
        }

        return view('client.profile.index', [
            'user' => $request->user(),
            'orders' => $orders,
            'activeTab' => 'historyOrder',
        ]);
    }

    /**
     * Chi tiết 1 đơn hàng cụ thể
     */
    public function show(Request $request, Order $order): JsonResponse|View
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['sellerOrders.items.product', 'sellerOrders.seller.sellerProfile']);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $order,
            ]);
        }

        return view('client.profile.modals.order-detail', compact('order'));
    }
}
