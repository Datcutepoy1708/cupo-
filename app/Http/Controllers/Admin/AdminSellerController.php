<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSellerController extends Controller
{
    /**
     * Danh sách gian hàng cho Admin.
     * - Browser request  -> tra ve Blade view (admin.sellers.index)
     * - AJAX / JSON      -> tra ve JSON paginate kem meta dem tung trang thai
     *
     * Query params:
     *   status  = pending | approved | rejected | blocked
     *   search  = tu khoa tim kiem (shop_name, email chu shop)
     *   page    = so trang
     */
    public function index(Request $request): View|JsonResponse
    {
        // Browser thong thuong -> tra Blade view, JS se tu AJAX load du lieu
        if (! $request->wantsJson()) {
            return view('admin.sellers.index');
        }

        $status = $request->query('status');
        $keyword = $request->query('search');

        $sellers = SellerProfile::with(['user', 'categories'])
            ->withCount('followers')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('shop_name', 'like', '%'.$keyword.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                        ->orWhere('name', 'like', '%'.$keyword.'%'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Dem so luong theo tung trang thai de cap nhat badge va stat cards
        $counts = SellerProfile::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json(array_merge($sellers->toArray(), [
            'meta' => [
                'total_all' => $counts->sum(),
                'total_pending' => $counts->get('pending', 0),
                'total_approved' => $counts->get('approved', 0),
                'total_rejected' => $counts->get('rejected', 0),
                'total_blocked' => $counts->get('blocked', 0),
            ],
        ]));
    }

    /**
     * Duyệt gian hàng -> status = approved (Rule 16: Approve không yêu cầu admin_note)
     * URL: PATCH /admin/sellers/{sellerProfile}/approve
     */
    public function approve(SellerProfile $sellerProfile): JsonResponse
    {
        $sellerProfile->update([
            'status' => 'approved',
            'admin_note' => null,
        ]);

        return response()->json([
            'message' => 'Duyệt gian hàng thành công!',
            'data' => $sellerProfile->fresh('user'),
        ]);
    }

    /**
     * Từ chối gian hàng -> status = rejected (Rule 16: Bắt buộc truyền admin_note)
     * URL: PATCH /admin/sellers/{sellerProfile}/reject
     */
    public function reject(Request $request, SellerProfile $sellerProfile): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối gian hàng.',
        ]);

        $sellerProfile->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
        ]);

        return response()->json([
            'message' => 'Đã từ chối đơn đăng ký gian hàng!',
            'data' => $sellerProfile->fresh('user'),
        ]);
    }

    /**
     * Khóa gian hàng -> status = blocked (Rule 16: Bắt buộc truyền admin_note)
     * URL: PATCH /admin/sellers/{sellerProfile}/block
     */
    public function block(Request $request, SellerProfile $sellerProfile): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do khóa gian hàng.',
        ]);

        $sellerProfile->update([
            'status' => 'blocked',
            'admin_note' => $validated['admin_note'],
        ]);

        return response()->json([
            'message' => 'Đã khóa gian hàng người bán!',
            'data' => $sellerProfile->fresh('user'),
        ]);
    }
}
