<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminProductController extends Controller
{
    /**
     * Danh sách sản phẩm cho Admin.
     * - Browser request  -> trả về Blade view (admin.products.index)
     * - AJAX / JSON      -> trả về JSON paginate kèm meta đếm từng trạng thái
     *
     * Query params:
     *   status  = pending | approved | rejected
     *   search  = từ khóa tìm kiếm (tên sản phẩm, SKU, tên shop, email người bán)
     *   page    = số trang
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('admin.products.index');
        }

        $status = $request->query('status');
        $keyword = $request->query('search');

        $products = Product::with(['seller.sellerProfile', 'category'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('sku', 'like', '%'.$keyword.'%')
                        ->orWhereHas('seller', function ($sq) use ($keyword) {
                            $sq->where('name', 'like', '%'.$keyword.'%')
                                ->orWhere('email', 'like', '%'.$keyword.'%')
                                ->orWhereHas('sellerProfile', fn ($spq) => $spq->where('shop_name', 'like', '%'.$keyword.'%'));
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Đếm số lượng theo từng trạng thái
        $counts = Product::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json(array_merge($products->toArray(), [
            'meta' => [
                'total_all' => $counts->sum(),
                'total_pending' => $counts->get('pending', 0),
                'total_approved' => $counts->get('approved', 0),
                'total_rejected' => $counts->get('rejected', 0),
            ],
        ]));
    }

    /**
     * Duyệt sản phẩm -> status = approved (Approve không yêu cầu admin_note)
     * URL: PATCH /admin/products/{product}/approve
     */
    public function approve(Product $product): JsonResponse
    {
        $product->update([
            'status' => 'approved',
            'admin_note' => null,
        ]);

        return response()->json([
            'message' => 'Duyệt sản phẩm thành công! Sản phẩm đã được công khai trên sàn.',
            'data' => $product->fresh(['seller.sellerProfile', 'category']),
        ]);
    }

    /**
     * Từ chối / gỡ sản phẩm -> status = rejected (Bắt buộc truyền admin_note >= 10 ký tự)
     * URL: PATCH /admin/products/{product}/reject
     */
    public function reject(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối sản phẩm.',
            'admin_note.min' => 'Lý do từ chối phải có tối thiểu 10 ký tự.',
        ]);

        $product->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
        ]);

        return response()->json([
            'message' => 'Đã từ chối sản phẩm!',
            'data' => $product->fresh(['seller.sellerProfile', 'category']),
        ]);
    }

    /**
     * Duyệt nhiều sản phẩm cùng lúc (Bulk approve)
     * URL: POST /admin/products/bulk-approve
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất 1 sản phẩm.',
        ]);

        $count = Product::whereIn('id', $validated['ids'])
            ->whereIn('status', ['pending', 'rejected'])
            ->update([
                'status' => 'approved',
                'admin_note' => null,
            ]);

        return response()->json([
            'message' => "Đã duyệt {$count} sản phẩm thành công!",
            'count' => $count,
        ]);
    }

    /**
     * Từ chối nhiều sản phẩm cùng lúc (Bulk reject)
     * URL: POST /admin/products/bulk-reject
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'admin_note' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất 1 sản phẩm.',
            'admin_note.required' => 'Vui lòng nhập lý do từ chối hàng loạt.',
            'admin_note.min' => 'Lý do từ chối phải có tối thiểu 10 ký tự.',
        ]);

        $count = Product::whereIn('id', $validated['ids'])
            ->update([
                'status' => 'rejected',
                'admin_note' => $validated['admin_note'],
            ]);

        return response()->json([
            'message' => "Đã từ chối {$count} sản phẩm!",
            'count' => $count,
        ]);
    }

    /**
     * Export danh sách sản phẩm dưới dạng CSV
     * URL: GET /admin/products/export?status=pending&search=keyword
     */
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');
        $keyword = $request->query('search');

        $products = Product::with(['seller.sellerProfile', 'category'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('sku', 'like', '%'.$keyword.'%')
                        ->orWhereHas('seller', function ($sq) use ($keyword) {
                            $sq->where('name', 'like', '%'.$keyword.'%')
                                ->orWhere('email', 'like', '%'.$keyword.'%')
                                ->orWhereHas('sellerProfile', fn ($spq) => $spq->where('shop_name', 'like', '%'.$keyword.'%'));
                        });
                });
            })
            ->latest()
            ->get();

        $statusLabel = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
        ];

        $filename = 'cupo-products-'.($status ?: 'all').'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($products, $statusLabel) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM cho Excel
            fwrite($out, "\xEF\xBB\xBF");

            // Header CSV
            fputcsv($out, [
                'ID', 'Tên sản phẩm', 'SKU', 'Gian hàng', 'Danh mục',
                'Giá bán (VNĐ)', 'Tồn kho', 'Phân loại', 'Số lượt xem',
                'Ngày đăng', 'Trạng thái', 'Ghi chú Admin',
            ]);

            foreach ($products as $p) {
                $shopName = $p->seller?->sellerProfile?->shop_name ?? $p->seller?->name ?? 'N/A';
                fputcsv($out, [
                    $p->id,
                    $p->name,
                    $p->sku ?? 'N/A',
                    $shopName,
                    $p->category?->name ?? 'Chưa chọn',
                    number_format($p->price, 0, ',', '.'),
                    $p->stock ?? 0,
                    $p->has_variants ? 'Có biến thể' : 'Sản phẩm thường',
                    $p->views_count ?? 0,
                    $p->created_at?->format('d/m/Y H:i') ?? '',
                    $statusLabel[$p->status] ?? $p->status,
                    $p->admin_note ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
