<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientProductController extends Controller
{
    /**
     * Trang Xem Chi Tiết Sản Phẩm (Shopee Style Product Detail).
     */
    public function show(Request $request, string $slug): View
    {
        $product = Product::with([
            'seller.sellerProfile',
            'category.parent',
            'images',
            'variants',
            'reviews' => function ($q) {
                $q->where('status', 'approved')->with('user:id,name,avatar')->latest();
            },
        ])
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        // Tăng lượt xem (views_count)
        $product->increment('views_count');

        // Tính điểm đánh giá trung bình & tổng số đánh giá
        $approvedReviews = $product->reviews;
        $avgRating = round((float) $approvedReviews->avg('rating'), 1);
        $totalReviews = $approvedReviews->count();
        $likesCount = (int) $product->likes_count;
        $soldCount = (int) OrderItem::where('product_id', $product->id)
            ->whereHas('sellerOrder', fn ($query) => $query->where('status', 'completed'))
            ->sum('quantity');

        // Sản phẩm liên quan cùng danh mục
        $relatedProducts = Product::with(['seller.sellerProfile', 'category'])
            ->where('status', 'approved')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(6)
            ->get();

        return view('client.products.show', compact(
            'product',
            'avgRating',
            'totalReviews',
            'likesCount',
            'soldCount',
            'relatedProducts'
        ));
    }

    /**
     * AJAX Toggle "Đã thích" / Yêu thích sản phẩm.
     */
    public function toggleLike(Request $request, Product $product): JsonResponse
    {
        $isLiked = $request->session()->get('liked_product_'.$product->id, false);

        if ($isLiked) {
            $product->decrement('likes_count');
            $request->session()->forget('liked_product_'.$product->id);
            $newLiked = false;
        } else {
            $product->increment('likes_count');
            $request->session()->put('liked_product_'.$product->id, true);
            $newLiked = true;
        }

        return response()->json([
            'status' => 'success',
            'liked' => $newLiked,
            'likes_count' => max(0, $product->fresh()->likes_count),
        ]);
    }
}
