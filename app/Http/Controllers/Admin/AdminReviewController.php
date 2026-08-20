<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    /**
     * Danh sách đánh giá toàn sàn & Trung tâm kiểm duyệt báo cáo.
     */
    public function index(Request $request): View|JsonResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin', 'moderator'])) {
            abort(403, 'Bạn không có quyền truy cập Quản lý đánh giá!');
        }

        $rating = $request->query('rating');
        $status = $request->query('status'); // approved | hidden
        $reportStatus = $request->query('report_status'); // all | pending | resolved | dismissed
        $keyword = $request->query('search');

        $query = Review::with([
            'product.seller.sellerProfile:id,user_id,shop_name',
            'user:id,name,email,avatar',
            'reply.seller:id,name',
        ])
            ->when($rating, fn ($q) => $q->where('rating', (int) $rating))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($reportStatus && $reportStatus !== 'all', fn ($q) => $q->where('report_status', $reportStatus))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('comment', 'like', "%{$keyword}%")
                        ->orWhere('report_reason', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"))
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$keyword}%"));
                });
            });

        $reviews = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_reviews' => Review::count(),
            'average_rating' => Review::count() > 0 ? round(Review::avg('rating'), 1) : 5.0,
            'pending_reports_count' => Review::where('is_reported', true)->where('report_status', 'pending')->count(),
            'hidden_reviews_count' => Review::where('status', 'hidden')->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json(array_merge($reviews->toArray(), [
                'meta' => $stats,
            ]));
        }

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Bật / Ẩn đánh giá vi phạm tiêu chuẩn cộng đồng.
     */
    public function toggleStatus(Review $review): JsonResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin', 'moderator'])) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này!');
        }

        $newStatus = ($review->status === 'approved') ? 'hidden' : 'approved';
        $review->update(['status' => $newStatus]);

        ActivityLogService::log(
            action: 'toggle_review_status',
            module: 'reviews',
            description: ($newStatus === 'hidden' ? 'Đã ẩn đánh giá #' : 'Đã khôi phục hiển thị đánh giá #').$review->id.' của sản phẩm '.($review->product->name ?? 'N/A'),
            subject: $review,
            properties: ['status' => $newStatus, 'product_id' => $review->product_id]
        );

        return response()->json([
            'message' => $newStatus === 'hidden' ? 'Đã ẩn đánh giá khỏi trang sản phẩm!' : 'Đã khôi phục hiển thị đánh giá!',
            'status' => $newStatus,
        ]);
    }

    /**
     * Phán quyết khiếu nại báo cáo đánh giá của Seller (Chấp thuận ẩn hoặc Bác bỏ khiếu nại).
     */
    public function resolveReport(Request $request, Review $review): JsonResponse
    {
        $role = auth()->user()->role ?? '';
        if (! in_array($role, ['super-admin', 'admin', 'moderator'])) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này!');
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:approve_report,dismiss_report'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['decision'] === 'approve_report') {
            $review->update([
                'status' => 'hidden',
                'report_status' => 'resolved',
                'admin_note' => $validated['admin_note'] ?? 'Chấp thuận khiếu nại của Shop — Đã ẩn đánh giá vi phạm.',
            ]);

            ActivityLogService::log(
                action: 'resolve_review_report',
                module: 'reviews',
                description: 'Chấp thuận báo cáo khiếu nại đánh giá #'.$review->id.' của Shop. Đã ẩn đánh giá.',
                subject: $review,
                properties: ['decision' => 'approved', 'admin_note' => $review->admin_note]
            );

            return response()->json([
                'message' => 'Đã chấp thuận báo cáo khiếu nại và ẩn đánh giá!',
                'data' => $review,
            ]);
        }

        $review->update([
            'report_status' => 'dismissed',
            'admin_note' => $validated['admin_note'] ?? 'Bác bỏ khiếu nại — Đánh giá hợp lệ tuân thủ tiêu chuẩn cộng đồng.',
        ]);

        ActivityLogService::log(
            action: 'dismiss_review_report',
            module: 'reviews',
            description: 'Bác bỏ khiếu nại đánh giá #'.$review->id.' của Shop. Giữ nguyên đánh giá.',
            subject: $review,
            properties: ['decision' => 'dismissed', 'admin_note' => $review->admin_note]
        );

        return response()->json([
            'message' => 'Đã bác bỏ khiếu nại và giữ nguyên đánh giá!',
            'data' => $review,
        ]);
    }
}
