<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * POST /checkout
     * Đặt hàng & Tự động tách đơn theo Shop (Shopee-Style Order Splitting)
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            $order = DB::transaction(function () use ($validated, $user) {
                // 1. Lấy giỏ hàng của Khách hàng hiện tại
                $cart = Cart::with(['items.product.seller', 'items.variant'])
                    ->where('user_id', $user->id)
                    ->first();

                if (!$cart || $cart->items->isEmpty()) {
                    throw new \Exception('Giỏ hàng của bạn đang trống, không thể đặt hàng.');
                }

                // 2. Kiểm tra lại Trạng thái Đã duyệt & Tồn kho của toàn bộ sản phẩm trong giỏ
                foreach ($cart->items as $item) {
                    if ($item->product->status !== 'approved') {
                        throw new \Exception("Sản phẩm '{$item->product->name}' hiện không còn được bán.");
                    }

                    $stock = $item->variant ? $item->variant->stock : $item->product->stock;
                    if ($stock < $item->quantity) {
                        throw new \Exception("Sản phẩm '{$item->product->name}' không đủ tồn kho (Còn lại: {$stock}).");
                    }
                }

                // 3. Tạo Đơn hàng Tổng (Master Order)
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                    'shipping_name' => $validated['recipient_name'],
                    'shipping_phone' => $validated['phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'pending',
                    'total_item_amount' => 0,
                    'total_shipping_fee' => 0,
                    'total_discount' => 0,
                    'grand_total' => 0,
                    'notes' => $validated['note'] ?? null,
                ]);

                // 4.  TỰ ĐỘNG TÁCH ĐƠN THEO TỪNG SHOP (Shopee-Style Order Splitting)
                $groupedItems = $cart->items->groupBy(fn($item) => $item->product->seller_id);
                $masterTotalAmount = 0;

                foreach ($groupedItems as $sellerId => $sellerItems) {
                    $subTotal = 0;

                    // Tạo Đơn hàng con thuộc về riêng Shop này
                    $sellerOrder = SellerOrder::create([
                        'order_id' => $order->id,
                        'seller_id' => $sellerId,
                        'sub_total' => 0,
                        'shipping_fee' => 0,
                        'discount_amount' => 0,
                        'grand_total' => 0,
                        'commission_amount' => 0,
                        'status' => 'pending',
                    ]);

                    foreach ($sellerItems as $item) {
                        $price = $item->variant ? $item->variant->price : $item->product->price;
                        $itemTotal = $price * $item->quantity;
                        $subTotal += $itemTotal;

                        // Lưu các món vào đơn hàng con của Shop
                        OrderItem::create([
                            'seller_order_id' => $sellerOrder->id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'product_name' => $item->product->name,
                            'product_image' => $item->product->thumbnail,
                            'price' => $price,
                            'quantity' => $item->quantity,
                            'total' => $itemTotal,
                        ]);

                        // Trừ tồn kho sản phẩm (stock)
                        if ($item->variant) {
                            $item->variant->decrement('stock', $item->quantity);
                        } else {
                            $item->product->decrement('stock', $item->quantity);
                        }
                    }

                    // Cập nhật tổng tiền đơn hàng con
                    $sellerOrder->update([
                        'sub_total' => $subTotal,
                        'grand_total' => $subTotal,
                    ]);

                    $masterTotalAmount += $subTotal;
                }

                // Cập nhật tổng tiền Đơn hàng tổng Master
                $order->update([
                    'total_item_amount' => $masterTotalAmount,
                    'grand_total' => $masterTotalAmount,
                ]);

                // 5. Làm sạch Giỏ hàng sau khi đặt thành công
                $cart->items()->delete();

                return $order;
            });

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'data' => $order->load(['sellerOrders.seller.sellerProfile', 'sellerOrders.items']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
