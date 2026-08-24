<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerProductController extends Controller
{
    /**
     * GET /seller/products
     * Lấy danh sách sản phẩm thuộc sở hữu của Seller đang đăng nhập
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::where('seller_id', $request->user()->id)
            ->with('category')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * POST /seller/products
     * Seller đăng sản phẩm mới (Mặc định status = pending chờ Admin duyệt)
     */
    public function store(SellerProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['seller_id'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-'.strtoupper(Str::random(8));
        }
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('products', 'public');
            $validated['thumbnail'] = $path;
        } elseif (empty($validated['thumbnail'])) {
            $validated['thumbnail'] = 'products/default.png';
        }
        $validated['status'] = 'pending'; // Bắt buộc chờ Admin duyệt

        $product = Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng sản phẩm mới thành công! Đang chờ Admin kiểm duyệt.',
            'data' => $product->load('category'),
        ], 201);
    }

    /**
     * GET /seller/products/{product}
     * Xem chi tiết sản phẩm của shop mình
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        // Chặn nếu Seller cố tình xem sản phẩm của Shop khác
        if ($product->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền truy cập sản phẩm này.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product->load(['category', 'images', 'variants']),
        ]);
    }

    /**
     * PUT/PATCH /seller/products/{product}
     * Seller cập nhật thông tin sản phẩm
     */
    public function update(SellerProductRequest $request, Product $product): JsonResponse
    {
        // Chặn nếu Seller cố tình sửa sản phẩm của Shop khác
        if ($product->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền chỉnh sửa sản phẩm này.'], 403);
        }

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
        }
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('products', 'public');
            $validated['thumbnail'] = $path;
        }

        $product->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin sản phẩm thành công!',
            'data' => $product->fresh('category'),
        ]);
    }

    /**
     * DELETE /seller/products/{product}
     * Seller xóa sản phẩm của shop mình
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        if ($product->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền xóa sản phẩm này.'], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'Xóa sản phẩm thành công!',
        ]);
    }
}
