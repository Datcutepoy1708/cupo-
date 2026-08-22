<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
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

        ActivityLogService::log(
            action: 'approve_seller',
            module: 'sellers',
            description: 'Đã duyệt mở gian hàng cho Shop '.$sellerProfile->shop_name,
            subject: $sellerProfile,
            properties: ['shop_name' => $sellerProfile->shop_name, 'user_id' => $sellerProfile->user_id]
        );

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

        ActivityLogService::log(
            action: 'reject_seller',
            module: 'sellers',
            description: 'Đã từ chối đơn đăng ký gian hàng của Shop '.$sellerProfile->shop_name.'. Lý do: '.$validated['admin_note'],
            subject: $sellerProfile,
            properties: ['shop_name' => $sellerProfile->shop_name, 'reason' => $validated['admin_note']]
        );

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

        ActivityLogService::log(
            action: 'block_seller',
            module: 'sellers',
            description: 'Đã khóa gian hàng của Shop '.$sellerProfile->shop_name.'. Lý do: '.$validated['admin_note'],
            subject: $sellerProfile,
            properties: ['shop_name' => $sellerProfile->shop_name, 'reason' => $validated['admin_note']]
        );

        return response()->json([
            'message' => 'Đã khóa gian hàng người bán!',
            'data' => $sellerProfile->fresh('user'),
        ]);
    }

    /**
     * Duyệt nhiều gian hàng cùng lúc (Bulk approve)
     * URL: POST /admin/sellers/bulk-approve
     * Body: { ids: [1, 2, 3] }
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seller_profiles,id'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất 1 gian hàng.',
        ]);

        $count = SellerProfile::whereIn('id', $validated['ids'])
            ->whereIn('status', ['pending', 'rejected', 'blocked'])
            ->update([
                'status' => 'approved',
                'admin_note' => null,
            ]);

        return response()->json([
            'message' => "Dà duyệt {$count} gian hàng thành công!",
            'count' => $count,
        ]);
    }

    /**
     * Export danh sách seller dưới dạng CSV
     * URL: GET /admin/sellers/export?status=pending&search=keyword
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');
        $keyword = $request->query('search');

        $sellers = SellerProfile::with(['user', 'categories'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('shop_name', 'like', '%'.$keyword.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('email', 'like', '%'.$keyword.'%')
                        ->orWhere('name', 'like', '%'.$keyword.'%'));
            })
            ->latest()
            ->get();

        $statusLabel = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'blocked' => 'Đã khóa',
        ];

        $filename = 'cupo-sellers-'.($status ?: 'all').'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($sellers, $statusLabel) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM (Excel mở đúng tiếng Việt)
            fwrite($out, "\xEF\xBB\xBF");

            // Header row
            fputcsv($out, [
                'ID', 'Tên gian hàng', 'Slug', 'Chủ shop', 'Email',
                'Loại hình', 'Lĩnh vực', 'Hoa hồng (%)',
                'CCCD/MST', 'Địa chỉ', 'Ngày đăng ký', 'Trạng thái', 'Ghi chú Admin',
            ]);

            foreach ($sellers as $s) {
                $cats = $s->categories->pluck('name')->join(', ');
                fputcsv($out, [
                    $s->id,
                    $s->shop_name,
                    $s->slug,
                    $s->user?->name ?? '',
                    $s->user?->email ?? '',
                    $s->business_type === 'company' ? 'Doanh nghiệp' : 'Cá nhân',
                    $cats,
                    $s->commission_rate ?? '',
                    $s->national_id ?? '',
                    $s->address ?? '',
                    $s->created_at?->format('d/m/Y H:i') ?? '',
                    $statusLabel[$s->status] ?? $s->status,
                    $s->admin_note ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
