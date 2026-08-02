<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\Admin\AdminRejectResourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    /**
     * GET /admin/products
     * Lấy danh sách sản phẩm trên sàn (hỗ trợ lọc theo status: pending,approved,rejected)
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $products = Product::with(['seller.sellerProfile', 'category'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     *  PATCH /admin/products/{product}/approve
     *  Admin duyệt sản phẩm -> status = approved
     */
    public function approve(Product $product): JsonResponse
    {
        $product->update([
            'status' => 'approved',
            'admin_note' => null,
        ]);

        return response()->json([
            'message' => 'Duyệt sản phẩm thành công! Sản phẩm đã được công khai trên sàn',
            'data' => $product->fresh(['seller', 'category'])
        ]);
    }

    /**
     * Validate bằng AdminRejectResourceRequest theo Rule 16
     */
    public function reject(AdminRejectResourceRequest $request, Product $product): JsonResponse
    {
        $product->update([
            'status' => 'rejected',
            'admin_note' => $request->validated('admin_note'),
        ]);
        return response()->json([
            'message' => 'Đã từ chối/gỡ sản phẩm vi phạm!',
            'data' => $product->fresh(['seller', 'category']),
        ]);
    }

}
