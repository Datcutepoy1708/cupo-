<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBannerController extends Controller
{
    /**
     * Danh sách Banner cho Admin.
     * - Browser request -> trả về Blade view (admin.banners.index)
     * - AJAX / JSON     -> trả về JSON paginate kèm meta đếm từng trạng thái
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            return view('admin.banners.index');
        }

        $status = $request->query('status');
        $position = $request->query('position');
        $keyword = $request->query('search');

        $now = now();

        $banners = Banner::query()
            ->when($status === 'active', function ($q) use ($now) {
                $q->where('is_active', true)
                    ->where(function ($sub) use ($now) {
                        $sub->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($sub) use ($now) {
                        $sub->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                    });
            })
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($status === 'expired', function ($q) use ($now) {
                $q->whereNotNull('ends_at')->where('ends_at', '<', $now);
            })
            ->when($position, fn ($q) => $q->where('position', $position))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('title', 'like', '%'.$keyword.'%')
                    ->orWhere('link_url', 'like', '%'.$keyword.'%');
            })
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Đếm thống kê cho Stat Cards và Tab Badges
        $allCount = Banner::count();
        $activeCount = Banner::active()->count();
        $inactiveCount = Banner::where('is_active', false)->count();
        $expiredCount = Banner::whereNotNull('ends_at')->where('ends_at', '<', $now)->count();

        return response()->json(array_merge($banners->toArray(), [
            'meta' => [
                'total_all' => $allCount,
                'total_active' => $activeCount,
                'total_inactive' => $inactiveCount,
                'total_expired' => $expiredCount,
            ],
        ]));
    }

    /**
     * POST /admin/banners
     * Admin tạo Banner mới
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image_path' => ['required', 'string'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'position' => ['required', 'in:homepage_hero,homepage_mid,category_top,sidebar'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->filled('starts_at')) {
                    if (strtotime($value) < strtotime($request->input('starts_at'))) {
                        $fail('Ngày kết thúc phải diễn ra sau hoặc bằng ngày bắt đầu.');
                    }
                }
            }],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề banner.',
            'image_path.required' => 'Vui lòng nhập hoặc chọn đường dẫn ảnh banner.',
            'link_url.url' => 'Đường dẫn liên kết (URL) không hợp lệ.',
            'position.required' => 'Vui lòng chọn vị trí hiển thị.',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['starts_at'] = $request->filled('starts_at') ? $request->input('starts_at') : null;
        $validated['ends_at'] = $request->filled('ends_at') ? $request->input('ends_at') : null;

        $banner = Banner::create($validated);

        return response()->json([
            'message' => 'Tạo banner mới thành công!',
            'data' => $banner,
        ], 201);
    }

    /**
     * GET /admin/banners/{banner}
     * Chi tiết 1 Banner
     */
    public function show(Banner $banner): JsonResponse
    {
        return response()->json([
            'data' => $banner,
        ]);
    }

    /**
     * PUT/PATCH /admin/banners/{banner}
     * Admin cập nhật Banner
     */
    public function update(Request $request, Banner $banner): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'image_path' => ['sometimes', 'required', 'string'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'position' => ['sometimes', 'required', 'in:homepage_hero,homepage_mid,category_top,sidebar'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->filled('starts_at')) {
                    if (strtotime($value) < strtotime($request->input('starts_at'))) {
                        $fail('Ngày kết thúc phải diễn ra sau hoặc bằng ngày bắt đầu.');
                    }
                }
            }],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề banner.',
            'image_path.required' => 'Vui lòng nhập đường dẫn ảnh banner.',
            'link_url.url' => 'Đường dẫn liên kết (URL) không hợp lệ.',
        ]);

        if ($request->has('starts_at')) {
            $validated['starts_at'] = $request->filled('starts_at') ? $request->input('starts_at') : null;
        }
        if ($request->has('ends_at')) {
            $validated['ends_at'] = $request->filled('ends_at') ? $request->input('ends_at') : null;
        }

        $banner->update($validated);

        return response()->json([
            'message' => 'Cập nhật banner thành công!',
            'data' => $banner->fresh(),
        ]);
    }

    /**
     * DELETE /admin/banners/{banner}
     * Admin xóa 1 Banner
     */
    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();

        return response()->json([
            'message' => 'Đã xóa banner thành công!',
        ]);
    }

    /**
     * POST /admin/banners/bulk-status
     * Admin đổi trạng thái (Hiển thị / Ẩn) cho nhiều banner
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:banners,id'],
            'is_active' => ['required', 'boolean'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất 1 banner.',
        ]);

        $count = Banner::whereIn('id', $validated['ids'])
            ->update(['is_active' => $validated['is_active']]);

        Banner::clearBannerCache();

        $statusText = $validated['is_active'] ? 'hiển thị' : 'ẩn';

        return response()->json([
            'message' => "Đã cập nhật {$statusText} cho {$count} banner thành công!",
            'count' => $count,
        ]);
    }

    /**
     * POST /admin/banners/bulk-delete
     * Admin xóa nhiều banner cùng lúc
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:banners,id'],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất 1 banner để xóa.',
        ]);

        $count = Banner::whereIn('id', $validated['ids'])->delete();

        Banner::clearBannerCache();

        return response()->json([
            'message' => "Đã xóa {$count} banner thành công!",
            'count' => $count,
        ]);
    }
}
