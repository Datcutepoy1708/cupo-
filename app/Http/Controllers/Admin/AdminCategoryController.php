<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'message' => 'Xóa danh mục thành công',
        ]);
    }

    /**
     * POST /admin/categories/bulk-status
     * Admin đổi trạng thái (hiển thị / ẩn) cho nhiều danh mục
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:categories,id'],
            'status' => ['required', 'boolean'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất 1 danh mục.',
        ]);

        $count = Category::whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        $statusText = $validated['status'] ? 'hiển thị' : 'ẩn';

        return response()->json([
            'message' => "Đã cập nhật {$statusText} cho {$count} danh mục!",
            'count' => $count,
        ]);
    }

    /**
     * POST /admin/categories/bulk-delete
     * Admin xóa nhiều danh mục cùng lúc
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:categories,id'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất 1 danh mục để xóa.',
        ]);

        $count = Category::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => "Đã xóa {$count} danh mục thành công!",
            'count' => $count,
        ]);
    }

    /**
     * GET /admin/categories/export
     * Export danh sách danh mục ra file CSV
     */
    public function export(Request $request): StreamedResponse
    {
        $categories = Category::with(['parent', 'children'])
            ->withCount('children')
            ->orderBy('parent_id', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $filename = 'cupo-categories-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($categories) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM cho Excel
            fwrite($out, "\xEF\xBB\xBF");

            // Header CSV
            fputcsv($out, [
                'ID', 'Tên danh mục', 'Slug', 'Danh mục cha', 'Số con', 'Trạng thái', 'Ngày tạo',
            ]);

            foreach ($categories as $cat) {
                fputcsv($out, [
                    $cat->id,
                    $cat->name,
                    $cat->slug,
                    $cat->parent ? $cat->parent->name : '— (Gốc)',
                    $cat->children_count,
                    $cat->status ? 'Hiển thị' : 'Đã ẩn',
                    $cat->created_at ? $cat->created_at->format('d/m/Y H:i') : '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
