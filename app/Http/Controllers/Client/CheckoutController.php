<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CheckoutRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * GET /checkout
     * Hiển thị trang thanh toán riêng (Shopee-Style Checkout Page)
     *
     * Hỗ trợ 2 luồng:
     *  1. Từ Giỏ hàng: đọc các cart_item_ids được chọn từ query string
     *  2. Từ "Mua Ngay" (trang chi tiết sản phẩm): đọc product_id, variant_id, qty từ query string
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        // Luồng 1: Mua từ Giỏ hàng → cart_item_ids[]=1&cart_item_ids[]=2...
        if ($request->filled('cart_item_ids')) {
            $cartItemIds = array_filter(explode(',', $request->get('cart_item_ids')));
            $cart = Cart::with(['items.product.seller.sellerProfile', 'items.variant'])
                ->where('user_id', $user->id)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
            }

            // Lọc chỉ lấy những item được tick chọn
            $selectedItems = $cart->items->filter(fn($i) => in_array($i->id, $cartItemIds))->values();

            if ($selectedItems->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
            }

            // Gom nhóm theo Shop
            $groupedShops = $selectedItems->groupBy(fn($item) => $item->product->seller_id)
                ->map(function ($items) {
                    $seller = $items->first()->product->seller;
                    return [
                        'seller_id'    => $seller->id,
                        'shop_name'    => $seller->sellerProfile->shop_name ?? $seller->name,
                        'shop_profile' => $seller->sellerProfile,
                        'items'        => $items->values(),
                    ];
                })->values();

            $totalPrice = $selectedItems->sum(fn($i) => ($i->variant ? $i->variant->current_price : $i->product->current_price) * $i->quantity);
            $totalQty   = $selectedItems->sum('quantity');
            $checkoutMode = 'cart';

            return view('client.checkout.index', compact(
                'groupedShops', 'totalPrice', 'totalQty',
                'addresses', 'defaultAddress', 'checkoutMode', 'cartItemIds'
            ));
        }

        // Luồng 2: Mua Ngay từ trang sản phẩm → ?product_id=X&variant_id=Y&qty=Z
        if ($request->filled('product_id')) {
            $product = Product::with(['seller.sellerProfile', 'variants'])
                ->where('id', $request->get('product_id'))
                ->where('status', 'approved')
                ->first();

            if (!$product) {
                return redirect()->route('home')->with('error', 'Sản phẩm không tồn tại hoặc không còn được bán.');
            }

            $variantId = $request->get('variant_id');
            $qty       = max(1, (int) $request->get('qty', 1));
            $variant   = $variantId ? $product->variants->find($variantId) : null;

            // Kiểm tra tồn kho
            $stock = $variant ? $variant->stock : $product->stock;
            if ($stock < $qty) {
                return redirect()->back()->with('error', "Tồn kho không đủ (Còn: $stock).");
            }

            $unitPrice   = $variant ? $variant->current_price : $product->current_price;
            $origPrice   = $variant ? $variant->price         : $product->price;
            $totalPrice  = $unitPrice * $qty;
            $totalQty    = $qty;
            $checkoutMode = 'direct';

            // Tạo cấu trúc "pseudo shop group" để dùng chung 1 view
            $groupedShops = collect([[
                'seller_id'    => $product->seller->id,
                'shop_name'    => $product->seller->sellerProfile->shop_name ?? $product->seller->name,
                'shop_profile' => $product->seller->sellerProfile,
                'items'        => collect([[
                    'is_direct'    => true,
                    'product'      => $product,
                    'variant'      => $variant,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'orig_price'   => $origPrice,
                    'subtotal'     => $totalPrice,
                ]]),
            ]]);

            return view('client.checkout.index', compact(
                'groupedShops', 'totalPrice', 'totalQty',
                'addresses', 'defaultAddress', 'checkoutMode',
                'product', 'variant', 'qty', 'unitPrice', 'origPrice'
            ));
        }

        // Mặc định: chuyển về giỏ hàng
        return redirect()->route('cart.index');
    }

    /**
     * POST /checkout
     * Đặt hàng & Tự động tách đơn theo Shop (Shopee-Style Order Splitting)
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        $user      = $request->user();
        $validated = $request->validated();

        try {
            $order = DB::transaction(function () use ($validated, $user, $request) {
                // 1. Lấy giỏ hàng của Khách hàng hiện tại
                $cart = Cart::with(['items.product.seller', 'items.variant'])
                    ->where('user_id', $user->id)
                    ->first();

                // ============================================================
                // Luồng "Mua Ngay" (direct): Chỉ đặt 1 sản phẩm / 1 biến thể
                // ============================================================
                $checkoutMode = $validated['checkout_mode'] ?? 'cart';

                if ($checkoutMode === 'direct') {
                    $product = Product::findOrFail($validated['product_id']);
                    $variant = $validated['product_variant_id']
                        ? ProductVariant::findOrFail($validated['product_variant_id'])
                        : null;
                    $qty   = (int) ($validated['qty'] ?? 1);
                    $price = $variant ? $variant->current_price : $product->current_price;

                    if ($product->status !== 'approved') {
                        throw new \Exception("Sản phẩm '{$product->name}' hiện không còn được bán.");
                    }
                    $stock = $variant ? $variant->stock : $product->stock;
                    if ($stock < $qty) {
                        throw new \Exception("Sản phẩm '{$product->name}' không đủ tồn kho (Còn: $stock).");
                    }

                    $order = Order::create([
                        'user_id'           => $user->id,
                        'order_number'      => 'ORD-' . strtoupper(Str::random(10)),
                        'shipping_name'     => $validated['recipient_name'],
                        'shipping_phone'    => $validated['phone'],
                        'shipping_address'  => $validated['shipping_address'],
                        'payment_method'    => $validated['payment_method'],
                        'payment_status'    => 'pending',
                        'total_item_amount' => 0,
                        'total_shipping_fee'=> 0,
                        'total_discount'    => 0,
                        'grand_total'       => 0,
                        'notes'             => $validated['note'] ?? null,
                    ]);

                    $itemTotal = $price * $qty;
                    $sellerOrder = SellerOrder::create([
                        'order_id'          => $order->id,
                        'seller_id'         => $product->seller_id,
                        'sub_total'         => $itemTotal,
                        'shipping_fee'      => 0,
                        'discount_amount'   => 0,
                        'grand_total'       => $itemTotal,
                        'commission_amount' => 0,
                        'status'            => 'pending',
                    ]);

                    OrderItem::create([
                        'seller_order_id'    => $sellerOrder->id,
                        'product_id'         => $product->id,
                        'product_variant_id' => $variant?->id,
                        'product_name'       => $product->name,
                        'product_image'      => $product->thumbnail,
                        'price'              => $price,
                        'quantity'           => $qty,
                        'total'              => $itemTotal,
                    ]);

                    // Trừ tồn kho
                    if ($variant) {
                        $variant->decrement('stock', $qty);
                    } else {
                        $product->decrement('stock', $qty);
                    }

                    $order->update([
                        'total_item_amount' => $itemTotal,
                        'grand_total'       => $itemTotal,
                    ]);

                    return $order;
                }

                // ============================================================
                // Luồng Giỏ hàng: lọc theo cart_item_ids hoặc toàn bộ giỏ
                // ============================================================
                if (!$cart || $cart->items->isEmpty()) {
                    throw new \Exception('Giỏ hàng của bạn đang trống, không thể đặt hàng.');
                }

                // Lọc theo cart_item_ids nếu được truyền
                $cartItemIds = !empty($validated['cart_item_ids'])
                    ? array_filter(explode(',', $validated['cart_item_ids']))
                    : null;

                $itemsToCheckout = $cartItemIds
                    ? $cart->items->filter(fn($i) => in_array($i->id, $cartItemIds))->values()
                    : $cart->items;

                if ($itemsToCheckout->isEmpty()) {
                    throw new \Exception('Không tìm thấy sản phẩm để đặt hàng.');
                }

                // Kiểm tra trạng thái & tồn kho
                foreach ($itemsToCheckout as $item) {
                    if ($item->product->status !== 'approved') {
                        throw new \Exception("Sản phẩm '{$item->product->name}' hiện không còn được bán.");
                    }
                    $stock = $item->variant ? $item->variant->stock : $item->product->stock;
                    if ($stock < $item->quantity) {
                        throw new \Exception("Sản phẩm '{$item->product->name}' không đủ tồn kho (Còn: $stock).");
                    }
                }

                // Tạo Master Order
                $order = Order::create([
                    'user_id'           => $user->id,
                    'order_number'      => 'ORD-' . strtoupper(Str::random(10)),
                    'shipping_name'     => $validated['recipient_name'],
                    'shipping_phone'    => $validated['phone'],
                    'shipping_address'  => $validated['shipping_address'],
                    'payment_method'    => $validated['payment_method'],
                    'payment_status'    => 'pending',
                    'total_item_amount' => 0,
                    'total_shipping_fee'=> 0,
                    'total_discount'    => 0,
                    'grand_total'       => 0,
                    'notes'             => $validated['note'] ?? null,
                ]);

                // Tách đơn theo Shop
                $groupedItems       = $itemsToCheckout->groupBy(fn($i) => $i->product->seller_id);
                $masterTotalAmount  = 0;

                foreach ($groupedItems as $sellerId => $sellerItems) {
                    $subTotal    = 0;
                    $sellerOrder = SellerOrder::create([
                        'order_id'          => $order->id,
                        'seller_id'         => $sellerId,
                        'sub_total'         => 0,
                        'shipping_fee'      => 0,
                        'discount_amount'   => 0,
                        'grand_total'       => 0,
                        'commission_amount' => 0,
                        'status'            => 'pending',
                    ]);

                    foreach ($sellerItems as $item) {
                        $price     = $item->variant ? $item->variant->current_price : $item->product->current_price;
                        $itemTotal = $price * $item->quantity;
                        $subTotal += $itemTotal;

                        OrderItem::create([
                            'seller_order_id'    => $sellerOrder->id,
                            'product_id'         => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'product_name'       => $item->product->name,
                            'product_image'      => $item->product->thumbnail,
                            'price'              => $price,
                            'quantity'           => $item->quantity,
                            'total'              => $itemTotal,
                        ]);

                        if ($item->variant) {
                            $item->variant->decrement('stock', $item->quantity);
                        } else {
                            $item->product->decrement('stock', $item->quantity);
                        }
                    }

                    $sellerOrder->update(['sub_total' => $subTotal, 'grand_total' => $subTotal]);
                    $masterTotalAmount += $subTotal;
                }

                $order->update([
                    'total_item_amount' => $masterTotalAmount,
                    'grand_total'       => $masterTotalAmount,
                ]);

                // Xóa khỏi giỏ hàng những item đã đặt
                if ($cartItemIds) {
                    $cart->items()->whereIn('id', $cartItemIds)->delete();
                } else {
                    $cart->items()->delete();
                }

                return $order;
            });

            return response()->json([
                'message'    => 'Đặt hàng thành công!',
                'order_id'   => $order->id,
                'order_number' => $order->order_number,
                'redirect'   => route('customer.orders.index'),
                'data'       => $order->load(['sellerOrders.seller.sellerProfile', 'sellerOrders.items']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
