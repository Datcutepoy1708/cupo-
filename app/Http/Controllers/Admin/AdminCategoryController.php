<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    /**
     * GET /admin/categories
     * Trả về view trang quản lý danh mục
     */
    public function index(): View
    {
        return view('admin.categories.index');
    }

    /**
     * GET /admin/categories/data
     * JSON API: Lấy cây danh mục để AJAX render
     */
    public function data(): JsonResponse
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->withCount(['children', 'sellerProfiles'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    /**
     * POST /admin/categories
     * Admin tạo danh mục mới
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
        ]);
        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
        $validated['status'] = $validated['status'] ?? true;

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Tạo danh mục thành công',
            'data' => $category->load('parent'),
        ], 201);
    }

    /**
     * GET /admin/categories/{category}
     * Xem chi tiết 1 danh mục
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => $category->load(['parent', 'children']),
        ]);
    }

    /**
     * PUT/PATCH /admin/categories/{category}
     * Admin sửa thông tin danh mục
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
        }
        $category->update($validated);

        return response()->json([
            'message' => 'Cập nhật danh mục thành công!',
            'data' => $category->fresh(['parent', 'children']),
        ]);
    }

    /**
     * DELETE /admin/categories/{category}
     * Admin xóa danh mục
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Xóa danh muc thành công',
        ]);
    }
}
