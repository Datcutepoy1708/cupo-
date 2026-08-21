<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class SellerReviewController extends Controller
{
    /**
     * Danh sách đánh giá sản phẩm của Shop.
     */
    public function index(Request $request): View|JsonResponse
    {
        $sellerId = auth()->id();
        $productIds = Product::where('seller_id', $sellerId)->pluck('id');

        $rating = $request->query('rating');
        $filterState = $request->query('state'); // all | replied | unreplied | reported
        $keyword = $request->query('search');

        $query = Review::with(['product:id,name,thumbnail', 'user:id,name,avatar', 'reply'])
            ->whereIn('product_id', $productIds)
            ->where('status', 'approved')
            ->when($rating, fn ($q) => $q->where('rating', (int) $rating))
            ->when($filterState === 'replied', fn ($q) => $q->has('reply'))
            ->when($filterState === 'unreplied', fn ($q) => $q->doesntHave('reply'))
            ->when($filterState === 'reported', fn ($q) => $q->where('is_reported', true))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('comment', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$keyword}%"));
                });
            });

        $reviews = $query->latest('id')->paginate(10)->withQueryString();

        // Calculate Shop Rating Statistics
        $allReviews = Review::whereIn('product_id', $productIds)->where('status', 'approved')->get();
        $totalReviews = $allReviews->count();
        $avgRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 5.0;

        $repliedCount = Review::whereIn('product_id', $productIds)->where('status', 'approved')->has('reply')->count();
        $responseRate = $totalReviews > 0 ? round(($repliedCount / $totalReviews) * 100) : 100;

        $starCounts = [
            5 => $allReviews->where('rating', 5)->count(),
            4 => $allReviews->where('rating', 4)->count(),
            3 => $allReviews->where('rating', 3)->count(),
            2 => $allReviews->where('rating', 2)->count(),
            1 => $allReviews->where('rating', 1)->count(),
        ];

        $stats = [
            'total_reviews' => $totalReviews,
            'average_rating' => $avgRating,
            'response_rate' => $responseRate,
            'unreplied_count' => $totalReviews - $repliedCount,
            'star_counts' => $starCounts,
        ];

        if ($request->wantsJson()) {
            return response()->json(array_merge($reviews->toArray(), [
                'meta' => $stats,
            ]));
        }

        return view('seller.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Seller phản hồi đánh giá của khách hàng.
     */
    public function reply(Request $request, Review $review): JsonResponse
    {
        $sellerId = auth()->id();

        // Kiểm tra xem sản phẩm có thuộc về Seller đang đăng nhập không
        if ($review->product->seller_id !== $sellerId) {
            return response()->json(['message' => 'Bạn không có quyền phản hồi đánh giá của sản phẩm này!'], 403);
        }

        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:1000'],
        ], [
            'reply.required' => 'Vui lòng nhập nội dung phản hồi.',
            'reply.max' => 'Nội dung phản hồi không được vượt quá 1000 ký tự.',
        ]);

        $reply = ReviewReply::updateOrCreate(
            ['review_id' => $review->id],
            [
                'seller_id' => $sellerId,
                'reply' => $validated['reply'],
            ]
        );

        return response()->json([
            'message' => 'Gửi phản hồi cho khách hàng thành công!',
            'data' => $reply,
        ]);
    }

    /**
     * Seller gửi báo cáo vi phạm đánh giá lên Admin.
     */
    public function report(Request $request, Review $review): JsonResponse
    {
        $sellerId = auth()->id();

        if ($review->product->seller_id !== $sellerId) {
            return response()->json(['message' => 'Bạn không có quyền báo cáo đánh giá này!'], 403);
        }

        $validated = $request->validate([
            'report_reason' => ['required', 'string', 'max:500'],
        ], [
            'report_reason.required' => 'Vui lòng chọn hoặc nhập lý do báo cáo vi phạm.',
            'report_reason.max' => 'Lý do báo cáo không được vượt quá 500 ký tự.',
        ]);

        $review->update([
            'is_reported' => true,
            'report_reason' => $validated['report_reason'],
            'report_status' => 'pending',
        ]);

        // Gửi thông báo cho Admin & Moderator
        $admins = User::whereIn('role', ['super-admin', 'admin', 'moderator'])->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new GeneralNotification(
                'Báo cáo đánh giá vi phạm',
                'Gian hàng '.($review->product->seller->sellerProfile->shop_name ?? 'Shop').' vừa báo cáo 1 đánh giá vi phạm.',
                route('admin.reviews.index'),
                'fa-solid fa-flag',
                'warning'
            ));
        }

        return response()->json([
            'message' => 'Đã gửi khiếu nại báo cáo vi phạm lên Ban quản trị sàn!',
            'data' => $review,
        ]);
    }
}
