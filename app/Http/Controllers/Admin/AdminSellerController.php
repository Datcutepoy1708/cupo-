<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSellerController extends Controller
{
    /**
     * Danh sách gian hàng cho Admin (lọc theo status)
     * URL: GET /admin/sellers?status=pending
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $sellers = SellerProfile::with('user')
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        return response()->json($sellers);
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
