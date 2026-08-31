<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerProductRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->with(['category', 'variants'])
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
        $hasVariants = ! empty($validated['has_variants']);
        $variantsData = $validated['variants'] ?? [];

        return DB::transaction(function () use ($request, $validated, $hasVariants, $variantsData) {
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

            // Nếu có biến thể, tự động tính tổng stock và giá gốc từ các biến thể
            if ($hasVariants && ! empty($variantsData)) {
                $validated['has_variants'] = true;
                $validated['stock'] = (int) collect($variantsData)->sum('stock');
                $minPrice = collect($variantsData)->min('price');
                $validated['price'] = $minPrice ?? ($validated['price'] ?? 0);
                
                $salePrices = collect($variantsData)->pluck('sale_price')->filter(fn ($p) => ! is_null($p) && $p > 0);
                $validated['sale_price'] = $salePrices->isNotEmpty() ? $salePrices->min() : null;
            } else {
                $validated['has_variants'] = false;
            }

            $product = Product::create($validated);

            // Lưu danh sách biến thể nếu có
            if ($hasVariants && ! empty($variantsData)) {
                $this->saveProductVariants($request, $product, $variantsData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng sản phẩm mới thành công! Đang chờ Admin kiểm duyệt.',
                'data' => $product->load(['category', 'variants']),
            ], 201);
        });
    }

    /**
     * GET /seller/products/{product}
     * Xem chi tiết sản phẩm của shop mình
     */
    public function show(Request $request, Product $product): JsonResponse
    {
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
        if ($product->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền chỉnh sửa sản phẩm này.'], 403);
        }

        $validated = $request->validated();
        $hasVariants = ! empty($validated['has_variants']);
        $variantsData = $validated['variants'] ?? [];

        return DB::transaction(function () use ($request, $product, $validated, $hasVariants, $variantsData) {
            if (isset($validated['name']) && $validated['name'] !== $product->name) {
                $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
            }

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('products', 'public');
                $validated['thumbnail'] = $path;
            }

            if ($hasVariants && ! empty($variantsData)) {
                $validated['has_variants'] = true;
                $validated['stock'] = (int) collect($variantsData)->sum('stock');
                $minPrice = collect($variantsData)->min('price');
                $validated['price'] = $minPrice ?? ($validated['price'] ?? $product->price);

                $salePrices = collect($variantsData)->pluck('sale_price')->filter(fn ($p) => ! is_null($p) && $p > 0);
                $validated['sale_price'] = $salePrices->isNotEmpty() ? $salePrices->min() : null;
            } else {
                $validated['has_variants'] = false;
                $validated['attributes'] = null;
            }

            $product->update($validated);

            if ($hasVariants && ! empty($variantsData)) {
                // Xóa biến thể cũ và tạo lại danh sách mới
                $product->variants()->delete();
                $this->saveProductVariants($request, $product, $variantsData);
            } else {
                $product->variants()->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thông tin sản phẩm thành công!',
                'data' => $product->fresh(['category', 'variants']),
            ]);
        });
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

    /**
     * Helper lưu các biến thể sản phẩm và xử lý ảnh nếu có
     */
    private function saveProductVariants(Request $request, Product $product, array $variantsData): void
    {
        foreach ($variantsData as $index => $varItem) {
            $sku = ! empty($varItem['sku']) 
                ? $varItem['sku'] 
                : ($product->sku . '-V' . ($index + 1) . '-' . strtoupper(Str::random(4)));

            $imagePath = $varItem['image_path'] ?? null;

            // Xử lý nếu có file ảnh upload trực tiếp theo index variant
            if ($request->hasFile("variant_image_{$index}")) {
                $imagePath = $request->file("variant_image_{$index}")->store('products/variants', 'public');
            } elseif ($request->hasFile("variant_images.{$index}")) {
                $imagePath = $request->file("variant_images.{$index}")->store('products/variants', 'public');
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'name' => $varItem['name'],
                'sku' => $sku,
                'price' => $varItem['price'],
                'sale_price' => ! empty($varItem['sale_price']) ? $varItem['sale_price'] : null,
                'stock' => (int) ($varItem['stock'] ?? 0),
                'image_path' => $imagePath,
            ]);
        }
    }
}

