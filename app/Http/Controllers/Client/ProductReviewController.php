<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreProductReviewRequest;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * GET /products/{product}/reviews
     * Xem danh sách đánh giá của 1 sản phẩm (Công khai)
     */
    public function index(Product $product): JsonResponse
    {
        $reviews = Review::where('product_id', $product->id)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $avgRating = Review::where('product_id', $product->id)
            ->where('status', 'approved')
            ->avg('rating');

        return response()->json([
            'status' => 'success',
            'data' => [
                'average_rating' => round($avgRating ?? 0, 1),
                'total_reviews' => $reviews->total(),
                'reviews' => $reviews,
            ],
        ]);
    }

    /**
     * POST /products/{product}/reviews
     * Khách hàng gửi đánh giá sản phẩm (Bắt buộc đã mua sản phẩm đó thành công)
     */
    public function store(StoreProductReviewRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();

        //  BẢO VỆ CHỐNG REVIEW FAKE: Kiểm tra xem Khách đã mua sản phẩm này và đơn con đã hoàn thành (completed) chưa!
        $orderItem = OrderItem::whereHas('sellerOrder', function ($query) use ($user) {
            $query->where('status', 'completed')
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })->where('product_id', $product->id)->first();

        if (! $orderItem) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua hàng và đơn hàng đã hoàn thành thành công.',
            ], 400);
        }

        $validated = $request->validated();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $orderItem->sellerOrder->order_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Đánh giá sản phẩm thành công! Cảm ơn nhận xét của bạn.',
            'data' => $review->load('user:id,name'),
        ], 201);
    }

    /**
     * PUT /reviews/{review}
     * Khách hàng cập nhật đánh giá sản phẩm của chính mình
     */
    public function update(StoreProductReviewRequest $request, Review $review): JsonResponse
    {
        // Chặn nếu cố tình sửa đánh giá của người khác
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Bạn không có quyền chỉnh sửa đánh giá này.',
            ], 403);
        }
        $validated = $request->validated();
        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Cập nhật đánh giá sản phẩm thành công!',
            'data' => $review->fresh('user:id,name'),
        ]);
    }

    /**
     * DELETE /reviews/{review}
     * Khách hàng xóa đánh giá sản phẩm của chính mình
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        // Chặn nếu cố tình xóa đánh giá của người khác
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Bạn không có quyền xóa đánh giá này.',
            ], 403);
        }
        $review->delete();

        return response()->json([
            'message' => 'Đã xóa đánh giá sản phẩm thành công!',
        ]);
    }
}
