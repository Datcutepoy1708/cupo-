<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CartStoreRequest;
use App\Http\Requests\Client\CartUpdateRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * GET /cart
     * Lấy danh sách sản phẩm trong giỏ hàng (Gom nhóm theo Shop/Seller)
     */
    public function index(Request $request): JsonResponse|View
    {
        $cart = Cart::with(['items.product.seller.sellerProfile', 'items.product.category', 'items.variant'])
            ->where('user_id', $request->user()->id)
            ->first();
        $groupedShops = collect();
        $totalPrice = 0;
        $totalItems = 0;
        if ($cart && $cart->items->isNotEmpty()) {
            // Gom nhóm sản phẩm theo seller_id (Tách đơn hàng theo Shop)
            $groupedShops = $cart->items->groupBy(function ($item) {
                return $item->product->seller_id;
            })->map(function ($items) {
                $firstProduct = $items->first()->product;
                $seller = $firstProduct->seller;

                return [
                    'seller_id' => $seller->id,
                    'shop_name' => $seller->sellerProfile->shop_name ?? $seller->name,
                    'items' => $items->values(),
                ];
            })->values();
            $totalPrice = $cart->items->sum(function ($item) {
                $price = $item->variant ? $item->variant->price : $item->product->price;

                return $price * $item->quantity;
            });
            $totalItems = $cart->items->sum('quantity');
        }
        // Nếu request yêu cầu JSON (API/Postman/AJAX) -> Trả về JSON
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'cart_id' => $cart->id ?? null,
                    'shops' => $groupedShops,
                    'total_items' => $totalItems,
                    'total_price' => $totalPrice,
                ],
            ]);
        }

        // Yêu cầu từ trình duyệt web -> Trả về Blade View HTML
        return view('client.cart.index', compact('groupedShops', 'totalPrice', 'totalItems'));
    }

    /**
     * POST /cart/add
     * Thêm sản phẩm vào giỏ hàng
     */
    public function store(CartStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        // 1. Chỉ cho phép mua sản phẩm đã được Admin duyệt (status = approved)
        if ($product->status !== 'approved') {
            return response()->json(['message' => 'Sản phẩm này hiện chưa được công khai bán.'], 400);
        }

        // 2. Kiểm tra tồn kho của sản phẩm (hoặc biến thể sản phẩm)
        $variantId = $validated['product_variant_id'] ?? null;
        $availableStock = $variantId
            ? optional($product->variants->find($variantId))->stock
            : $product->stock;

        if ($availableStock < $validated['quantity']) {
            return response()->json(['message' => 'Số lượng tồn kho không đủ (Còn lại: '.$availableStock.').'], 400);
        }

        // 3. Tìm hoặc tạo giỏ hàng cho User
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // 4. Tìm sản phẩm đã có trong giỏ chưa
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $validated['product_variant_id'] ?? null)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            if ($availableStock < $newQuantity) {
                return response()->json(['message' => 'Tổng số lượng trong giỏ vượt quá số lượng tồn kho hiện tại.'], 400);
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'quantity' => $validated['quantity'],
            ]);
        }

        $totalItems = $cart->items()->sum('quantity');

        return response()->json([
            'message' => 'Thêm sản phẩm vào giỏ hàng thành công!',
            'total_items' => $totalItems,
        ]);
    }

    /**
     * PUT /cart/items/{cartItem}
     * Cập nhật số lượng của 1 món trong giỏ hàng
     */
    public function update(CartUpdateRequest $request, CartItem $cartItem): JsonResponse
    {
        // Chặn nếu cố tình sửa giỏ hàng của người khác
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền chỉnh sửa mục giỏ hàng này.'], 403);
        }

        $validated = $request->validated();
        $availableStock = $cartItem->variant ? $cartItem->variant->stock : $cartItem->product->stock;

        if ($availableStock < $validated['quantity']) {
            return response()->json(['message' => 'Số lượng tồn kho không đủ (Còn lại: '.$availableStock.').'], 400);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);
        $totalItems = $cartItem->cart->items()->sum('quantity');

        return response()->json([
            'message' => 'Cập nhật số lượng thành công!',
            'total_items' => $totalItems,
            'data' => $cartItem->fresh(['product', 'variant']),
        ]);
    }

    /**
     * DELETE /cart/items/{cartItem}
     * Xóa 1 món khỏi giỏ hàng
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền xóa mục giỏ hàng này.'], 403);
        }

        $cart = $cartItem->cart;
        $cartItem->delete();
        $totalItems = $cart->items()->sum('quantity');

        return response()->json([
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!',
            'total_items' => $totalItems,
        ]);
    }

    /**
     * DELETE /cart/clear
     * Xóa sạch giỏ hàng
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'message' => 'Đã làm sạch giỏ hàng!',
            'total_items' => 0,
        ]);
    }
}
